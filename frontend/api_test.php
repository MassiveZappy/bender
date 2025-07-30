<?php
/**
 * API Connectivity Test Script
 *
 * This script tests connectivity to the backend API server
 * and displays the results in a user-friendly format.
 */

// Configuration
$api_base_url = "http://localhost:5000";
$api_endpoints = [
    "/api/admin/users" => "GET",
    "/api/admin/articles" => "GET",
    "/api/skins" => "GET"
];

// Function to test an API endpoint
function test_api_endpoint($url, $method) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 second timeout

    $start_time = microtime(true);
    $response = curl_exec($ch);
    $end_time = microtime(true);

    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $response_time = round(($end_time - $start_time) * 1000); // in milliseconds

    curl_close($ch);

    return [
        'success' => ($status_code >= 200 && $status_code < 300),
        'status_code' => $status_code,
        'response_time' => $response_time,
        'error' => $error,
        'response' => $response
    ];
}

// Run tests
$test_results = [];
$all_successful = true;

foreach ($api_endpoints as $endpoint => $method) {
    $url = $api_base_url . $endpoint;
    $result = test_api_endpoint($url, $method);
    $test_results[$endpoint] = $result;

    if (!$result['success']) {
        $all_successful = false;
    }
}

// Output HTML
header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Connectivity Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .overall-status {
            text-align: center;
            padding: 10px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .failure {
            background-color: #f8d7da;
            color: #721c24;
        }
        .test-result {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
        }
        .test-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .endpoint {
            font-weight: bold;
            font-family: monospace;
        }
        .status-code {
            font-weight: bold;
        }
        .status-code.success {
            color: #28a745;
        }
        .status-code.failure {
            color: #dc3545;
        }
        .response-details {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 3px;
            font-family: monospace;
            white-space: pre-wrap;
            max-height: 200px;
            overflow: auto;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
        }
        .test-action {
            margin-top: 10px;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0069d9;
        }
    </style>
</head>
<body>
    <h1>API Connectivity Test</h1>

    <div class="overall-status <?php echo $all_successful ? 'success' : 'failure'; ?>">
        <?php if ($all_successful): ?>
            All API endpoints are accessible!
        <?php else: ?>
            Some API endpoints are not accessible. Check the details below.
        <?php endif; ?>
    </div>

    <h2>Test Results</h2>

    <?php foreach ($test_results as $endpoint => $result): ?>
        <div class="test-result">
            <div class="test-header">
                <span class="endpoint"><?php echo $endpoint; ?> (<?php echo $api_endpoints[$endpoint]; ?>)</span>
                <span class="status-code <?php echo $result['success'] ? 'success' : 'failure'; ?>">
                    Status: <?php echo $result['status_code']; ?>
                </span>
            </div>

            <div>Response Time: <?php echo $result['response_time']; ?> ms</div>

            <?php if (!empty($result['error'])): ?>
                <div class="error">Error: <?php echo htmlspecialchars($result['error']); ?></div>
            <?php endif; ?>

            <?php if (!empty($result['response'])): ?>
                <div class="response-summary">
                    <?php
                        // Try to decode JSON response
                        $json = json_decode($result['response'], true);
                        if ($json !== null) {
                            echo "Response contains valid JSON with " . count($json) . " items";
                        } else {
                            echo "Response length: " . strlen($result['response']) . " bytes";
                        }
                    ?>
                </div>

                <details>
                    <summary>View Response Details</summary>
                    <div class="response-details"><?php echo htmlspecialchars($result['response']); ?></div>
                </details>
            <?php endif; ?>

            <div class="test-action">
                <button onclick="window.location.href='<?php echo $api_base_url . $endpoint; ?>'">Open Endpoint in New Tab</button>
            </div>
        </div>
    <?php endforeach; ?>

    <script>
        // Add toggle functionality for response details
        document.addEventListener('DOMContentLoaded', function() {
            const details = document.querySelectorAll('details');
            details.forEach(detail => {
                detail.addEventListener('toggle', event => {
                    if (detail.open) {
                        // Scroll the details into view when opened
                        detail.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                });
            });
        });
    </script>
</body>
</html>
