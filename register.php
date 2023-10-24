<?php

require_once(__DIR__ . "/init.php");

$param_next = isset($_GET["next"]) ? $_GET["next"] : null;

$post_req = $_SERVER["REQUEST_METHOD"] === "POST";
if ($post_req) {
    // Collect POST data
    $username = clean_data($_POST["username"]);
    $email = clean_data($_POST["email"]);
    $first_name = ucwords(clean_data($_POST["firstName"]));
    $last_name = ucwords(clean_data($_POST["lastName"]));
    $role = strtolower(clean_data($_POST["role"]));
    $gender = ucfirst(clean_data($_POST["gender"]));
    $password = clean_data($_POST["password"]);
    $confirm_password = clean_data($_POST["confirmPassword"]);

    // Check form validity
    $valid_username = preg_match(USERNAME_REGEXP, $username);
    $valid_email = strlen($email) > 0 && strlen($email) <= EMAIL_MAX_LENGTH && preg_match(EMAIL_REGEXP, $email);
    $valid_first_name = strlen($first_name) > 0 && strlen($first_name) <= FIRST_NAME_MAX_LENGTH;
    $valid_last_name = strlen($last_name) > 0 && strlen($last_name) <= LAST_NAME_MAX_LENGTH;
    $valid_gender = $gender === "M" || $gender === "F";
    $valid_password = preg_match(PASSWORD_REGEXP, $password);
    $valid_confirm_password = $password === $confirm_password;

    // Proceed to insert data if all is valid
    if ($valid_form = $valid_username && $valid_email && $valid_first_name && $valid_last_name && $valid_gender && $valid_password && $valid_confirm_password) {
        // Boolean
        $user_exists = false;
        $email_exists = false;
        $query_success = true;

        // Check if user exists
        $check_username_query = "SELECT * FROM User
                        WHERE username = :username";
        try {
            $stmt = $pdo->prepare($check_username_query);
            $stmt->bindParam(":username", $username, PDO::PARAM_STR);
            $stmt->execute();
        } catch (PDOException $e) {
            $query_success = false;
            $database_error = $e->getMessage();
        }
        $check_username_result = $stmt->fetch(PDO::FETCH_ASSOC);
        $user_exists = $check_username_result && count($check_username_result) > 0 && $query_success;

        // Check if email exists
        $check_email_query = "SELECT * FROM User
                        WHERE username = :email";
        try {
            $stmt = $pdo->prepare($check_email_query);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->execute();
        } catch (PDOException $e) {
            $query_success = false;
            $database_error = $e->getMessage();
        }
        $check_email_result = $stmt->fetch(PDO::FETCH_ASSOC);
        $email_exists = $check_email_result && count($check_email_result) > 0 && $query_success;

        // If user and email does not exist yet, insert user to database
        if (!$user_exists && !$email_exists) {
            $password_hashed = password_hash($password, PASSWORD_DEFAULT);
            $is_admin = $role === "admin" ? true : false;
            $insert_query = "INSERT INTO User 
                                (username, email, first_name, last_name, password, is_admin, gender)
                            VALUES 
                                (:username, :email, :first_name, :last_name, :password, :is_admin, :gender)";
            try {
                $stmt = $pdo->prepare($insert_query);
                $stmt->bindParam(":username", $username, PDO::PARAM_STR);
                $stmt->bindParam(":email", $email, PDO::PARAM_STR);
                $stmt->bindParam(":first_name", $first_name, PDO::PARAM_STR);
                $stmt->bindParam(":last_name", $last_name, PDO::PARAM_STR);
                $stmt->bindParam(":last_name", $last_name, PDO::PARAM_STR);
                $stmt->bindParam(":password", $password_hashed, PDO::PARAM_STR);
                $stmt->bindParam(":is_admin", $is_admin, PDO::PARAM_BOOL);
                $stmt->bindParam(":gender", $gender, PDO::PARAM_STR);
                $stmt->execute();
                $_SESSION["is_authenticated"] = true;
                $_SESSION["is_admin"] = $is_admin;
                $_SESSION["username"] = $username;
                $_SESSION["name"] = $select_result["name"];
                header("Location: " . ($param_next ? $param_next : "index.php"));
            } catch (PDOException $e) {
                $query_success = false;
                $database_error = $e->getMessage();
            }
        }
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <?= head("Register") ?>
    <script src="static/scripts/register.js" type="module"></script>
</head>

<body>
    <!-- Navbar -->
    <?= navbar(false, "register") ?>

    <!-- Main -->
    <main class="form-main opacity-0">
        <!-- Form -->
        <form id="registerForm" action="register.php<?= $param_next ? "?next=$param_next" : "" ?>" method="post">
            <!-- Heading -->
            <h1 class="form-header">
                Sign up to get started.
            </h1>

            <div class="flex w-full flex-col space-y-6 min-[500px]:flex-row min-[500px]:space-y-0 min-[500px]:items-center min-[500px]:justify-between">
                <!-- First name -->
                <div class="input-ctr min-[500px]:w-[46%]">
                    <label for="firstName">First name</label>
                    <input type="text" id="firstName" name="firstName" spellcheck="false">
                    <?= ($post_req && !$valid_first_name) ? error_message(is_empty($first_name) ? empty_error("First name") : ERROR["first_name"], "firstName") : "" ?>
                </div>

                <!-- Last name -->
                <div class="input-ctr min-[500px]:w-[46%]">
                    <label for="lastName">Last name</label>
                    <input type="text" id="lastName" name="lastName" spellcheck="false">
                    <?= ($post_req && !$valid_last_name) ? error_message(is_empty($last_name) ? empty_error("Last name") : ERROR["last_name"], "lastName") : "" ?>
                </div>
            </div>

            <!-- Username -->
            <div class="input-ctr">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" spellcheck="false">
                <?= ($post_req && (!$valid_username || $user_exists)) ? error_message(!$valid_username ? (is_empty($username) ? empty_error("username") : ERROR["username"]) : "Username already exists.", "username") : "" ?>
            </div>

            <!-- Email -->
            <div class="input-ctr">
                <label for="email">Email</label>
                <input type="text" id="email" name="email" spellcheck="false">
                <?= ($post_req && (!$valid_email || $email_exists)) ? error_message(!$valid_email ? (is_empty($email) ? empty_error("email") : ERROR["email"]) : "Email has already been used.", "email") : "" ?>
            </div>

            <!-- Role -->
            <div class="input-ctr">
                <label for="role">Role</label>
                <input type="hidden" id="role" name="role" spellcheck="false" value="user">
                <div id="roleChoices" class="input-choices-ctr">
                    <button type="button" id="roleChoiceUser" data-value="user" class="button-black-active">User</button>
                    <button type="button" id="roleChoiceAdmin" data-value="admin" class="button-black">Admin</button>
                </div>
            </div>

            <!-- Gender -->
            <div class="input-ctr">
                <label for="gender">Gender</label>
                <input type="hidden" id="gender" name="gender" spellcheck="false">
                <div id="genderChoices" class="input-choices-ctr">
                    <button type="button" id="genderChoiceMale" data-value="M" class="button-black">Male</button>
                    <button type="button" id="genderChoiceFemale" data-value="F" class="button-black">Female</button>
                </div>
                <?= ($post_req && !$valid_gender) ? error_message(is_empty($gender) ? empty_error("gender") : ERROR["gender"], "gender") : "" ?>
            </div>

            <!-- Password -->
            <div class="input-ctr">
                <label for="password">Password</label>
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
                <label for="confirmPassword">Confirm Password</label>
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

            <!-- Submit button -->
            <button type="submit" class="button-black-active">Register</button>

            <!-- Form error message -->
            <?=
            ($post_req && $valid_form && !$query_success) ?
                ("<div class=\"text-center\">" .
                    error_message(ERROR["general"], "form") .
                    "</div>"
                ) : ""
            ?>

            <!-- Login redirect -->
            <div class="text-center">
                Already have an account? <a href="login.php<?= $param_next ? "?next=$param_next" : "" ?>" class="text-link">Login here.</a>
            </div>
        </form>
    </main>
</body>

</html>