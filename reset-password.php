<?php

require_once(__DIR__ . "/init.php");

// Ensure token is present
$param_token = isset($_GET["token"]) ? clean_data($_GET["token"]) : null;
if (!$param_token) {
    header("Location: index.php");
    exit;
}

// Get hashed token of param token
$param_token_hashed = hash("sha256", $param_token);

// Check if hashed token matches any user
$select_token_query = "SELECT * FROM User
                    WHERE reset_token_hash = :reset_token_hash";
try {
    $stmt = $pdo->prepare($select_token_query);
    $stmt->bindParam(":reset_token_hash", $param_token_hashed, PDO::PARAM_STR);
    $stmt->execute();
} catch (PDOException $e) {
    header("Location: index.php");
    exit;
}

// Ensure user with token exists
$select_token_result = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$select_token_result || count($select_token_result) === 0) {
    header("Location: index.php");
    exit;
}

// If token has expired, remove it from the database and return to login
$username = $select_token_result["username"];
$token_expiry_time = $select_token_result["reset_token_expires_at"];
if (strtotime($token_expiry_time) <= time()) {
    $reset_token_query = "UPDATE User
                        SET reset_token_hash = NULL, reset_token_expires_at = NULL
                        WHERE username = :username";
    try {
        $stmt = $pdo->prepare($reset_token_query);
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);
        $stmt->execute();
    } catch (PDOException $e) {
    }
    header("Location: index.php");
    return;
}

$post_req = $_SERVER["REQUEST_METHOD"] === "POST";

if ($post_req) {
    // Collect POST data
    $password = clean_data($_POST["password"]);
    $confirm_password = clean_data($_POST["confirmPassword"]);

    // Check form data validity
    $valid_password = preg_match(PASSWORD_REGEXP, $password);
    $valid_confirm_password = $password === $confirm_password;
    $valid_form = $valid_password && $valid_confirm_password;

    // Proceed to insert data if all is valid
    if ($valid_form) {
        // Successful authentication boolean
        $query_success = true;

        // Hash password
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);

        // Check if user exists
        $reset_password_query = "UPDATE 
                                    User
                                SET 
                                    password = :password, 
                                    reset_token_hash = NULL, 
                                    reset_token_expires_at = NULL
                                WHERE 
                                    username = :username";
        try {
            $stmt = $pdo->prepare($reset_password_query);
            $stmt->bindParam(":password", $password_hashed, PDO::PARAM_STR);
            $stmt->bindParam(":username", $username, PDO::PARAM_STR);
            $stmt->execute();
        } catch (PDOException $e) {
            $query_success = false;
            $database_error = $e->getMessage();
        }

        // Try to login
        if ($query_success) {
            header("Location: index.php");
            exit;
        }
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <?= head("Login") ?>
    <script src="static/scripts/resetPassword.js" type="module"></script>
</head>

<body>
    <!-- Navbar -->
    <?= navbar(false, "login") ?>

    <!-- Main -->
    <main class="form-main opacity-0">
        <!-- Form -->
        <form id="resetPasswordForm" action="reset-password.php?token=<?= $param_token ?>" method="post">
            <!-- Heading -->
            <h1 class="form-header">
                Create a new password.
            </h1>

            <!-- Password -->
            <div class="input-ctr">
                <label for="password">New Password</label>
                <div id="passwordInput" class="input-password-ctr">
                    <input type="password" id="password" name="password" spellcheck="false" class="w-full border-none">
                    <div id="toggleViewPassword" class="input-password-toggle">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
                <?= ($post_req && !$valid_password) ? error_message(is_empty($password) ? empty_error("password") : ERROR["password"], "password") : "" ?>
            </div>

            <!-- Confirm password -->
            <div class="input-ctr">
                <label for="confirmPassword">Confirm New Password</label>
                <div id="confirmPasswordInput" class="input-password-ctr">
                    <input type="password" id="confirmPassword" name="confirmPassword" spellcheck="false" class="w-full border-none">
                    <div id="toggleViewConfirmPassword" class="input-password-toggle">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
                <?= ($post_req && !$valid_confirm_password) ? error_message(ERROR["confirm_password"], "confirmPassword") : "" ?>
            </div>

            <!-- Submit -->
            <button type="submit" class="button-blue">Reset Password</button>

            <!-- Form error message -->
            <?=
            ($post_req && ($valid_form && !$query_success)) ?
                ("<div class=\"text-center\">" .
                    error_message(ERROR["general"], "form") .
                    "</div>"
                ) : ""
            ?>

            <!-- Register redirect -->
            <div class="text-center">
                <a href="login.php" class="text-link">Back to login</a>
            </div>
        </form>
    </main>
</body>

</html>