<?php
session_start();
$user_id = $_SESSION["user_id"] ?? null;
$is_admin = $_SESSION["is_admin"] ?? false;
$page_title = "Bender - Satirical News";
include "assets/templates/header.php";
?>

<div class="row">
    <div class="col-lg-8 offset-lg-2">
        <div class="content-card text-center">
            <h1>Welcome to Bender</h1>
            <p class="lead mb-4">Your source for the most entertaining satirical news on the web.</p>

            <?php if ($user_id): ?>
                <div class="mb-4">
                    <p class="mb-3">Hello, user #<?= htmlspecialchars(
                        $user_id,
                    ) ?>!</p>
                    <div class="d-flex justify-content-center flex-wrap">
                        <a href="dashboard.php" class="bender-btn">Go to Dashboard</a>
                        <?php if ($is_admin): ?>
                            <a href="admin.php" class="bender-btn bender-btn-secondary">Admin Panel</a>
                        <?php endif; ?>
                        <a href="logout.php" class="bender-btn bender-btn-outline">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="mb-4">
                    <p class="mb-3">Join our community to create and share satirical news articles.</p>
                    <div class="d-flex justify-content-center flex-wrap">
                        <a href="login.php" class="bender-btn">Login</a>
                        <a href="signup.php" class="bender-btn bender-btn-secondary">Sign Up</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-4">
        <div class="content-card text-center">
            <h3>Create Articles</h3>
            <p>Write your own satirical news articles with our easy-to-use editor.</p>
            <a href="article.php" class="bender-btn">Start Writing</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="content-card text-center">
            <h3>Choose Skins</h3>
            <p>Select from various news outlet skins to style your satirical content.</p>
            <a href="dashboard.php" class="bender-btn">View Skins</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="content-card text-center">
            <h3>Share</h3>
            <p>Share your articles with friends and have a good laugh together.</p>
            <a href="dashboard.php" class="bender-btn">My Articles</a>
        </div>
    </div>
</div>

<?php include "assets/templates/footer.php"; ?>
