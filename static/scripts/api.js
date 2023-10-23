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
            method: "DELETE",
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

// Modify task
export const taskAction = async (taskId, action) => {
    try {
        const response = await fetch("api/task_action.php", {
            method: "PUT",
            body: JSON.stringify({
                taskId: taskId,
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
