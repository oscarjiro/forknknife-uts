import { getMenu, deleteMenu, taskAction } from "./api.js";
import { emptyMenuMessage, systemError, menuItem } from "./components.js";
import { MENU_CATEGORIES } from "./const.js";
import {
    capitalizeFirst,
    checkMenuCategory,
    closeModal,
    fadeInMain,
    popUpModal,
    toCamelCase,
} from "./utils.js";

$(document).ready(async () => {
    // Load all menu
    await activatePage("All", true);

    // Add event listener to each section
    for (const section in sections) {
        sections[section].element.click(() => {
            const sameSection =
                section === "All"
                    ? !MENU_CATEGORIES.includes(states.activePage)
                    : states.activePage === sections;
            !sameSection && activatePage(section);
        });
    }

    // Fade in main
    fadeInMain();
});

// Elements
const menuContainer = $("#menuContainer");

// States
const states = {
    activePage: "All",
    menuCount: {
        All: 0,
    },
    menu: [],
    isAdmin: false,
};

// Sections
const sections = {
    All: {
        element: $("#allSection"),
        count: $("#allSectionCount"),
    },
};

MENU_CATEGORIES.forEach((category) => {
    sections[category] = {
        element: $(`#${toCamelCase(category)}Section`),
        count: $(`#${toCamelCase(category)}SectionCount`),
    };
    states.menuCount[category] = 0;
});

// Reset menu count
const resetMenuCount = () => {
    for (const progress in states.menuCount) {
        states.menuCount[progress] = 0;
    }
};

// Update menu count
const updateMenuCount = () => {
    // Reset menu count
    resetMenuCount();

    // Update menu count
    states.menu.forEach(({ category }) => {
        states.menuCount[category]++;
        states.menuCount.All++;
    });

    // Update each menu count
    for (const category in sections) {
        // Update UI count
        sections[category].count.html(states.menuCount[category]);

        // Update container to empty message if 0 menu
        if (
            states.activePage === category &&
            states.menuCount[category] === 0
        ) {
            menuContainer
                .attr("class", "empty-menu-container")
                .html(emptyMenuMessage);
        }
    }

    // Update container indefinitely if all menu are empty
    if (states.menuCount.All === 0) {
        menuContainer
            .attr("class", "empty-menu-container")
            .html(emptyMenuMessage);
    }
};

// Load all menu
const loadMenu = async () => {
    // Get all menu
    const result = await getMenu();

    // If error in response, show error
    if (!result.ok) {
        console.error(result.error);
    }

    // Otherwise, set menu state as result
    states.menu = result.result;
    states.isAdmin = result.isAdmin;

    // Return result
    return result;
};

// Confirm delete
const confirmDeleteMenu = (menuId, menuName) => {
    // Pop up modal
    popUpModal(
        `Are you sure you want to delete <strong class="font-bold">"${menuName}"</strong>?`,
        "Delete",
        "button-red"
    );

    // Execute task on confirmation event listener
    const acceptDeleteMenu = async () => {
        // Delete task
        const response = await deleteMenu(menuId);

        // If error in response, close modal and return
        if (!response.ok) {
            console.error(response.error);
            $("#confirmAction").off("click", acceptDeleteMenu);
            closeModal();
            return;
        }

        // If successful response, refresh tasks
        await activatePage(states.activePage, true);

        // Remove event listener and close modal
        $("#confirmAction").off("click", acceptDeleteMenu);
        closeModal();
    };

    // Add event listener to confirm button
    $("#confirmAction").click(acceptDeleteMenu);
};

// Activate page
const activatePage = async (section = null, reload = false) => {
    // Set active page state
    states.activePage = checkMenuCategory(section) ? section : "All";

    // Change section header style
    for (const section in sections) {
        const classSuffix = section === states.activePage ? "-active" : "";
        sections[section].element.attr(
            "class",
            `section-title-item${classSuffix} group`
        );
        sections[section].count.attr(
            "class",
            `section-title-count${classSuffix}`
        );
    }

    // If reload is set to true, reload menu
    if (reload) {
        const response = await loadMenu();

        // If unsuccessful, show error and return
        if (!response.ok) {
            menuContainer
                .attr("class", "empty-menu-container")
                .html(
                    systemError(response.error.message, response.error?.scope)
                );
            return;
        }
    }

    // Append every menu
    menuContainer.attr("class", "menu-container").html("");
    states.menu.forEach(
        ({ id, name, description, image_name, category, price }) => {
            if (
                !MENU_CATEGORIES.includes(states.activePage) ||
                states.activePage === category
            ) {
                menuContainer.append(
                    menuItem(
                        id,
                        name,
                        description,
                        image_name,
                        category,
                        price,
                        states.isAdmin
                    )
                );

                if (states.isAdmin) {
                    $(`#deleteMenu${id}`).click(() =>
                        confirmDeleteMenu(id, name)
                    );
                }
            }
        }
    );

    document.querySelectorAll(".menu-item").forEach((menu) => {
        const io = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    menu.classList.add("slide-in-smooth");
                    observer.disconnect();
                }
            });
        });

        io.observe(menu);
    });

    /**
     // Add intersection observer to each task
    const showOnScroll = (task) => {
        const io = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    task.classList.add("slide-right-smooth");
                    observer.disconnect();
                }
            });
        });

        io.observe(task);
    };
    document.querySelectorAll(".task").forEach(showOnScroll);
     */

    // Update menu count if reload is true
    updateMenuCount();
};
