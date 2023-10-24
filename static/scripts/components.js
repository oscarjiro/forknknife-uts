import { ERROR } from "./const.js";
import { capitalizeFirst, toCurrency } from "./utils.js";

// Error message
export const errorMessage = (message, name) => `
    <div id="${name}ErrorMessage" class="error-msg">
        ${message}
    </div>
`;

// Open eye and hidden eye icon
export const passwordToggleHide = `
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
    </svg>
`;

export const passwordToggleVisible = `
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
    </svg>
`;

// Menu action icons
const deleteIcon = (id) => `
    <svg id="deleteMenu${id}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="rgb(var(--red-rgb))" class="menu-action-icon">
        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
    </svg>
`;
const editIcon = `
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="menu-action-icon">
        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
    </svg>
`;
export const addIcon = (id) => `
    <svg id="addQuantityMenu${id}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="menu-action-icon">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
    </svg>
`;
export const minusIcon = (id) => `
    <svg id="minusQuantityMenu${id}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="menu-action-icon">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15" />
    </svg>
`;

// Menu item
export const menuItem = (
    id,
    name,
    description,
    image_name,
    category,
    price,
    isAdmin
) => {
    const menuLabel = isAdmin
        ? `
            <div class="menu-label-price">
                ${toCurrency(price)}
            </div>
        `
        : `
            <div class="menu-label-category">
                ${category}
            </div>
        `;
    const menuDetails = isAdmin
        ? `
            <div class="menu-desc">
                    ${description}
            </div>
            <div class="menu-footer">
                <div class="menu-footer-category">
                    ${category}
                </div>
                <div class="menu-action">
                    ${deleteIcon(id)}
                    <a href="edit.php?id=${id}">
                        ${editIcon}
                    </a>
                </div>
            </div>
        `
        : "";
    const menuOrderButton = isAdmin
        ? ""
        : `
        <div id="orderMenuItem${id}" class="add group">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="add-icon">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            <div class="add-text w-fit">Order menu</div>
        </div>
    `;

    return `
        <div id="menuItem${id}" class="menu-item opacity-0">
            <div class="menu-img-ctr">
                <img src="static/menu_images/${image_name}" alt="${name}" class="menu-img">
            </div>
            <div>
                ${menuLabel}
                <div class="menu-name">
                    ${name}
                </div>
            </div>
            ${menuDetails}
            ${menuOrderButton}
        </div>
    `;
};

// Empty task message
export const emptyMenuMessage = () =>
    `<div class="empty-menu">No item yet.</div>`;

// Database error
export const systemError = (message, scope = null) => `
    <div class="database-error">
        <div class="text-invalid">
            ${scope ? scope : ERROR.general}
        </div>
        <div class="database-error-msg">
            <code>${message}</code>
        </div>
    </div>
`;

// Error message for empty inputs
export const emptyError = (name) => `${capitalizeFirst(name)} must be filled.`;
