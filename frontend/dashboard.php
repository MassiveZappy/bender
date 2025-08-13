<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION["user_id"];
$page_title = "Dashboard - Bender";

// Fetch user's articles from backend
$ch = curl_init(
    "http://localhost:5000/api/articles?user_id=" . urlencode($user_id),
);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$articles_response = json_decode(curl_exec($ch), true);
curl_close($ch);

$articles = $articles_response["articles"] ?? $articles_response;

// Include header
include "assets/templates/header.php";
?>

<script>
function deleteArticle(id) {
    if (!confirm('Delete this article?')) return;

    // Create form data to submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'delete_article.php';

    // Add article ID as form field
    const idField = document.createElement('input');
    idField.type = 'hidden';
    idField.name = 'id';
    idField.value = id;
    form.appendChild(idField);

    // Submit the form
    document.body.appendChild(form);
    form.submit();
}
</script>

<div class="row">
    <div class="col-lg-10 offset-lg-1">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                <h2 class="mb-0">Your Articles</h2>
                <a href="article.php" class="bender-btn">Create New Article</a>
            </div>

            <?php if (empty($articles)): ?>
                <div class="text-center p-4">
                    <p>You haven't created any articles yet.</p>
                    <a href="article.php" class="bender-btn mt-3">Write Your First Article</a>
                </div>
            <?php else: ?>
                <div class="article-list">
                    <?php foreach ($articles as $article): ?>
                        <div class="content-card mb-3">
                            <div class="row align-items-center">
                                <div class="col-md-7">
                                    <h4><?= htmlspecialchars(
                                        $article["title"],
                                    ) ?></h4>
                                    <div class="text-muted">
                                        Skin: <?= htmlspecialchars(
                                            $article["skin_id"],
                                        ) ?>
                                    </div>
                                </div>
                                <div class="col-md-5 text-md-right mt-3 mt-md-0">
                                    <div class="d-flex justify-content-md-end flex-wrap">
                                        <a href="article.php?id=<?= $article[
                                            "id"
                                        ] ?>" class="bender-btn mr-2 mb-2">Edit</a>
                                        <a href="view_article.php?id=<?= $article[
                                            "id"
                                        ] ?>" class="bender-btn bender-btn-secondary mr-2 mb-2">View</a>
                                        <button onclick="deleteArticle(<?= $article[
                                            "id"
                                        ] ?>)" class="bender-btn bender-btn-outline mb-2">Delete</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="text-center mt-4">
                <a href="index.php" class="bender-btn bender-btn-outline">Back to Home</a>
            </div>
        </div>
    </div>
</div>

<?php include "assets/templates/footer.php"; ?>
