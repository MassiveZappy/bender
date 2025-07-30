<?php
/**
 * Bender - Asset Autoloader
 *
 * This file injects our CSS and JavaScript into the page without modifying the original source files.
 * It uses output buffering to add the script tag at the end of the body.
 */

// Don't execute if this file is called directly
if (basename($_SERVER["SCRIPT_FILENAME"]) === "autoload.php") {
    header("HTTP/1.0 403 Forbidden");
    exit("Direct access forbidden.");
}

// Start output buffering
ob_start(function ($buffer) {
    // Only process HTML output
    if (!preg_match("/<\/body>/i", $buffer)) {
        return $buffer;
    }

    // Get the current URL to determine the relative path
    $scriptPath = $_SERVER["SCRIPT_NAME"];
    $dirDepth = substr_count(dirname($scriptPath), "/");
    $baseDir = str_repeat("../", max(0, $dirDepth - 1));

    // If we're in the root directory, use empty string
    if ($baseDir === "../") {
        $baseDir = "";
    }

    // Create viewport meta tag and font imports for modern design
    $viewport =
        '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">';
    $viewport .= '<link rel="preconnect" href="https://fonts.googleapis.com">';
    $viewport .=
        '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
    $viewport .=
        '<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Fira+Code&display=swap" rel="stylesheet">';

    // Create CSS link tags
    $css = '<link rel="stylesheet" href="' . $baseDir . 'assets/modern.css">';
    $css .= '<link rel="stylesheet" href="' . $baseDir . 'assets/mobile.css">';

    // Determine the current script name
    $currentScript = basename($_SERVER["SCRIPT_NAME"]);

    // Add article-specific CSS if on article.php
    if ($currentScript === "article.php") {
        $css .=
            '<link rel="stylesheet" href="' . $baseDir . 'assets/article.css">';
    }

    // Create script tags
    $script = '<script src="' . $baseDir . 'assets/modern.js"></script>';
    $script .= '<script src="' . $baseDir . 'assets/main.js"></script>';

    // Add article-specific JS if on article.php
    if ($currentScript === "article.php") {
        $script .= '<script src="' . $baseDir . 'assets/article.js"></script>';
    }

    // Insert the viewport meta tag and CSS into head if it exists
    $buffer = str_replace("</head>", $viewport . $css . "</head>", $buffer);

    // Insert the script before closing body tag
    return str_replace("</body>", $script . "</body>", $buffer);
});

// Register a shutdown function to flush the buffer
register_shutdown_function(function () {
    ob_end_flush();
});
?>
