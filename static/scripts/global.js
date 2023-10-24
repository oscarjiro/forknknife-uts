$(document).ready(() => {
    setTimeout(() => $("#cartItemCount").removeClass("opacity-0"), 100);

    $(window).scroll(() => {
        const distance = $(window).scrollTop();
        if (distance > 10) {
            $(navbar).attr(
                "class",
                "border-b border-b-[rgb(var(--black-rgb))]"
            );
        } else {
            $(navbar).attr("class", "");
        }
    });

    $(window).resize(() => {
        const width = $(window).width();
        if (width >= 850) {
            $(burger).attr("data-state", "closed");
            $(burger).attr("aria-expanded", "false");
            $(navbar).attr("class", "");
            if (!$(expandedNavbar).hasClass("translate-x-[300px]")) {
                $(expandedNavbar).addClass("translate-x-[300px]");
            }
            if (!$(expandedNavbar).hasClass("opacity-0")) {
                $(expandedNavbar).addClass("opacity-0");
            }
            if (!$(expandedNavbar).hasClass("hidden")) {
                setTimeout(() => $(expandedNavbar).addClass("hidden"), 200);
            }
        }
    });

    $(burger).click(() => {
        const currentState = $(burger).attr("data-state");
        if (!currentState || currentState === "closed") {
            $(burger).attr("data-state", "opened");
            $(burger).attr("aria-expanded", "true");
            $(navbar).attr(
                "class",
                "border-b border-b-[rgb(var(--black-rgb))]"
            );
            $(expandedNavbar).removeClass("hidden");
            setTimeout(
                () =>
                    $(expandedNavbar)
                        .removeClass("translate-x-[300px]")
                        .removeClass("opacity-0"),
                200
            );
        } else {
            $(burger).attr("data-state", "closed");
            $(burger).attr("aria-expanded", "false");
            $(navbar).attr("class", "");
            if (!$(expandedNavbar).hasClass("translate-x-[300px]")) {
                $(expandedNavbar).addClass("translate-x-[300px]");
            }
            if (!$(expandedNavbar).hasClass("opacity-0")) {
                $(expandedNavbar).addClass("opacity-0");
            }
            if (!$(expandedNavbar).hasClass("hidden")) {
                setTimeout(() => $(expandedNavbar).addClass("hidden"), 200);
            }
        }
    });
});

const burger = "#navbarToggle";
const navbar = "nav";
const expNav = "#expandedNavbar";
