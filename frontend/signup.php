<?php
session_start();
$page_title = "Sign Up - Bender";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = [
        "username" => $_POST["username"],
        "password" => $_POST["password"],
    ];
    $ch = curl_init("http://localhost:5000/api/signup");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (!empty($response["success"])) {
        header("Location: login.php");
        exit();
    } else {
        $error = $response["error"] ?? "Signup failed";
    }
}

include "assets/templates/header.php";
?>
<div class="row">
    <div class="col-md-6 offset-md-3 col-lg-4 offset-lg-4">
        <div class="content-card">
            <h2 class="text-center mb-4">Sign Up</h2>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger mb-4">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="mb-4">
                <div class="form-group mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input id="username" name="username" class="form-control" placeholder="Choose a username" required>
                </div>

                <div class="form-group mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input id="password" name="password" type="password" class="form-control" placeholder="Choose a password" required>
                </div>

                <div class="text-center">
                    <button type="submit" class="bender-btn w-100">Create Account</button>
                </div>
            </form>

            <div class="text-center mt-3">
                <p>Already have an account? <a href="login.php">Login</a></p>
            </div>
        </div>
    </div>
</div>

<?php include "assets/templates/footer.php"; ?>
