<?php
/**
 * API Proxy for Bender Frontend
 *
 * This script forwards requests from the frontend to the backend API,
 * avoiding CORS issues and simplifying frontend API calls.
 */

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set("display_errors", 1);

// Configuration
$api_base_url = "http://localhost:5000";

// Get the request method
$request_method = $_SERVER["REQUEST_METHOD"];

// Extract the API endpoint from the URL
$request_uri = $_SERVER["REQUEST_URI"];
$api_endpoint = "";

// Check if endpoint is provided directly in the query string (preferred method)
if (isset($_GET["endpoint"])) {
    $api_endpoint = $_GET["endpoint"];
}
// Check if we're using direct path format (api_proxy.php/endpoint)
elseif (preg_match("/api_proxy\.php\/(.*)/", $request_uri, $matches)) {
    $api_endpoint = $matches[1];
}
// Then check the /api/ format as fallback
elseif (preg_match("/\/api\/(.*)/", $request_uri, $matches)) {
    $api_endpoint = $matches[1];
} else {
    // Not an API request
    http_response_code(404);
    echo json_encode(["error" => "Invalid API endpoint"]);
    exit();
}

// Clean the endpoint to ensure no leading/trailing slashes
$api_endpoint = trim($api_endpoint, "/");

// Construct the full API URL
$api_url = $api_base_url . "/api/" . $api_endpoint;

// Log the request for debugging
error_log("API Proxy Request: $request_method $api_url");

// Initialize cURL
$ch = curl_init($api_url);

// Set basic cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request_method);
curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Set a reasonable timeout
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // Connection timeout

// Add headers from the original request
$headers = ["Content-Type: application/json", "Accept: application/json"];

// Add any other headers from the original request
if (function_exists("getallheaders")) {
    foreach (getallheaders() as $name => $value) {
        if (
            $name !== "Host" &&
            $name !== "Content-Length" &&
            $name !== "Content-Type"
        ) {
            $headers[] = "$name: $value";
        }
    }
}

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// For POST, PUT, PATCH, DELETE, add the request body
if (
    $request_method === "POST" ||
    $request_method === "PUT" ||
    $request_method === "PATCH" ||
    $request_method === "DELETE"
) {
    $request_body = file_get_contents("php://input");
    if (!empty($request_body)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request_body);
        error_log("API Proxy Request Body: $request_body");
    } else {
        // Check if data was sent in $_POST
        if (!empty($_POST)) {
            $json_data = json_encode($_POST);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
            error_log("API Proxy Request Body (from POST): $json_data");
        }
    }
}

// Execute the request
$start_time = microtime(true);
$response = curl_exec($ch);
$end_time = microtime(true);
$duration = round(($end_time - $start_time) * 1000, 2); // in milliseconds

// Get response info
$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
$curl_error = curl_error($ch);
$curl_errno = curl_errno($ch);

// Log the response info
error_log(
    "API Proxy Response: Status=$status_code, Time=${duration}ms, URL=$api_url",
);

// Handle cURL errors
if ($response === false) {
    error_log("API Proxy Error: $curl_error ($curl_errno)");
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "API request failed",
        "details" => $curl_error,
        "code" => $curl_errno,
    ]);
    curl_close($ch);
    exit();
}

// Close cURL
curl_close($ch);

// Set the appropriate status code
http_response_code($status_code);

// Set the content type if available
if ($content_type) {
    header("Content-Type: $content_type");
} else {
    // Default to JSON if no content type is provided
    header("Content-Type: application/json");
}

// If we got a server error (500), add more context
if ($status_code >= 500) {
    error_log("API Proxy Server Error: $response");

    // Check if response is valid JSON
    $json_response = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        // Not valid JSON, wrap it in a JSON structure
        $response = json_encode([
            "success" => false,
            "error" => "Backend server error",
            "details" => $response,
        ]);
    }
}

// Output the response
echo $response;
