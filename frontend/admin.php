<?php
session_start();
if (!isset($_SESSION["user_id"]) || !$_SESSION["is_admin"]) {
    header("Location: index.php");
    exit();
}

// Fetch users
$ch = curl_init("http://localhost:5000/api/admin/users");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Set timeout to 10 seconds
$response = curl_exec($ch);
$users = !empty($response) ? json_decode($response, true) : [];
$users_error = curl_error($ch);
curl_close($ch);

// Fetch articles
$ch = curl_init("http://localhost:5000/api/admin/articles");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Set timeout to 10 seconds
$response = curl_exec($ch);
$articles = !empty($response) ? json_decode($response, true) : [];
$articles_error = curl_error($ch);
curl_close($ch);

// Filter admins
$admins = array_filter($users, fn($u) => $u["is_admin"]);

// Fetch API version information
$ch = curl_init("http://localhost:5000/api/admin/version");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Set timeout to 10 seconds
$response = curl_exec($ch);
$version_info = !empty($response) ? json_decode($response, true) : [];
$version_error = curl_error($ch);
curl_close($ch);

// Check if we're viewing articles for a specific user
$view_user_articles = $_GET["view_user_articles"] ?? null;
$user_articles = [];
$viewing_username = "";

if ($view_user_articles) {
    // Fetch articles for specific user
    $ch = curl_init(
        "http://localhost:5000/api/articles?user_id=" .
            urlencode($view_user_articles),
    );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Set timeout to 10 seconds
    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if (!empty($response)) {
        $user_articles_response = json_decode($response, true);
        $user_articles = $user_articles_response["articles"] ?? [];
    } elseif (!empty($curl_error)) {
        echo "<div class='alert alert-danger'>Error connecting to API: $curl_error</div>";
    }

    // Get username for the user
    foreach ($users as $user) {
        if ($user["id"] == $view_user_articles) {
            $viewing_username = $user["username"];
            break;
        }
    }
}

// Define page title for template
$page_title = "Admin Panel - Bender";

