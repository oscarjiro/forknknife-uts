<?php

require_once(__DIR__ . "/init.php");

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <?= head("Forgot Password") ?>
    <script src="static/scripts/forgotPassword.js" type="module"></script>
</head>

<body>
    <!-- Navbar -->
    <?= navbar(false, "login") ?>

    <!-- Main -->
    <main class="form-main opacity-0">
        <section class="w-full min-[700px]:w-[600px] space-y-6">
            <!-- Heading -->
            <h1 class="form-header">
                Forgot your password?
            </h1>

            <!-- Email -->
            <div class="input-ctr">
                <label for="email">Email</label>
                <input type="text" id="email" name="email" spellcheck="false">
            </div>

            <!-- Submit -->
            <button id="sendReset" type="submit" class="button-blue">Reset Password</button>

            <!-- Form error message -->

            <!-- Register redirect -->
            <div class="text-center">
                <a href="login.php" class="text-link">Back to login</a>
            </div>
        </section>
    </main>
</body>

</html>