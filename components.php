<?php

require_once(__DIR__ . "/config.php");

// Reject access to this route
$route = basename($_SERVER["PHP_SELF"]);
if ($route === "components.php") {
    header("Location: index.php");
}

function icon($class)
{
    return "
        <svg xmlns=\"http://www.w3.org/2000/svg\" class=\"$class icon icon-tabler icon-tabler-tools-kitchen-2\"viewBox=\"0 0 24 24\" stroke-width=\"1\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\">
            <path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"></path>
            <path d=\"M19 3v12h-5c-.023 -3.681 .184 -7.406 5 -12zm0 12v6h-1v-3m-10 -14v17m-3 -17v3a3 3 0 1 0 6 0v-3\"></path>
        </svg>
    ";
}

function head($title = null)
{
    return "
        <meta charset=\"UTF-8\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
        <title>fork & knife" . ($title ? " | $title" : "") . "</title>
        <link rel=\"icon\" type=\"image/svg+xml\" href=\"static/favicon.svg\">
        <link rel=\"stylesheet\" href=\"static/styles/styles.css\">
        <link rel=\"stylesheet\" href=\"static/styles/burger.css\">
        <script src=\"https://code.jquery.com/jquery-3.7.1.js\" integrity=\"sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=\" crossorigin=\"anonymous\"></script>
        <script src=\"static/scripts/global.js\" type\"module\"></script>
    ";
}

function shopping_cart($class, $active, $exp = false)
{
    $text_color = !$exp ? ($active ? "text-active" : "text-inactive") : ($active ? "text-[rgb(var(--bg-rgb))]" : "text-[rgba(var(--bg-rgb),0.7)]");
    $text_color_hover = !$exp ? "group-hover:text-active" : "group-hover:text-[rgb(var(--bg-rgb))]";
    $count_bg = !$exp ? ($active ? "bg-[rgb(var(--fg-rgb))]" : "bg-[rgba(var(--fg-rgb),0.7)]") : ($active ? "bg-[rgb(var(--bg-rgb))]" : "bg-[rgba(var(--bg-rgb),0.7)]");
    $count_bg_hover = !$exp ?  "group-hover:bg-[rgb(var(--fg-rgb))]" : "group-hover:bg-[rgb(var(--white-rgb))]";
    $count_color = !$exp ? "text-[rgb(var(--bg-rgb))]" : "text-[rgb(var(--fg-rgb))]";
    $count_id = "cartItemCount" . ($exp ? "Exp" : "");
    $text_opacity = !$exp ? "opacity-0" : "";

    return "
        <a href=\"checkout.php\" class=\"relative cursor-pointer $text_color group smooth\">
            <svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\" class=\"$class smooth group-hover:-rotate-[15deg] $text_color_hover delay-0\">
                <path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z\" />
            </svg>
            <div class=\"flex items-center justify-center text-xs absolute top-[5px] left-[20px] p-2 w-[25px] h-[25px] min-w-[25px] min-h-[25px] $count_color $count_bg smooth $count_bg_hover rounded-full overflow-hidden\">
                <span id=\"$count_id\" class=\"$text_opacity smooth\">0</span>
            </div>
        </a>
  ";
}

