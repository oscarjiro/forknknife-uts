import {
    USERNAME_REGEXP,
    PASSWORD_REGEXP,
    EMAIL_MAX_LENGTH,
    EMAIL_REGEXP,
    FIRST_NAME_MAX_LENGTH,
    LAST_NAME_MAX_LENGTH,
    MENU_NAME_MAX_LENGTH,
    MENU_DESCRIPTION_MAX_LENGTH,
    MENU_CATEGORIES,
    ISO_8601_DATE_REGEXP,
    MENU_IMAGE_MAX_SIZE,
    ERROR,
} from "./const.js";
import {
    emptyError,
    errorMessage,
    passwordToggleHide,
    passwordToggleVisible,
} from "./components.js";

/* STRING MANIPULATION */
// Uppercase every first letter of a word
export const uppercaseWords = (string) => {
    let words = string.split(" ");
    for (let i = 0; i < words.length; i++) {
        let word = words[i];
        words[i] = word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
    }
    return words.join(" ");
};

// Capitalize first letter
export const capitalizeFirst = (string) =>
    !string ? string : string.charAt(0).toUpperCase() + string.slice(1);

// Convert to camel case
export const toCamelCase = (string) => {
    return string
        .toLowerCase()
        .replace(/\s(.)/g, function (match, group) {
            return group.toUpperCase();
        })
        .replace(/\s/g, "")
        .replace(/^(.)/, function (match, group) {
            return group.toLowerCase();
        })
        .replace(/[^a-zA-Z0-9]/g, "");
};

// Convert to IDR
export const toCurrency = (price) => {
    const formatter = new Intl.NumberFormat("id-ID", {
        minimumFractionDigits: 2,
    });

    return `IDR ${formatter.format(price)}`;
};

// Format timestamp
export const formatTimestamp = (timestamp) => {
    const date = new Date(timestamp);
    const options = {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "numeric",
        minute: "numeric",
        second: "numeric",
        hour12: true,
    };
    return date.toLocaleString(undefined, options);
};

// Convert ISO 8601 to formatted date
export const formatDate = (inputDate) => {
    // Create a Date object from the input date string
    const dateParts = inputDate.split("-");
    const year = parseInt(dateParts[0]);
    const month = parseInt(dateParts[1]);
    const day = parseInt(dateParts[2]);
    const date = new Date(year, month - 1, day);

    // Define arrays for month names and day names
    const months = [
        "January",
        "February",
        "March",
        "April",
        "May",
        "June",
        "July",
        "August",
        "September",
        "October",
        "November",
        "December",
    ];
    const daysOfWeek = [
        "Sunday",
        "Monday",
        "Tuesday",
        "Wednesday",
        "Thursday",
        "Friday",
        "Saturday",
    ];

    // Get the formatted date
    const formattedDate =
        day + " " + months[month - 1].substr(0, 3) + " " + year;

    // Get the day of the week
    const dayOfWeek = daysOfWeek[date.getDay()];

    return `${dayOfWeek.substr(0, 3)}, ${formattedDate}`;
};

/* STRING PRODUCTION */
// Clean text input
export const cleanInput = (input) => input.trim();

// Get input value
export const getValue = (input) => cleanInput(input?.val());

// Get error message ID
export const errorMessageId = (name) => `#${name}ErrorMessage`;

/* CHECKERS */
// Check if empty string
export const isEmpty = (string) => string.length === 0;

// Check username
export const checkUsername = (username) => USERNAME_REGEXP.test(username);

// Check password
export const checkPassword = (password) => PASSWORD_REGEXP.test(password);

// Check first name
export const checkFirstName = (firstName) =>
    firstName.length > 0 && firstName.length <= FIRST_NAME_MAX_LENGTH;

// Check last name
export const checkLastName = (lastName) =>
    lastName.length > 0 && lastName.length <= LAST_NAME_MAX_LENGTH;

// Check email
export const checkEmail = (email) =>
    email.length > 0 &&
    email.length <= EMAIL_MAX_LENGTH &&
    EMAIL_REGEXP.test(email);

// Check gender
export const checkGender = (gender) => gender === "M" || gender === "F";

// Check if valid menu category
export const checkMenuCategory = (category) =>
    MENU_CATEGORIES.includes(category);

// Check menu name
export const checkMenuName = (name) =>
    name.length > 0 && name.length <= MENU_NAME_MAX_LENGTH;

// Check menu price
export const checkMenuPrice = (price) => price && price > 0;

// Check menu description
export const checkMenuDescription = (description) =>
    description.length > 0 && description.length <= MENU_DESCRIPTION_MAX_LENGTH;

// Check image type
export const checkImageType = (type) => type.startsWith("image/");

// Check image type
export const checkImageSize = (size) => size <= MENU_IMAGE_MAX_SIZE;

// Check date in ISO 8601 format
export const checkDate = (date) => ISO_8601_DATE_REGEXP.test(date);

/* DOM MANIPULATION */
// Fade in main
export const fadeInMain = () =>
    setTimeout(() => $("main").removeClass("opacity-0"), 100);

// Close modal
export const closeModal = () => {
    $("#cancelAction").off("click", closeModal);
    $("#blackout").addClass("opacity-0");
    setTimeout(() => $("#blackout").remove(), 300);
};

