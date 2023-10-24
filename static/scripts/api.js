import { ERROR } from "./const.js";

// Get all menu items
export const getMenu = async () => {
    try {
        const response = await fetch("api/get_menu.php");
        const result = await response.json();
        return result;
    } catch (error) {
        return {
            ok: false,
            result: [],
            error: { message: ERROR.general },
        };
    }
};

// Delete task
export const deleteMenu = async (menuId) => {
    try {
        const response = await fetch("api/delete_menu.php", {
            method: "POST", // Originally DELETE, changed to POST because of 000webhost restriction
            body: JSON.stringify({
                menuId: menuId,
            }),
            headers: { "Content-Type": "application/json" },
        });
        const result = await response.json();
        return result;
    } catch (error) {
        return {
            ok: false,
            error: { message: ERROR.general },
        };
    }
};

// Get active order
export const getOrder = async (orderId = null) => {
    try {
        const response = await fetch(
            `api/get_order.php${orderId ? `?id=${orderId}` : ""}`
        );
        const result = await response.json();
        return result;
    } catch (error) {
        return {
            ok: false,
            result: {
                orderId: null,
                totalPrice: 0,
                totalQuantity: 0,
                details: [],
                orderDate: null,
            },
            error: { message: ERROR.general },
        };
    }
};

// Order a menu item
export const orderMenu = async (menuId, quantity, orderId = null) => {
    try {
        const response = await fetch("api/order_menu.php", {
            method: "POST",
            body: JSON.stringify({
                menuId: menuId,
                quantity: quantity,
                orderId: orderId,
            }),
            headers: { "Content-Type": "application/json" },
        });
        const result = await response.json();
        return result;
    } catch (error) {
        return {
            ok: false,
            error: { message: ERROR.general },
        };
    }
};

// Finish an order
export const finishOrder = async (orderId) => {
    try {
        const response = await fetch("api/confirm_order.php", {
            method: "POST", // Originally PUT, changed to POST because of 000webhost restriction
            body: JSON.stringify({
                orderId: orderId,
            }),
            headers: { "Content-Type": "application/json" },
        });
        const result = await response.json();
        return result;
    } catch (error) {
        return {
            ok: false,
            error: { message: ERROR.general },
        };
    }
};

// Modify order item
export const modifyOrder = async (orderDetailsId, action) => {
    try {
        const response = await fetch("api/modify_order_menu.php", {
            method: "POST",
            body: JSON.stringify({
                orderDetailsId: orderDetailsId,
                action: action,
            }),
            headers: { "Content-Type": "application/json" },
        });
        const result = await response.json();
        return result;
    } catch (error) {
        return {
            ok: false,
            error: { message: ERROR.general },
        };
    }
};

// Send password reset request
export const sendPasswordResetLink = async (email) => {
    try {
        const response = await fetch("api/request_password_reset.php", {
            method: "POST",
            body: JSON.stringify({
                email: email,
            }),
            headers: { "Content-Type": "application/json" },
        });
        const result = await response.json();
        return result;
    } catch (error) {
        return {
            ok: false,
            error: { message: ERROR.general },
        };
    }
};
