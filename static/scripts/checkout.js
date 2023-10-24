import { finishOrder, getOrder, modifyOrder } from "./api.js";
import { addIcon, minusIcon, systemError } from "./components.js";
import { ERROR } from "./const.js";
import { closeModal, fadeInMain, popUpModal, toCurrency } from "./utils.js";

$(document).ready(async () => {
    // Load order
    await loadOrder();

    // Checkout event listener
    if (states.orderId) {
        confirmCheckout.click(confirmFinishOrder);
    }

    if (!states.orderId || states.totalQuantity === 0) {
        confirmCheckout.remove();
    }

    // Fade in main
    fadeInMain();
});

// Menu items container
const elements = {
    menuItemsContainer: $("#menuItemsContainer"),
    totalPrice: $("#totalPrice"),
    totalQuantity: $("#totalQuantity"),
    confirmCheckout: $("#confirmCheckout"),
    navCount: $("#cartItemCount"),
    navCountExp: $("#cartItemCountExp"),
};
const {
    menuItemsContainer,
    totalPrice,
    totalQuantity,
    confirmCheckout,
    navCount,
    navCountExp,
} = elements;

// States
const states = {
    orderId: null,
    totalPrice: 0,
    totalQuantity: 0,
    details: [],
    orderDate: null,
};

// Finish order
const confirmFinishOrder = () => {
    // Pop up modal
    popUpModal(
        "Are you sure you want to confirm checkout?",
        "Confirm",
        "button-green"
    );

    const acceptFinishOrder = async () => {
        const response = await finishOrder(states.orderId);
        $("#confirmAction").off("click", acceptFinishOrder);
        if (!response.ok) {
            console.error(response.error);
            closeModal();
            return;
        }
        window.location.href = `history.php#order${states.orderId}`;
    };

    $("#confirmAction").click(acceptFinishOrder);
};

// Modify order item
const modifyOrderItem = async (
    orderDetailsId,
    action,
    itemQuantity,
    itemName
) => {
    // Ensure valid action
    if (!["add", "minus", "delete"].includes(action)) {
        console.error("Invalid action specified.");
        return;
    }

    // Change action to delete if quantity is 1 and action is minus
    if (itemQuantity === 1 && action === "minus") {
        action = "delete";
    }

    // If not delete, immediately perform request
    if (action !== "delete") {
        const response = await modifyOrder(orderDetailsId, action);
        if (!response.ok) {
            console.error(response.error);
            return;
        }
        await loadOrder();
        return;
    }

    // Otherwise, popup confirmation modal
    popUpModal(
        `Are you sure you want to remove your order for <strong class="font-bold">"${itemName}"</strong>?`,
        "Delete",
        "button-red"
    );

    const acceptDeleteOrder = async () => {
        const response = await modifyOrder(orderDetailsId, "delete");
        $("#confirmAction").off("click", acceptDeleteOrder);
        if (!response.ok) {
            console.error(response.error);
            closeModal();
            return;
        }
        await loadOrder();
        closeModal();
    };

    $("#confirmAction").click(acceptDeleteOrder);
};

// Load order items
const loadOrderItems = () => {
    menuItemsContainer.html("");
    if (states.details.length === 0) {
        menuItemsContainer.html(`
            <div class="empty-menu">
                You haven't ordered anything! <br> <a href="index.php" class="text-link">View our menu</a> to start ordering.
            </div>
        `);
        return;
    }
    states.details.forEach(
        ({ name, category, price, quantity, subtotal, orderDetailsId }) => {
            menuItemsContainer.append(`
                <div 
                    class="p-4 
                            border-b border-b-[rgb(var(--fg-rgb))]
                            flex flex-col space-y-4
                            min-[1000px]:space-y-0 min-[1000px]:flex-row
                            min-[1000px]:justify-between min-[1000px]:items-center"
                >
                    <div class="min-[600px]:space-y-1">
                        <div class="text-general-small text-upperwide">${category}</div>
                        <div class="text-general-header tracking-tighter">${name}</div>
                    </div>
                    <div 
                        class="min-[1000px]:text-right
                                flex flex-col space-y-2
                                min-[1000px]:space-y-0 min-[1000px]:flex-row
                                min-[1000px]:justify-between min-[1000px]:items-center min-[1000px]:space-x-5"
                    >
                        <div class="min-[600px]:space-y-1">
                            <div class="text-general-small text-upperwide">
                                ${quantity} x ${toCurrency(price)}
                            </div>
                            <div class="text-general-header font-bold">
                                ${toCurrency(subtotal)}
                            </div>
                        </div>
                        <div 
                            class="flex items-center space-x-8
                                    min-[1000px]:flex-col min-[1000px]:items-start
                                    min-[1000px]:space-x-0 min-[1000px]:space-y-1"
                        >
                            ${addIcon(orderDetailsId)}
                            ${minusIcon(orderDetailsId)}
                            <svg id="deleteItem${orderDetailsId}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="menu-action-icon">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                    </div>
                </div>
            `);

            $(`#addQuantityMenu${orderDetailsId}`).click(
                async () =>
                    await modifyOrderItem(orderDetailsId, "add", quantity, name)
            );
            $(`#minusQuantityMenu${orderDetailsId}`).click(
                async () =>
                    await modifyOrderItem(
                        orderDetailsId,
                        "minus",
                        quantity,
                        name
                    )
            );
            $(`#deleteItem${orderDetailsId}`).click(
                async () =>
                    await modifyOrderItem(
                        orderDetailsId,
                        "delete",
                        quantity,
                        name
                    )
            );
        }
    );
};

// Load price and quantity
const loadPriceQuantity = () => {
    totalPrice.text(toCurrency(states.totalPrice));
    totalQuantity.text(
        `${states.totalQuantity} item${states.totalQuantity > 1 ? "s" : ""}`
    );
    navCount.text(states.totalQuantity);
    navCountExp.text(states.totalQuantity);
};

// Load order count
const loadOrder = async () => {
    const response = await getOrder();
    if (!response.ok) {
        console.error(response.error);
        menuItemsContainer.html(
            systemError(response.error.message, ERROR.general)
        );
    }

    // Load states
    const result = response.result;
    for (const key in result) {
        states[key] = result[key];
    }

    // Load price and quantity
    loadPriceQuantity();

    // Load items
    loadOrderItems();
};
