import { getOrder } from "./api.js";
import { fadeInMain } from "./utils.js";

$(document).ready(async () => {
    // Load order count
    await loadOrderCount();

    // Fade in main
    fadeInMain();
});

// States
const states = {
    orderId: null,
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
