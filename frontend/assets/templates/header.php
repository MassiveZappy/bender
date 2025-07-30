<?php
// Define default page title if not set
$page_title = $page_title ?? "Bender - Satirical News";
$is_admin = $_SESSION["is_admin"] ?? false;
$user_id = $_SESSION["user_id"] ?? null;

// Detect if mobile (simplified detection, would use better detection in production)
$is_mobile = false;
if (isset($_SERVER["HTTP_USER_AGENT"])) {
    $is_mobile = preg_match(
        "/Mobile|Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i",
        $_SERVER["HTTP_USER_AGENT"],
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="stylesheet" href="assets/modern.css">
    <!-- You could add a third-party icon library like FontAwesome if needed -->
    <style>
        /* Sticky footer solution */
        html, body {
            height: 100%;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Custom additional styles for this page */
        .bender-header {
            background-color: #7ca4bd;
            color: white;
            padding: 1rem 0;
        }

        .bender-body {
            background-color: #ffffff;
            flex: 1 0 auto; /* This is key for sticky footer */
            padding: 2rem 0;
            display: flex;
            flex-direction: column;
        }

        .bender-footer {
            background-color: #a6c1d6;
            color: #000d18;
            padding: 1.5rem 0;
            flex-shrink: 0; /* Prevent footer from shrinking */
        }

        /* Card with hover effect */
        .content-card {
            background-color: #c8deec;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .content-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 13, 24, 0.1);
        }

        /* Special styling for buttons */
        .bender-btn {
            background-color: #a6c1d6;
            color: #000d18;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin: 0.5rem;
            text-align: center;
        }

        .bender-btn:hover {
            background-color: #7ca4bd;
            color: #fff;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 13, 24, 0.2);
            text-decoration: none;
        }

        .bender-btn-outline {
            background-color: transparent;
            color: #7ca4bd;
            border: 2px solid #7ca4bd;
        }

        .bender-btn-outline:hover {
            background-color: #7ca4bd;
            color: white;
        }

        .bender-btn-secondary {
            background-color: #fffbc7;
            color: #000d18;
        }

        .bender-btn-secondary:hover {
            background-color: #f0edb9;
        }

        /* Navigation menu */
        .nav-menu {
            display: flex;
            gap: 1rem;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
        }

        .nav-menu a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            transition: all 0.3s ease;
        }

        .nav-menu a:hover {
            background-color: rgba(255, 251, 199, 0.2);
            transform: translateY(-2px);
        }

        /* Mobile menu */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: white;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: block;
            }

            .nav-menu {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 60px;
                left: 0;
                right: 0;
                background-color: #7ca4bd;
                padding: 1rem;
                z-index: 1000;
            }

            .nav-menu.show {
                display: flex;
            }
        }
    </style>
    <script>
        // Toggle mobile menu
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.mobile-menu-toggle');
            const navMenu = document.querySelector('.nav-menu');

            if (menuToggle && navMenu) {
                menuToggle.addEventListener('click', function() {
                    navMenu.classList.toggle('show');
                });
            }
        });
    </script>
</head>
<body>
    <!-- Header -->
    <header class="bender-header">
        <div class="container">
            <div class="row d-flex justify-content-between align-items-center">
                <div class="col-md-4">
                    <a href="index.php" class="logo">BENDER</a>
                </div>
                <div class="col-md-8 d-flex justify-content-end">
                    <button class="mobile-menu-toggle">≡</button>
                    <nav class="nav-menu">
                        <?php if ($user_id): ?>
                            <a href="dashboard.php" class="nav-link">Dashboard</a>
                            <a href="article.php" class="nav-link">Create Article</a>
                            <?php if ($is_admin): ?>
                                <a href="admin.php" class="nav-link">Admin</a>
                            <?php endif; ?>
                            <a href="logout.php" class="nav-link">Logout</a>
                        <?php else: ?>
                            <a href="login.php" class="nav-link">Login</a>
                            <a href="signup.php" class="nav-link">Sign Up</a>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="bender-body">
        <div class="container" style="flex: 1 0 auto; display: flex; flex-direction: column;">
