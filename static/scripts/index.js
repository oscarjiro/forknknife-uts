import { getMenu, deleteMenu, getOrder, orderMenu } from "./api.js";
import {
    emptyMenuMessage,
    systemError,
    menuItem,
    addIcon,
    minusIcon,
} from "./components.js";
import { MENU_CATEGORIES } from "./const.js";
import {
    checkMenuCategory,
    clearQueryParam,
    closeModal,
    fadeInMain,
    getParam,
    popUpModal,
    toCamelCase,
    toCurrency,
} from "./utils.js";

$(document).ready(async () => {
    // Load all menu
    await activatePage("All", true);

    // Load order
    await loadOrderCount();

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

    // Open menu if there is param
    const paramId = parseInt(getParam("id"));
    $(`#orderMenuItem${paramId}`)?.[0]?.scrollIntoView();
    $(`#orderMenuItem${paramId}`).click();
    clearQueryParam();

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
    isAuthenticated: false,
    orderId: null,
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
    states.isAuthenticated = result.isAuthenticated;

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

// Order menu
const confirmOrderMenu = async (menuId, quantity) => {
    const response = await orderMenu(menuId, quantity, states.orderId);
    if (!response.ok) {
        console.error(response.error);
        return;
    }
    await loadOrderCount();
};

// Load order count
const loadOrderCount = async () => {
    const response = await getOrder(states.orderId);
    if (!response.ok) {
        console.error(response.error);
        return;
    }

    // Get result
    const result = response.result;
    states.orderId = result.orderId;

    // Update count
    $("#cartItemCount").text(result.totalQuantity);
    $("#cartItemCountExp").text(result.totalQuantity);
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
                } else {
                    $(`#orderMenuItem${id}`).click(() => {
                        // Set local states
                        const itemStates = {
                            price: price,
                            totalPrice: price,
                            quantity: 1,
                        };

                        // Pop up blackout and modal
                        $("body").append(`
                            <section id="blackout" class="hidden opacity-0">
                                <div class="flex flex-col space-y-5 bg-[rgba(var(--fg-rgb),0.7)] backdrop-blur-lg text-[rgb(var(--bg-rgb))] p-12 text-xl rounded-xl overflow-hidden">
                                    <div>
                                        <div class="menu-label-category">
                                            ${category}
                                        </div>
                                        <div class="menu-name">
                                            ${name}
                                        </div>
                                    </div>
                                    <div class="h-[200px] w-full overflow-hidden rounded-lg">
                                        <img src="static/menu_images/${image_name}" class="w-full h-full object-cover" />
                                    </div>
                                    <div class="text-lg font-light">
                                        ${description}
                                    </div>
                                    <div class="w-full flex items-center justify-between">
                                        ${minusIcon(id)}
                                        <div id="quantityMenuItem${id}">${
                            itemStates.quantity
                        }</div>
                                        ${addIcon(id)}
                                    </div>
                                    <div class="flex items-center justify-between space-x-4">
                                        <div class="text-upperwide flex justify-center py-2 px-4 border border-[rgb(var(--bg-rgb))] rounded-lg font-bold">
                                            IDR
                                        </div>
                                        <div id="totalPriceMenuItem${id}" class="w-full py-2 text-center font-light bg-[rgb(var(--bg-rgb))] text-[rgb(var(--fg-rgb))] text-upperwide rounded-lg">
                                            ${toCurrency(
                                                itemStates.totalPrice
                                            ).replace("IDR ", "")}
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-6 justify-between">
                                        <button id="cancelAddButton" class="button-red">
                                            Cancel
                                        </button>
                                        <button id="confirmOrderMenu${id}" class="button-white">
                                            Order
                                        </button>
                                    </div>
                                </div>
                            </section>
                        `);

                        // Show blackout
                        $("#blackout").removeClass("hidden");
                        setTimeout(
                            () => $("#blackout").removeClass("opacity-0"),
                            100
                        );

                        // Update state and UI
                        const updateStateAndUI = () => {
                            itemStates.totalPrice =
                                itemStates.price * itemStates.quantity;

                            // Update UI
                            $(`#quantityMenuItem${id}`).text(
                                itemStates.quantity
                            );
                            $(`#totalPriceMenuItem${id}`).text(
                                toCurrency(itemStates.totalPrice).replace(
                                    "IDR ",
                                    ""
                                )
                            );
                        };

                        // Add quantity
                        const addQuantity = () => {
                            itemStates.quantity++;
                            updateStateAndUI();
                        };
                        $(`#addQuantityMenu${id}`).click(addQuantity);

                        // Minus quantity
                        const minusQuantity = () => {
                            // Update state
                            itemStates.quantity =
                                itemStates.quantity <= 1
                                    ? 1
                                    : itemStates.quantity - 1;
                            updateStateAndUI();
                        };
                        $(`#minusQuantityMenu${id}`).click(minusQuantity);

                        // Confirm order menu
                        const confirmOrderItem = async () => {
                            if (!states.isAuthenticated) {
                                window.location.href = `login.php?next=index.php?id=${id}`;
                            }
                            await confirmOrderMenu(id, itemStates.quantity);
                            closeBlackout();
                        };
                        $(`#confirmOrderMenu${id}`).click(confirmOrderItem);

                        // Close blackout event listener
                        const closeBlackout = () => {
                            $("#blackout").addClass("opacity-0");
                            setTimeout(() => $("#blackout").remove(), 300);

                            // Detach event listeners
                            $(`#addQuantityMenu${id}`).off(
                                "click",
                                addQuantity
                            );
                            $(`#minusQuantityMenu${id}`).off(
                                "click",
                                minusQuantity
                            );
                            $("#cancelAddButton").off("click", closeBlackout);
                            $(`#confirmOrderMenu${id}`).off(
                                "click",
                                confirmOrderMenu
                            );
                        };
                        $("#cancelAddButton").click(closeBlackout);
                    });
                }
            }
        }
    );

    // On scroll show
    document.querySelectorAll(".menu-item").forEach((menu) => {
        const io = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    menu.classList.add("slide-in-smooth");
                    menu.classList.remove("opacity-0");
                    observer.disconnect();
                }
            });
        });

        io.observe(menu);
    });

    // Update menu count if reload is true
    updateMenuCount();
};
