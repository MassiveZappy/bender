<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
$article_id = $_GET["id"] ?? null;
if (!$article_id) {
    echo "No article specified.";
    exit();
}

// Fetch article
$ch = curl_init("http://localhost:5000/api/articles/$article_id");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$article = json_decode(curl_exec($ch), true);
// echo $article;
curl_close($ch);

if (empty($article["id"])) {
    echo "Article not found.";
    exit();
}

// Prepare fields
$title = htmlspecialchars($article["title"] ?? "");
$subtitle = htmlspecialchars($article["subtitle"] ?? "");
$content_html = $article["content_html"] ?? "";
$publication_datetime = htmlspecialchars(
    $article["publication_datetime"] ?? "",
);
$author = htmlspecialchars($article["author"] ?? "");
$author_description = htmlspecialchars($article["author_description"] ?? "");
$tags = $article["tags"] ?? [];
if (is_string($tags)) {
    $tags = json_decode($tags, true);
    if (!is_array($tags)) {
        $tags = [];
    }
}
$primary_tag = count($tags) > 0 ? htmlspecialchars($tags[0]) : "";
$other_tags = array_slice($tags, 1);

// Fetch skin info
$skin_id = $article["skin_id"];
$ch = curl_init("http://localhost:5000/api/skins");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$skins = json_decode(curl_exec($ch), true);
curl_close($ch);

$skin = array_filter($skins, fn($s) => $s["id"] == $skin_id);
$skin = reset($skin);
$template_path = $skin["template_path"] ?? null;

if ($template_path && file_exists($template_path)) {
    $template = file_get_contents($template_path);
    // Replace placeholders with article data
    $output = str_replace(
        [
            "{{title}}",
            "{{subtitle}}",
            "{{content}}",
            "{{publication_datetime}}",
            "{{author}}",
            "{{author_description}}",
            "{{primary_tag}}",
            "{{tags}}",
        ],
        [
            $title,
            $subtitle,
            $content_html,
            $publication_datetime,
            $author,
            $author_description,
            $primary_tag,
            htmlspecialchars(implode(", ", $tags)),
        ],
        $template,
    );
    echo $output;
} else {
    // Fallback: plain rendering
    echo "<h1> ERROR FINDING SKIN FOR ARTICLE $template_path </h1>";
    echo "<h1>$title</h1>";
    if ($subtitle) {
        echo "<h2>$subtitle</h2>";
    }
    if ($publication_datetime) {
        echo "<div><strong>Published:</strong> $publication_datetime</div>";
    }
    if ($author) {
        echo "<div><strong>Author:</strong> $author</div>";
    }
    if ($author_description) {
        echo "<div><em>$author_description</em></div>";
    }
    if ($primary_tag) {
        echo "<div><strong>Primary Tag:</strong> <span style='font-weight:bold;'>$primary_tag</span></div>";
    }
    if (count($other_tags) > 0) {
        echo "<div><strong>Other Tags:</strong> " .
            htmlspecialchars(implode(", ", $other_tags)) .
            "</div>";
    }
    echo "<div>$content_html</div>";
}
?>
