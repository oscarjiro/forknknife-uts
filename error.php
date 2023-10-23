<?php

// Require dependencies
require_once(__DIR__ . "/init.php");

// Redirect to index if no error
if (!isset($e) && !isset($error_scope)) {
    header("Location: index.php");
    exit;
}


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <?= head() ?>
</head>

<body>
    <!-- Navbar -->
    <?= navbar(is_authenticated()) ?>

    <!-- Main -->
    <main class="flex flex-col w-full space-y-10">
        <?= system_error($e->getMessage(), $error_scope) ?>
    </main>
</body>

</html>