// Pop up modal
export const popUpModal = (message, action, buttonClass) => {
    $("body").append(`
        <section id="blackout" class="hidden opacity-0">
            <div id="modal">
                <div id="modalTitle">${message}</div>
                <div class="modal-buttons-ctr">
                    <button id="cancelAction" class="button-gray">Cancel</button>
                    <button id="confirmAction" class="${buttonClass}">${action}</button>
                </div>
            </div>
        </section>
    `);
    $("#blackout").removeClass("hidden");
    setTimeout(() => $("#blackout").removeClass("opacity-0"), 100);

    // Event listeners to close modal
    $("#cancelAction").click(closeModal);
};

// Toggle view password
export const toggleViewPassword = (toggle, input) => {
    const isHidden = input.attr("type") === "password";
    toggle.html(isHidden ? passwordToggleHide : passwordToggleVisible);
    input.attr("type", isHidden ? "text" : "password");
};

/* INPUT HANDLING */
// General input handler
export const onInputHandler = (
    input,
    errorPlacement,
    validator,
    errorElement,
    errorMessage,
    errorId
) => {
    // Check if valid
    const value = getValue(input);
    const isValid = !value || validator(value);

    // If invalid and no error yet, show error
    if (!isValid && $(errorId).length === 0) {
        errorPlacement.after(errorElement);
    }

    // If invalid but different error is shown, update error
    else if (
        !isValid &&
        $(errorId).length > 0 &&
        $(errorId).text() !== errorMessage
    ) {
        $(errorId).text(errorMessage);
    }

    // If valid but error was shown, remove error
    else if (isValid && $(errorId).length > 0) {
        $(errorId).remove();
    }
};

// Simple non-empty on input handler
export const onlyFillInputHandler = (errorId) => {
    $(errorId).remove();
};

// General error shower
export const showError = (
    value,
    errorPlacement,
    isValid,
    name,
    errorElement,
    emptyErrorElement,
    errorMessage,
    errorId
) => {
    // Check if value is empty
    const emptyValue = isEmpty(value);

    // Determine appropriate element and message
    const appropriateErrorElement = emptyValue
        ? emptyErrorElement
        : errorElement;
    const appropriateErrorMessage = emptyValue
        ? emptyError(name)
        : errorMessage;

    // If invalid and no error shown, show error element
    if (!isValid && $(errorId).length === 0) {
        errorPlacement.after(appropriateErrorElement);
    }

    // If invalid but inappropriate error shown, update error
    else if (!isValid && $(errorId).length > 0) {
        $(errorId).text(appropriateErrorMessage);
    }

    // If valid, remove error element
    else {
        $(errorId).remove();
    }
};

// Simple non-empty error shower
export const onlyFillShowError = (
    valid,
    errorElement,
    errorPlacement,
    errorId
) => {
    if (!valid && $(errorId).length === 0) {
        errorPlacement.after(errorElement);
    } else if (valid) {
        $(errorId).remove();
    }
};

// Choices input mechanism
export const inputChoicesEventListener = (parentId, hiddenInput) => {
    $(`#${parentId}`).on("click", "[data-value]", function () {
        const inputValue = $(this).data("value");

        // Update hidden input
        hiddenInput.val(cleanInput(inputValue)).trigger("change");

        // Change button class for all children
        $(`#${parentId} [data-value]`).each(function () {
            $(this).toggleClass(
                "button-black",
                $(this).data("value") !== inputValue
            );
            $(this).toggleClass(
                "button-black-active",
                $(this).data("value") === inputValue
            );
        });
    });
};

// Preview image
export const previewImage = (
    changeEvent,
    inputElement,
    emptyError,
    errorMessageId,
    errorPlacement,
    name,
    previewImageElement,
    previewNameElement,
    uploadTextElement
) => {
    // Get image and its type and size
    const imageNotEmpty = inputElement.get(0).files.length > 0;
    const image = imageNotEmpty ? inputElement.get(0).files[0] : null;
    const validImageType = imageNotEmpty ? checkImageType(image.type) : false;
    const validImageSize = imageNotEmpty ? checkImageSize(image.size) : false;
    const validImage = validImageSize && validImageType;

    // Prevent change if invalid
    if (!validImage) {
        changeEvent.preventDefault();
        const imageErrorMessage = !imageNotEmpty
            ? emptyError
            : !validImageType
            ? ERROR.imageType
            : ERROR.imageSize;

        // Show error message
        if ($(errorMessageId).length === 0) {
            errorPlacement.after(errorMessage(imageErrorMessage, name));
        } else {
            $(errorMessageId).text(imageErrorMessage);
        }

        // Hide preview image
        previewImageElement.attr("src", "");
        if (!previewImageElement.attr("class").includes("hidden")) {
            previewImageElement.addClass("hidden");
        }

        // Hide preview name
        previewNameElement.html("");
        if (!previewNameElement.attr("class").includes("hidden")) {
            previewNameElement.addClass("hidden");
        }

        // Show upload text
        uploadTextElement.removeClass("opacity-0");

        // Clear input value
        inputElement.val("");
        return;
    }

    // Remove any error message
    $(errorMessageId).remove();

    // Read uploaded image
    const reader = new FileReader();
    reader.readAsDataURL(image);

    // Show preview image
    reader.onload = () => {
        previewImageElement.attr("src", reader.result);
        previewImageElement.removeClass("hidden");
    };

    // Show preview name and hide upload text
    previewNameElement.removeClass("hidden").html(image.name);
    if (!uploadTextElement.attr("class").includes("opacity-0")) {
        uploadTextElement.addClass("opacity-0");
    }
};

/* OTHER */
export const clearQueryParam = () => {
    // Use history.replaceState to update the URL without refreshing
    history.replaceState(null, "", window.location.pathname);
};

export const getParam = (paramKey) => {
    const url = new URLSearchParams(window.location.search);
    return url.get(paramKey);
};