function navbar($authenticated = true, $active = null, $is_admin = false)
{
    $icon = icon("nav-logo-icon");
    $admin_label = $is_admin
        ? ("
            <div class=\"nav-logo-label\">
                Admin
            </div>
            ")
        : "";
    $admin_links = "
        <a href=\"add.php\" class=\"nav-link " . ($active === "add" ? "text-active" : "") . "\">
            Add
        </a>";
    $admin_links_exp = "
        <a href=\"add.php\" class=\"nav-link-exp " . ($active === "add" ? "text-[rgb(var(--bg-rgb))]" : "") . "\">
            Add
        </a>";
    $user_links = "
        <a href=\"history.php\" class=\"nav-link " . ($active === "history" ? "text-active" : "") . "\">
            History
        </a>" .
        shopping_cart("w-6 h-6", $active === "checkout");
    $user_links_exp = "
        <a href=\"history.php\" class=\"nav-link-exp " . ($active === "history" ? "text-[rgb(var(--bg-rgb))]" : "") . "\">
            History
        </a>" .
        shopping_cart("w-6 h-6", $active === "checkout", true);
    $authenticated_links = (!$is_admin ? $user_links : $admin_links) . "
        <a href=\"logout.php\" class=\"nav-link\">
            Logout
        </a>
    ";
    $unauthenticated_links = "
        <a href=\"login.php\" class=\"nav-link " . ($active === "login" ? "text-active" : "") . "\">
            Login
        </a>
        <a href=\"register.php\" class=\"nav-link " . ($active === "register" ? "text-active" : "") . "\">
            Register
        </a>
    ";
    $authenticated_exp_links = (!$is_admin ? $user_links_exp : $admin_links_exp) . "
        <a href=\"logout.php\" class=\"nav-link-exp\">
            Logout
        </a>
    ";
    $unauthenticated_exp_links = "
        <a href=\"login.php\" class=\"nav-link-exp " . ($active === "login" ? "text-[rgb(var(--bg-rgb))]" : "") . "\">
            Login
        </a>
        <a href=\"register.php\" class=\"nav-link-exp " . ($active === "register" ? "text-[rgb(var(--bg-rgb))]" : "") . "\">
            Register
        </a>
    ";
    $links = $authenticated ? $authenticated_links : $unauthenticated_links;
    $links_exp = $authenticated ? $authenticated_exp_links : $unauthenticated_exp_links;

    return "
        <nav>
            <a href=\"index.php\">
                <div class=\"nav-logo-ctr group\">
                    $icon
                    <div class=\"nav-logo-text\">
                        fork & knife
                    </div>
                    <div class=\"nav-logo-text hidden min-[330px]:block min-[550px]:hidden\">
                        f&k
                    </div>
                    $admin_label
                </div>
            </a>
            <div id=\"navbarLinks\" class=\"nav-link-ctr\">
                <a href=\"index.php\" class=\"nav-link " . ($active === "index" ? "text-active" : "") . "\">
                    Home
                </a>
                $links
            </div>
            <button id=\"navbarToggle\" class=\"navbar-toggle smooth min-[850px]:hidden\" aria-expanded=\"false\">
                <svg stroke=\"rgb(var(--black-rgb))\" class=\"hamburger\" viewBox=\"0 0 100 100\" width=\"30\">
                    <line class=\"line top\" x1=\"90\" x2=\"10\" y1=\"40\" y2=\"40\" stroke-width=\"5\" stroke-linecap=\"round\" stroke-dasharray=\"80\" stroke-dashoffset=\"0\">
                    </line>
                    <line class=\"line bottom\" x1=\"10\" x2=\"90\" y1=\"60\" y2=\"60\" stroke-width=\"5\" stroke-linecap=\"round\" stroke-dasharray=\"80\" stroke-dashoffset=\"0\">
                    </line>
                </svg>
            </button>
        </nav>
        <section id=\"expandedNavbar\" class=\"opacity-0 translate-x-[300px] hidden smooth min-[850px]:hidden overflow-hidden py-12 px-8 flex flex-col items-center space-y-16 fixed h-screen z-[1] top-[var(--navbar-height)] right-0 w-screen max-w-full min-[500px]:w-[300px] bg-[rgb(var(--fg-rgb))] text-[rgb(var(--bg-rgb))]\">
            <a href=\"index.php\" class=\"flex items-center space-x-4\">
                <div class=\"hidden min-[500px]:block\">
                    $icon
                </div>
                <div class=\"nav-logo-text block text-2xl min-[280px]:text-3xl min-[500px]:hidden\">
                    fork & knife
                </div>
            </a>
            <div class=\"flex flex-col items-center justify-center space-y-5\">
                <a href=\"index.php\" class=\"nav-link-exp " . ($active === "index" ? "text-[rgb(var(--bg-rgb))]" : "") . "\">
                    Home
                </a>
                $links_exp
            </div>
        </section>
    ";
}

function error_message($message, $id)
{
    return "
        <div id=\"{$id}ErrorMessage\" class=\"error-msg\">
            $message
        </div>
    ";
}

function system_error($message, $scope = null)
{
    return "
        <div class=\"database-error\">
            <div class=\"text-invalid\">" .
        ($scope ? $scope : ERROR["general"]) .
        "</div>
            <div class=\"database-error-msg\">
                <code>$message</code>
            </div>
        </div>
    ";
}
