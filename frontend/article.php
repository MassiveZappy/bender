<?php
// Include the full-width fix for article.php
include_once "assets/article-fix.php";
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION["user_id"];
$article_id = $_GET["id"] ?? null;
$page_title = ($article_id ? "Edit" : "Create") . " Article - Bender";

// Fetch available skins
$ch = curl_init("http://localhost:5000/api/skins");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$skins = json_decode(curl_exec($ch), true);
curl_close($ch);

// Set defaults for new article
$default_skin_id = !empty($skins) ? $skins[0]["id"] : "";
$default_datetime = date("Y-m-d\TH:i"); // Format for datetime-local input
$default_author = $_SESSION["user_name"] ?? ""; // If you store user name in session

$article = [
    "title" => "",
    "subtitle" => "",
    "content" => "",
    "skin_id" => $default_skin_id,
    "publication_datetime" => $default_datetime,
    "author" => $default_author,
    "author_description" => "",
    "tags" => [],
];
if ($article_id) {
    $ch = curl_init("http://localhost:5000/api/articles/$article_id");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);
    if (!empty($response["id"])) {
        $article = $response;
        if (!isset($article["tags"])) {
            $article["tags"] = [];
        }
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $tags = array_map("trim", explode(",", $_POST["tags"]));
    $data = [
        "user_id" => $user_id,
        "title" => $_POST["title"],
        "subtitle" => $_POST["subtitle"],
        "content" => $_POST["content"],
        "skin_id" => $_POST["skin_id"],
        "publication_datetime" => $_POST["publication_datetime"],
        "author" => $_POST["author"],
        "author_description" => $_POST["author_description"],
        "tags" => $tags,
    ];
    if ($article_id) {
        // Edit existing article
        $ch = curl_init("http://localhost:5000/api/articles/$article_id");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
        ]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);
    } else {
        // Create new article
        $ch = curl_init("http://localhost:5000/api/articles");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
        ]);
        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);
    }
    if (!empty($response["success"])) {
        header("Location: dashboard.php");
        exit();
    } else {
        $error = $response["error"] ?? "Error saving article";
        // Log backend error to browser console for debugging
        if (!empty($response["error"])) {
            echo "<script>console.error(" .
                json_encode($response["error"]) .
                ");</script>";
        }
    }
}
?>
<style>
/* Special styles for article editor page */
html, body {
    height: 100%;
}

body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

.article-form {
    width: 100%;
    max-width: 100%;
}

.bender-footer {
    flex-shrink: 0;
    width: 100%;
}

/* Fix for editor content */
.CodeMirror {
    height: 400px !important;
    margin-bottom: 20px;
}

/* Ensure proper spacing */
.content-card {
    margin-bottom: 2rem;
}

/* Make sure content pushes footer down */
.bender-body {
    flex: 1 0 auto;
    display: flex;
    flex-direction: column;
}

.container {
    flex: 1 0 auto;
    display: flex;
    flex-direction: column;
}

/* Keep editor controls visible */
.editor-toolbar {
    z-index: 10;
}
</style>
<?php include "assets/templates/header.php"; ?>

<div class="row">
    <div class="col-lg-10 offset-lg-1">
        <div class="content-card">
            <h2 class="mb-4"><?= $article_id ? "Edit" : "Create" ?> Article</h2>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger mb-4">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="article-form">
                <div class="form-group mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input id="title" name="title" class="form-control" placeholder="Enter article title" required value="<?= htmlspecialchars(
                        $article["title"],
                    ) ?>">
                </div>

                <div class="form-group mb-3">
                    <label for="subtitle" class="form-label">Subtitle</label>
                    <input id="subtitle" name="subtitle" class="form-control" placeholder="Enter article subtitle" value="<?= htmlspecialchars(
                        $article["subtitle"] ?? "",
                    ) ?>">
                </div>

                <div class="form-group mb-4">
                    <label for="markdown-editor" class="form-label">Content (Markdown)</label>
                    <textarea id="markdown-editor" name="content" class="form-control" placeholder="Write your article content using Markdown" rows="12"><?= htmlspecialchars(
                        $article["content"],
                    ) ?></textarea>
                </div>

                <div class="form-group mb-3">
                    <label for="skin_id" class="form-label">Select Skin Template</label>
                    <select id="skin_id" name="skin_id" class="form-control" required>
            <?php foreach ($skins as $skin): ?>
                <option value="<?= $skin["id"] ?>" <?= $skin["id"] ==