// Include header template
include "assets/templates/header.php";
?>
    <style>
        .admin-card {
            background-color: #c8deec;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 13, 24, 0.1);
        }

        .admin-card h3 {
            color: #000d18;
            border-bottom: 2px solid #7ca4bd;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
        }

        .admin-table th,
        .admin-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #a6c1d6;
        }

        .admin-table th {
            background-color: #a6c1d6;
            color: #000d18;
            font-weight: bold;
        }

        .admin-table tr:nth-child(even) {
            background-color: rgba(166, 193, 214, 0.1);
        }

        .admin-table tr:hover {
            background-color: rgba(166, 193, 214, 0.2);
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .badge-admin {
            background-color: #7ca4bd;
            color: white;
        }

        .badge-user {
            background-color: #fffbc7;
            color: #000d18;
        }

        /* Responsive table */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Small button variant */
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        /* Mobile optimizations */
        @media (max-width: 768px) {
            .admin-table th,
            .admin-table td {
                padding: 0.5rem;
                font-size: 0.9rem;
            }

            .bender-btn {
                padding: 0.4rem 0.6rem;
                margin: 0.2rem;
            }
        }
    </style>
    <script>
    function deleteUser(id) {
        // Get current logged-in user ID
        const currentUserId = <?= $_SESSION["user_id"] ?>;

        // Prevent deleting own account
        if (id == currentUserId) {
            alert("You cannot delete your own account while logged in!");
            return;
        }

        // Check if the user is an admin
        const isAdmin = document.querySelector(`tr[data-user-id="${id}"] .badge-admin`) !== null;

        let confirmMessage = 'Are you sure you want to delete this user? This action cannot be undone.';
        if (isAdmin) {
            confirmMessage = 'WARNING: You are about to delete an ADMIN account! This could impact system access and functionality. Are you absolutely sure you want to proceed? This action cannot be undone.';
        }

        if (!confirm(confirmMessage)) return;
        console.log('Deleting user with ID:', id);

        // Show status message immediately
        const statusElem = document.getElementById('user-status-' + id);
        statusElem.style.display = 'inline-block';
        statusElem.textContent = 'Processing...';
        statusElem.style.color = '#7ca4bd';

        // Use our API proxy to avoid CORS issues
        fetch('api_proxy.php?endpoint=admin/users/' + id, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-User-ID': <?= $_SESSION["user_id"] ?>
            }
        })
        .then(res => {
            console.log('Delete user response status:', res.status);
            console.log('Delete user response headers:', Array.from(res.headers.entries()));

            // Log full response for debugging
            res.clone().text().then(text => {
                console.log('Raw response:', text);
            });

            return res.json().catch(e => {
                console.error('JSON parse error:', e);
                return { error: 'Invalid response format' };
            });
        })
        .then(data => {
            console.log('Delete user response data:', data);
            if (data.success) {
                statusElem.textContent = 'Deleted successfully!';
                statusElem.style.color = '#2ecc71';
                setTimeout(() => location.reload(), 1000);
            }
            else {
                statusElem.textContent = 'Failed: ' + (data.error || 'Unknown error');
                statusElem.style.color = '#e74c3c';
                setTimeout(() => {
                    statusElem.style.display = 'none';
                }, 3000);
            }
        })
        .catch(err => {
            console.error('Error deleting user:', err);
            statusElem.textContent = 'Error: ' + err.message;
            statusElem.style.color = '#e74c3c';
            // Display a more detailed error message in the console
            console.error('Detailed error info:', {
                message: err.message,
                stack: err.stack,
                name: err.name
            });

            // Show a direct error with retry option
            console.error('Connection error:', err.message);

            // Add error details to the status message
            statusElem.innerHTML = 'Error: ' + err.message + '<br><small>Check console for details</small>';

            // Add a retry button
            const retryBtn = document.createElement('button');
            retryBtn.innerText = 'Retry';
            retryBtn.className = 'bender-btn btn-sm';
            retryBtn.style.marginLeft = '5px';
            retryBtn.onclick = () => deleteUser(id);
            statusElem.parentNode.appendChild(retryBtn);

            setTimeout(() => {
                statusElem.style.display = 'none';
                if (retryBtn.parentNode) {
                    retryBtn.parentNode.removeChild(retryBtn);
                }
            }, 5000);
        });
    }

    function deleteArticle(id) {
        if (!confirm('Are you sure you want to delete this article? This action cannot be undone.')) return;
        console.log('Deleting article with ID:', id);

        // Show status message immediately
        const statusElem = document.getElementById('delete-status-' + id);
        statusElem.style.display = 'inline-block';
        statusElem.textContent = 'Processing...';
        statusElem.style.color = '#7ca4bd';

        // Use our API proxy to avoid CORS issues
        fetch('api_proxy.php?endpoint=articles/' + id, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => {
            console.log('Delete article response status:', res.status);
            return res.json().catch(e => {
                console.error('JSON parse error:', e);
                return { error: 'Invalid response format' };
            });
        })
        .then(data => {
            console.log('Delete article response data:', data);
            if (data.success) {
                statusElem.textContent = 'Deleted successfully!';
                statusElem.style.color = '#2ecc71';
                setTimeout(() => location.reload(), 1000);
            }
            else {
                statusElem.textContent = 'Failed: ' + (data.error || 'Unknown error');
                statusElem.style.color = '#e74c3c';
                setTimeout(() => {
                    statusElem.style.display = 'none';
                }, 3000);
            }
        })
        .catch(err => {
            console.error('Error deleting article:', err);
            statusElem.innerHTML = 'Error: ' + err.message + '<br><small>Check console for details</small>';
            statusElem.style.color = '#e74c3c';

            // Add a retry button
            const retryBtn = document.createElement('button');
            retryBtn.innerText = 'Retry';
            retryBtn.className = 'bender-btn btn-sm';
            retryBtn.style.marginLeft = '5px';
            retryBtn.onclick = () => deleteArticle(id);
            statusElem.parentNode.appendChild(retryBtn);

            setTimeout(() => {
                statusElem.style.display = 'none';
                if (retryBtn.parentNode) {
                    retryBtn.parentNode.removeChild(retryBtn);
                }
            }, 5000);
        });
    }

    function makeAdmin(id) {
        if (!confirm('Make this user an admin? This will grant them full administrative privileges.')) return;
        console.log('Making user admin with ID:', id);

        // Show status message immediately
        const statusElem = document.getElementById('user-status-' + id);
        statusElem.style.display = 'inline-block';
        statusElem.textContent = 'Processing...';
        statusElem.style.color = '#7ca4bd';

        // Use our API proxy to avoid CORS issues
        fetch('api_proxy.php?endpoint=admin/users/' + id, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ is_admin: 1 })
        })
        .then(res => {
            console.log('Make admin response status:', res.status);
            return res.json().catch(e => {
                console.error('JSON parse error:', e);
                return { error: 'Invalid response format' };
            });
        })
        .then(data => {
            console.log('Make admin response data:', data);
            if (data.success) {
                statusElem.textContent = 'Made admin successfully!';
                statusElem.style.color = '#2ecc71';
                setTimeout(() => location.reload(), 1000);
            }
            else {
                statusElem.textContent = 'Failed: ' + (data.error || 'Unknown error');
                statusElem.style.color = '#e74c3c';
                setTimeout(() => {
                    statusElem.style.display = 'none';
                }, 3000);
            }
        })
        .catch(err => {
            console.error('Error making admin:', err);
            statusElem.innerHTML = 'Error: ' + err.message + '<br><small>Check console for details</small>';
            statusElem.style.color = '#e74c3c';

            // Add a retry button
            const retryBtn = document.createElement('button');
            retryBtn.innerText = 'Retry';
            retryBtn.className = 'bender-btn btn-sm';
            retryBtn.style.marginLeft = '5px';
            retryBtn.onclick = () => makeAdmin(id);
            statusElem.parentNode.appendChild(retryBtn);

            setTimeout(() => {
                statusElem.style.display = 'none';
                if (retryBtn.parentNode) {
                    retryBtn.parentNode.removeChild(retryBtn);
                }
            }, 5000);
        });
    }

    function viewUserArticles(userId) {
        // Redirect to a filtered view of articles for this user
        window.location.href = 'admin.php?view_user_articles=' + userId;
    }
    </script>
    <div class="row">
        <div class="col-lg-10 offset-lg-1">
            <div class="content-card">
                <h2 class="mb-4">Admin Panel</h2>
                <p class="mb-4">Welcome to the administration panel. Here you can manage users and articles.</p>

                <!-- API Version Information -->
                <div class="admin-card">
                    <h3>API Version Information</h3>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <tbody>
                                <?php if (!empty($version_error)): ?>
                                    <tr>
                                        <td colspan="2" class="text-center">Error fetching version info: <?= htmlspecialchars($version_error) ?></td>
                                    </tr>
                                <?php elseif (empty($version_info)): ?>
                                    <tr>
                                        <td colspan="2" class="text-center">Version information not available</td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <th>API Version</th>
                                        <td><?= htmlspecialchars($version_info["version"]) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Build Date</th>
                                        <td><?= htmlspecialchars($version_info["build_date"]) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Environment</th>
                                        <td><?= htmlspecialchars($version_info["environment"]) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Python Version</th>
                                        <td><?= htmlspecialchars($version_info["python_version"]) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Flask Version</th>
                                        <td><?= htmlspecialchars($version_info["flask_version"]) ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if (!empty($users_error)): ?>
                    <div class="alert alert-danger">Error fetching users: <?= htmlspecialchars(
                        $users_error,
                    ) ?></div>
                <?php endif; ?>

                <?php if (!empty($articles_error)): ?>
                    <div class="alert alert-danger">Error fetching articles: <?= htmlspecialchars(
                        $articles_error,
                    ) ?></div>
                <?php endif; ?>

                <!-- Admin Users Section -->
                <div class="admin-card">
                    <h3>Admin Users</h3>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($admins)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No admin users found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($admins as $admin): ?>
                                        <tr data-user-id="<?= $admin["id"] ?>">
                                            <td><?= $admin["id"] ?></td>
                                            <td><?= htmlspecialchars(
                                                $admin["username"],
                                            ) ?></td>
                                            <td><span class="badge badge-admin">Admin</span></td>
                                            <td>
                                                <button class="bender-btn btn-sm mr-2" onclick="viewUserArticles(<?= $admin[
                                                    "id"
                                                ] ?>)">View Articles</button>
                                                <?php if (
                                                    $admin["id"] !=
                                                    $_SESSION["user_id"]
                                                ): ?>
                                                <button class="bender-btn bender-btn-outline btn-sm" onclick="deleteUser(<?= $admin[
                                                    "id"
                                                ] ?>)">Delete</button>
                                                <?php else: ?>
                                                <span class="text-muted">Current User</span>
                                                <?php endif; ?>
                                                <span class="badge" style="display:none; margin-left:5px;" id="user-status-<?= $admin[
                                                    "id"
                                                ] ?>"></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- All Users Section -->
                <div class="admin-card">
                    <h3>All Users</h3>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No users found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr data-user-id="<?= $user["id"] ?>">
                                            <td><?= $user["id"] ?></td>
                                            <td><?= htmlspecialchars(
                                                $user["username"],
                                            ) ?></td>
                                            <td>
                                                <span class="badge <?= $user[
                                                    "is_admin"
                                                ]
                                                    ? "badge-admin"
                                                    : "badge-user" ?>">
                                                    <?= $user["is_admin"]
                                                        ? "Admin"
                                                        : "User" ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="bender-btn btn-sm mr-2" onclick="viewUserArticles(<?= $user[
                                                    "id"
                                                ] ?>)">View Articles</button>
                                                <?php if (
                                                    !$user["is_admin"]
                                                ): ?>
                                                    <button class="bender-btn bender-btn-secondary btn-sm mr-2" onclick="makeAdmin(<?= $user[
                                                        "id"
                                                    ] ?>)">Make Admin</button>
                                                <?php endif; ?>
                                                <?php if (
                                                    $user["id"] !=
                                                    $_SESSION["user_id"]
                                                ): ?>
                                                <button class="bender-btn bender-btn-outline btn-sm" onclick="deleteUser(<?= $user[
                                                    "id"
                                                ] ?>)">Delete</button>
                                                <?php else: ?>
                                                <span class="text-muted">Current User</span>
                                                <?php endif; ?>
                                                <span class="badge" style="display:none; margin-left:5px;" id="user-status-<?= $user[
                                                    "id"
                                                ] ?>"></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- User Articles (if viewing specific user) -->
                <?php if ($view_user_articles): ?>
                <div class="admin-card">
                    <h3>Articles by <?= htmlspecialchars(
                        $viewing_username,
                    ) ?> (ID: <?= $view_user_articles ?>)</h3>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Skin</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($user_articles)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No articles found for this user</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach (
                                        $user_articles
                                        as $article
                                    ): ?>
                                        <tr>
                                            <td><?= $article["id"] ?></td>
                                            <td><?= htmlspecialchars(
                                                $article["title"],
                                            ) ?></td>
                                            <td><?= htmlspecialchars(
                                                $article["skin_id"] ??
                                                    "Default",
                                            ) ?></td>
                                            <td><?= $article["created_at"] ??
                                                "N/A" ?></td>
                                            <td>
                                                <a href="view_article.php?id=<?= $article[
                                                    "id"
                                                ] ?>" class="bender-btn btn-sm mr-2">View</a>
                                                <button class="bender-btn bender-btn-outline btn-sm" onclick="deleteArticle(<?= $article[
                                                    "id"
                                                ] ?>)">Delete</button>
                                                <span class="badge" style="display:none; margin-left:5px;" id="delete-status-<?= $article[
                                                    "id"
                                                ] ?>"></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="admin.php" class="bender-btn bender-btn-outline">Back to All Users</a>
                    </div>
                </div>
                <?php else: ?>
                <!-- All Articles Section -->
                <div class="admin-card">
                    <h3>All Articles</h3>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Skin</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($articles)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No articles found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($articles as $article): ?>
                                        <tr>
                                            <td><?= $article["id"] ?></td>
                                            <td><?= htmlspecialchars(
                                                $article["title"],
                                            ) ?></td>
                                            <td>User #<?= $article[
                                                "user_id"
                                            ] ?></td>
                                            <td><?= htmlspecialchars(
                                                $article["skin_id"] ??
                                                    "Default",
                                            ) ?></td>
                                            <td>
                                                <a href="view_article.php?id=<?= $article[
                                                    "id"
                                                ] ?>" class="bender-btn btn-sm mr-2">View</a>
                                                <button class="bender-btn bender-btn-outline btn-sm" onclick="deleteArticle(<?= $article[
                                                    "id"
                                                ] ?>)">Delete</button>
                                                <span class="badge" style="display:none; margin-left:5px;" id="delete-status-<?= $article[
                                                    "id"
                                                ] ?>"></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="index.php" class="bender-btn bender-btn-outline">Back to Home</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include "assets/templates/footer.php"; ?>
