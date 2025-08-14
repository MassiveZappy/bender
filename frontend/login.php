<?php
session_start();
$page_title = "Login - Bender";

// Initialize username variable to preserve form input on errors
$username = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Store username to preserve it in case of errors
    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";

    $data = [
        "username" => $_POST["username"],
        "password" => $_POST["password"],
    ];
    $ch = curl_init("http://localhost:5000/api/login");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Check if the API call was successful
    if ($http_code == 200 && $response) {
        $response_data = json_decode($response, true);
        if (!empty($response_data["success"])) {
            $_SESSION["user_id"] = $response_data["user_id"];
            $_SESSION["is_admin"] = $response_data["is_admin"];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = $response_data["error"] ?? "Invalid username or password";
        }
    } else {
        // Handle API connection issues
        $error =
            "Could not connect to authentication service. Please try again later.";
    }
}

include "assets/templates/header.php";
?>
<div class="row">
    <div class="col-md-6 offset-md-3 col-lg-4 offset-lg-4">
        <div class="content-card">
            <h2 class="text-center mb-4">Login</h2>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger mb-4">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="mb-4">
                <div class="form-group mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input id="username" name="username" class="form-control" placeholder="Enter your username" value="<?= htmlspecialchars(
                        $username,
                    ) ?>" required>
                </div>

                <div class="form-group mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input id="password" name="password" type="password" class="form-control" placeholder="Enter your password" value=<?= htmlspecialchars(
                        $password ?? "",
                    ) ?> required>
                </div>

                <div class="text-center">
                    <button type="submit" class="bender-btn w-100">Login</button>
                </div>
            </form>

            <div class="text-center mt-3">
                <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
            </div>
        </div>
    </div>
</div>

<?php include "assets/templates/footer.php"; ?>