$article["skin_id"]
    ? "selected"
    : "" ?>>
                    <?= htmlspecialchars($skin["name"]) ?>
                </option>
            <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted">The skin determines how your article will appear to readers.</small>
                </div>

                <div class="form-group mb-3">
                    <label for="publication_datetime" class="form-label">Publication Date & Time</label>
                    <input id="publication_datetime" type="datetime-local" class="form-control" name="publication_datetime" value="<?= htmlspecialchars(
                        $article["publication_datetime"] ?? "",
                    ) ?>">
                </div>

                <div class="form-group mb-3">
                    <label for="author" class="form-label">Author</label>
                    <input id="author" name="author" class="form-control" placeholder="Author name" value="<?= htmlspecialchars(
                        $article["author"] ?? "",
                    ) ?>">
                </div>

                <div class="form-group mb-3">
                    <label for="author_description" class="form-label">Author Description</label>
                    <input id="author_description" name="author_description" class="form-control" placeholder="Brief author description or title" value="<?= htmlspecialchars(
                        $article["author_description"] ?? "",
                    ) ?>">
                </div>

                <div class="form-group mb-4">
                    <label for="tags" class="form-label">Tags</label>
                    <input id="tags" name="tags" class="form-control" placeholder="Tags (comma separated, first tag is primary)" value="<?= htmlspecialchars(
                        is_array($article["tags"])
                            ? implode(",", $article["tags"])
                            : $article["tags"],
                    ) ?>">
                    <small class="form-text text-muted">The first tag will be highlighted as the primary tag.</small>
                </div>

                <div class="text-center">
                    <button type="submit" class="bender-btn"><?= $article_id
                        ? "Update"
                        : "Create" ?> Article</button>
                </div>
            </form>
            <?php if ($article_id): ?>
                <div class="d-flex justify-content-center mt-4 flex-wrap">
                    <form method="POST" action="delete_article.php" class="mr-2 mb-2">
                        <input type="hidden" name="id" value="<?= $article_id ?>">
                        <button type="submit" class="bender-btn bender-btn-outline" onclick="return confirm('Are you sure you want to delete this article?')">Delete Article</button>
                    </form>
                    <a href="view_article.php?id=<?= $article_id ?>" class="bender-btn bender-btn-secondary mr-2 mb-2">View Article</a>
                    <a href="dashboard.php" class="bender-btn mb-2">Back to Dashboard</a>
                </div>
            <?php else: ?>
                <div class="text-center mt-4">
                    <a href="dashboard.php" class="bender-btn bender-btn-outline">Back to Dashboard</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.css">
    <script src="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.js"></script>
    <script>
        var simplemde = new SimpleMDE({ element: document.getElementById("markdown-editor") });

        document.querySelector('form[method="POST"]').addEventListener('submit', function(e) {
            var content = simplemde.value().trim();
            if (!content) {
                alert("Content is required.");
                simplemde.codemirror.focus();
                e.preventDefault();
            } else {
                // Sync SimpleMDE value to textarea before submit
                document.getElementById("markdown-editor").value = content;
            }
        });

        // Full width override script
        (function() {
            // Apply full width styles to all container elements
            function applyFullWidth() {
                // Force container elements to use full width
                const fullWidthElements = [
                    'body', '.container', 'main', 'form[method="POST"]',
                    '.CodeMirror', '.editor-toolbar', '.CodeMirror-scroll'
                ];

                // Apply padding to container elements
                document.querySelector('body').style.padding = '0 30px';
                document.querySelector('main').style.padding = '20px 40px';
                document.querySelector('form[method="POST"]').style.padding = '20px 40px';

                fullWidthElements.forEach(selector => {
                    document.querySelectorAll(selector).forEach(el => {
                        el.style.maxWidth = '100%';
                        el.style.width = '100%';
                        el.style.marginLeft = '0';
                        el.style.marginRight = '0';
                        el.style.textAlign = 'left';
                        el.style.boxSizing = 'border-box';
                    });
                });

                // Fix the inputs
                document.querySelectorAll('input, select, textarea').forEach(el => {
                    el.style.width = '100%';
                    el.style.maxWidth = '100%';
                    el.style.textAlign = 'left';
                });

                // Fix SimpleMDE
                if (window.simplemde) {
                    window.simplemde.codemirror.refresh();
                }
            }

            // Apply immediately
            applyFullWidth();

            // Apply after a short delay to ensure all elements are rendered
            setTimeout(applyFullWidth, 100);

            // Apply whenever window is resized
            window.addEventListener('resize', applyFullWidth);
        })();
    </script>

<script>
// Additional script to ensure footer positioning
document.addEventListener('DOMContentLoaded', function() {
    // Function to adjust content height
    function adjustContentHeight() {
        // Get the height of the viewport
        const viewportHeight = window.innerHeight;

        // Get the height of header and footer
        const headerHeight = document.querySelector('.bender-header').offsetHeight;
        const footerHeight = document.querySelector('.bender-footer').offsetHeight;

        // Calculate available space
        const availableHeight = viewportHeight - headerHeight - footerHeight;

        // Set minimum height for content area
        const mainContent = document.querySelector('.bender-body');
        mainContent.style.minHeight = availableHeight + 'px';

        // Also ensure the editor has enough space if it exists
        const editor = document.querySelector('.CodeMirror');
        if (editor) {
            const editorToolbarHeight = document.querySelector('.editor-toolbar')?.offsetHeight || 0;
            const formElementsHeight = 300; // Approximate height of other form elements
            const idealEditorHeight = Math.max(300, availableHeight - editorToolbarHeight - formElementsHeight);
            editor.style.height = idealEditorHeight + 'px';
        }
    }

    // Initial adjustment
    adjustContentHeight();

    // Adjust on window resize
    window.addEventListener('resize', adjustContentHeight);

    // Also adjust when SimpleMDE is fully initialized
    if (window.simplemde) {
        simplemde.codemirror.on('refresh', adjustContentHeight);
    }
});
</script>

<?php include "assets/templates/footer.php"; ?>
