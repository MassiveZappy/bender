<?php
/**
 * Admin API Test Tool
 *
 * This script tests the admin API endpoints directly from the frontend
 * to help debug connection issues.
 */

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration
$api_base_url = "http://localhost:5000";
$test_user_id = 3; // ID of a test user (not admin) to try operations on
$test_article_id = 1; // ID of a test article to try operations on

// Get the action from the query string
$action = $_GET['action'] ?? '';

// API Test Results
$result = null;
$error = null;
$curl_error = null;
$http_code = null;

// Test Functions
function test_get_users() {
    global $api_base_url, $result, $error, $curl_error, $http_code;

    $ch = curl_init($api_base_url . "/api/admin/users");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);

    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false) {
        $error = "cURL Error: " . $curl_error;
    } else {
        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = "JSON Parsing Error: " . json_last_error_msg();
            $result = $response;
        }
    }

    curl_close($ch);
}

function test_make_admin($user_id) {
    global $api_base_url, $result, $error, $curl_error, $http_code;

    $ch = curl_init($api_base_url . "/api/admin/users/$user_id");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["is_admin" => true]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen(json_encode(["is_admin" => true]))
    ]);

    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false) {
        $error = "cURL Error: " . $curl_error;
    } else {
        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = "JSON Parsing Error: " . json_last_error_msg();
            $result = $response;
        }
    }

    curl_close($ch);
}

function test_delete_user($user_id) {
    global $api_base_url, $result, $error, $curl_error, $http_code;

    $ch = curl_init($api_base_url . "/api/admin/users/$user_id");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");

    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false) {
        $error = "cURL Error: " . $curl_error;
    } else {
        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = "JSON Parsing Error: " . json_last_error_msg();
            $result = $response;
        }
    }

    curl_close($ch);
}

function test_delete_article($article_id) {
    global $api_base_url, $result, $error, $curl_error, $http_code;

    $ch = curl_init($api_base_url . "/api/articles/$article_id");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");

    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false) {
        $error = "cURL Error: " . $curl_error;
    } else {
        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = "JSON Parsing Error: " . json_last_error_msg();
            $result = $response;
        }
    }

    curl_close($ch);
}

// Run the selected test
if ($action === 'get_users') {
    test_get_users();
} else if ($action === 'make_admin') {
    test_make_admin($test_user_id);
} else if ($action === 'delete_user') {
    test_delete_user($test_user_id);
} else if ($action === 'delete_article') {
    test_delete_article($test_article_id);
}

// Output HTML
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin API Test Tool</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2 {
            color: #333;
        }
        .tests {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .test-btn {
            padding: 10px 15px;
            background-color: #a6c1d6;
            color: #000d18;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .test-btn:hover {
            background-color: #7ca4bd;
        }
        .result-panel {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin-top: 20px;
        }
        .error {
            color: #721c24;
            background-color: #f8d7da;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .success {
            color: #155724;
            background-color: #d4edda;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        pre {
            background-color: #f8f9fa;
            border: 1px solid #eee;
            border-radius: 4px;
            padding: 10px;
            overflow: auto;
            max-height: 400px;
        }
        .http-code {
            font-weight: bold;
        }
        .http-success {
            color: #28a745;
        }
        .http-error {
            color: #dc3545;
        }
        .test-description {
            margin-top: 20px;
            border-left: 4px solid #a6c1d6;
            padding-left: 15px;
        }
        .api-url {
            font-family: monospace;
            background-color: #eee;
            padding: 3px 6px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <h1>Admin API Test Tool</h1>
    <p>This tool helps you test the admin API endpoints directly from the frontend to diagnose connection issues.</p>

    <div class="tests">
        <a href="?action=get_users" class="test-btn">Get All Users</a>
        <a href="?action=make_admin" class="test-btn">Make User Admin (ID: <?= $test_user_id ?>)</a>
        <a href="?action=delete_user" class="test-btn">Delete User (ID: <?= $test_user_id ?>)</a>
        <a href="?action=delete_article" class="test-btn">Delete Article (ID: <?= $test_article_id ?>)</a>
    </div>

    <?php if ($action): ?>
        <div class="result-panel">
            <h2>Test Results for: <?= htmlspecialchars($action) ?></h2>

            <?php if ($error): ?>
                <div class="error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php elseif ($curl_error): ?>
                <div class="error">
                    <?= htmlspecialchars($curl_error) ?>
                </div>
            <?php endif; ?>

            <?php if ($http_code): ?>
                <div class="http-code <?= ($http_code >= 200 && $http_code < 300) ? 'http-success' : 'http-error' ?>">
                    HTTP Status Code: <?= $http_code ?>
                </div>
            <?php endif; ?>

            <?php if ($result): ?>
                <h3>Response:</h3>
                <pre><?= is_array($result) ? json_encode($result, JSON_PRETTY_PRINT) : htmlspecialchars($result) ?></pre>
            <?php endif; ?>

            <div class="test-description">
                <?php if ($action === 'get_users'): ?>
                    <p>This test retrieves all users from the API endpoint <span class="api-url"><?= $api_base_url ?>/api/admin/users</span>.</p>
                <?php elseif ($action === 'make_admin'): ?>
                    <p>This test attempts to make user ID <?= $test_user_id ?> an admin using the API endpoint <span class="api-url"><?= $api_base_url ?>/api/admin/users/<?= $test_user_id ?></span> with a PUT request.</p>
                <?php elseif ($action === 'delete_user'): ?>
                    <p>This test attempts to delete user ID <?= $test_user_id ?> using the API endpoint <span class="api-url"><?= $api_base_url ?>/api/admin/users/<?= $test_user_id ?></span> with a DELETE request.</p>
                <?php elseif ($action === 'delete_article'): ?>
                    <p>This test attempts to delete article ID <?= $test_article_id ?> using the API endpoint <span class="api-url"><?= $api_base_url ?>/api/articles/<?= $test_article_id ?></span> with a DELETE request.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <p>Select a test to run from the buttons above.</p>
    <?php endif; ?>

    <hr>
    <p><a href="admin.php" class="test-btn">Back to Admin Panel</a></p>

    <script>
        // Add AJAX testing capabilities if needed
        document.addEventListener('DOMContentLoaded', function() {
            // Example JavaScript testing code could go here
        });
    </script>
</body>
</html>
