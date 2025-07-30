<?php
// Direct fix for article.php - forces full width layout
// This file should be included at the top of article.php

// Only execute this if we're on the article page
$script_name = basename($_SERVER["SCRIPT_NAME"]);
if ($script_name === "article.php") {
    // Output the CSS directly into the page
    echo <<<EOT
    <style>
    /* Direct CSS fix to make article.php use full width */
    body, html {
        max-width: 100% !important;
        width: 100% !important;
        padding: 0 30px !important;
        margin: 0 !important;
        text-align: left !important;
        box-sizing: border-box !important;
    }

    .container, main, div {
        max-width: 100% !important;
        width: 100% !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        text-align: left !important;
        box-sizing: border-box !important;
    }

    main {
        padding: 20px 40px !important;
    }

    form[method="POST"] {
        max-width: 100% !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 20px 40px !important;
        text-align: left !important;
    }

    input, select, textarea, .CodeMirror, .editor-toolbar {
        width: 100% !important;
        max-width: 100% !important;
        text-align: left !important;
    }

    /* Make SimpleMDE editor full width */
    .CodeMirror, .CodeMirror-scroll {
        min-height: 400px !important;
        width: 100% !important;
    }

    .editor-toolbar {
        width: 100% !important;
    }

    /* Fix any centered elements */
    h2, p, label {
        text-align: left !important;
    }

    /* Ensure any containing divs don't constrain width */
    div {
        max-width: 100% !important;
    }
    </style>

    <script>
    // Ensure the editor uses full width once the page is loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Force elements to use full width
        document.querySelectorAll('body, .container, main, form, .CodeMirror, .editor-toolbar').forEach(function(el) {
            el.style.maxWidth = '100%';
            el.style.width = '100%';
            el.style.marginLeft = '0';
            el.style.marginRight = '0';
            el.style.textAlign = 'left';
            el.style.boxSizing = 'border-box';

            // Add padding to body element
            if (el.tagName === 'BODY') {
                el.style.padding = '0 30px';
            }

            // Add padding to main content area
            if (el.tagName === 'MAIN') {
                el.style.padding = '20px 40px';
            }

            // Add padding to form
            if (el.tagName === 'FORM') {
                el.style.padding = '20px 40px';
            }
        });

        // Fix SimpleMDE if it exists
        if (window.simplemde) {
            setTimeout(function() {
                window.simplemde.codemirror.refresh();
            }, 100);
        }
    });
    </script>
    EOT;
}
?>
