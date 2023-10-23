import {
    getValue,
    toggleViewPassword,
    isEmpty,
    errorMessageId,
    onlyFillInputHandler,
    onlyFillShowError,
    fadeInMain,
} from "./utils.js";
import { errorMessage, emptyError } from "./components.js";
import { ERROR } from "./const.js";

$(document).ready(() => {
    // userId on input check
    userId.element.on("input", () => onlyFillInputHandler(userId.errorId));

    // Password on input check
    password.element.on("input", () => onlyFillInputHandler(password.errorId));

    // Password visibility toggle
    password.toggle.click(() =>
        toggleViewPassword(password.toggle, password.element)
    );

    // Recaptcha
    $(".recaptcha-checkbox-border").click(() => {
        const recaptchaResponse = grecaptcha.getResponse();
        const validRecaptcha = recaptchaResponse.length !== 0;
        const formErrorMessageId = errorMessageId("form");
        if (validRecaptcha && $(formErrorMessageId).length > 0) {
            $(formErrorMessageId).remove();
        }
    });

    // Form submission handler
    $("#loginForm").submit((event) => formHandler(event));

    // Fade in main
    fadeInMain();
});

// Elements
const names = {
    userId: "userId",
    password: "password",
};

const inputs = {
    userId: {
        element: $(`#${names.userId}`),
        error: errorMessage(emptyError("Username or email"), names.userId),
        errorId: errorMessageId(names.userId),
        errorPlacement: $(`#${names.userId}`),
    },
    password: {
        element: $(`#${names.password}`),
        error: errorMessage(emptyError(names.password), names.password),
        errorId: errorMessageId(names.password),
        errorPlacement: $("#passwordInput"),
        toggle: $("#toggleViewPassword"),
    },
};

const { userId, password } = inputs;

// Form handler
const formHandler = (event) => {
    // Check for form validity
    const recaptchaResponse = grecaptcha.getResponse();
    const validUserId = !isEmpty(getValue(userId.element));
    const validPassword = !isEmpty(getValue(password.element));
    const validRecaptcha = recaptchaResponse.length !== 0;
    const validForm = validUserId && validPassword && validRecaptcha;

    // If invalid, prevent submission
    if (!validForm) {
        event.preventDefault();

        // Add errors to invalid fields
        onlyFillShowError(
            validUserId,
            userId.error,
            userId.errorPlacement,
            userId.errorId
        );

        onlyFillShowError(
            validPassword,
            password.error,
            password.errorPlacement,
            password.errorId
        );

        const formErrorMessageId = errorMessageId("form");
        const formErrorPlacement = $("#registerButton");
        const formErrorElement = errorMessage(ERROR.recaptcha, "form");
        if ($(formErrorMessageId).length === 0 && !validRecaptcha) {
            formErrorPlacement.after(
                `<div class="text-center">${formErrorElement}</div>`
            );
        } else if (
            $(formErrorMessageId).length > 0 &&
            !validRecaptcha &&
            $(formErrorMessageId).text() !== ERROR.recaptcha
        ) {
            $(formErrorMessageId).text(ERROR.recaptcha);
        } else if ($(formErrorMessageId).length > 0 && validRecaptcha) {
            $(formErrorMessageId).remove();
        }
    }
};
