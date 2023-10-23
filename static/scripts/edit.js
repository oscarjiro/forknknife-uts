import { ERROR, MENU_DESCRIPTION_MAX_LENGTH } from "./const.js";
import {
    fadeInMain,
    getValue,
    errorMessageId,
    onInputHandler,
    checkMenuName,
    checkMenuCategory,
    checkMenuPrice,
    checkMenuDescription,
    showError,
    inputChoicesEventListener,
    previewImage,
    checkImageType,
    checkImageSize,
} from "./utils.js";
import { emptyError, errorMessage } from "./components.js";

$(document).ready(() => {
    // Name on input check
    name.element.on("input", () =>
        onInputHandler(
            name.element,
            name.errorPlacement,
            checkMenuName,
            name.error,
            ERROR.taskName,
            name.errorId
        )
    );

    // Price on input check
    price.element.on("input", () =>
        onInputHandler(
            price.element,
            price.errorPlacement,
            checkMenuPrice,
            price.error,
            ERROR.menuPrice,
            price.errorId
        )
    );

    // Description on input check
    description.element.on("input", () => {
        // Get value and update count
        let value = description.element.val();

        // Truncate values
        const truncatedValue = value.substring(0, MENU_DESCRIPTION_MAX_LENGTH);
        description.element.val(truncatedValue);

        // Update element
        value = description.element.val();
        $("#textareaCount").text(value.length);

        // Input handler
        onInputHandler(
            description.element,
            description.errorPlacement,
            checkMenuDescription,
            description.error,
            ERROR.taskDescription,
            description.errorId
        );
    });

    // Category buttons
    inputChoicesEventListener(category.parentId, category.element);

    // Category element when changed
    category.element.change(() =>
        onInputHandler(
            category.element,
            category.errorPlacement,
            checkMenuCategory,
            category.error,
            ERROR.menuCategory,
            category.errorId
        )
    );

    // Image upload
    image.element.change((event) =>
        previewImage(
            event,
            image.element,
            image.emptyError,
            image.errorId,
            image.errorPlacement,
            "image",
            image.previewElement,
            image.previewName,
            image.uploadText
        )
    );

    image.uploadTrigger.click(() => image.element.click());

    // Form submission
    $("#editForm").submit((event) => {
        // Get all values
        const nameValue = getValue(name.element);
        const priceValue = getValue(price.element);
        const descriptionValue = getValue(description.element);
        const categoryValue = getValue(category.element);
        const nonEmptyImage = image.element.get(0).files.length > 0;
        const uploadedImage = nonEmptyImage
            ? image.element.get(0).files[0]
            : null;

        // Check form validity
        const validName = checkMenuName(nameValue);
        const validPrice = checkMenuPrice(priceValue);
        const validDescription = checkMenuDescription(descriptionValue);
        const validCategory = checkMenuCategory(categoryValue);
        const imageChange = uploadedImage ? true : false;
        const validImageType = nonEmptyImage
            ? checkImageType(uploadedImage.type)
            : false;
        const validImageSize = nonEmptyImage
            ? checkImageSize(uploadedImage.size)
            : false;
        const validImage = !imageChange || (validImageType && validImageSize);
        const validForm =
            validName &&
            validPrice &&
            validDescription &&
            validCategory &&
            validImage;

        // If invalid, prevent submission
        if (!validForm) {
            event.preventDefault();

            // Add errors to invalid fields
            showError(
                nameValue,
                name.element,
                validName,
                "Menu name",
                name.error,
                name.emptyError,
                ERROR.menuName,
                name.errorId
            );

            showError(
                priceValue,
                price.element,
                validPrice,
                "Menu price",
                price.error,
                price.emptyError,
                ERROR.menuPrice,
                price.errorId
            );

            showError(
                descriptionValue,
                description.element,
                validDescription,
                "Menu description",
                description.error,
                description.emptyError,
                ERROR.menuDescription,
                description.errorId
            );

            showError(
                categoryValue,
                category.element,
                validCategory,
                "Menu category",
                category.error,
                category.emptyError,
                ERROR.menuCategory,
                category.errorId
            );

            const imageError = !nonEmptyImage
                ? image.emptyError
                : !validImageType
                ? image.errorType
                : image.errorSize;
            const imageErrorMessage = !nonEmptyImage
                ? emptyError("Menu image")
                : !validImageType
                ? ERROR.imageType
                : ERROR.imageSize;
            if (!validImage && $(image.errorId).length === 0) {
                image.errorPlacement.after(imageError);
            } else if (!validImage && $(image.errorId).length > 0) {
                $(image.errorId).text(imageErrorMessage);
            } else {
                $(image.errorId).remove();
            }
        }
    });

    // Show main
    fadeInMain();
});

// Elements
const inputs = {
    name: {
        element: $("#name"),
        error: errorMessage(ERROR.menuName, "name"),
        emptyError: errorMessage(emptyError("Menu name"), "name"),
        errorId: errorMessageId("name"),
        errorPlacement: $("#name"),
    },
    price: {
        element: $("#price"),
        error: errorMessage(ERROR.menuPrice, "price"),
        emptyError: errorMessage(emptyError("Menu price"), "price"),
        errorId: errorMessageId("price"),
        errorPlacement: $("#price"),
    },
    description: {
        element: $("#description"),
        error: errorMessage(ERROR.menuDescription, "description"),
        emptyError: errorMessage(emptyError("Menu description"), "description"),
        errorId: errorMessageId("description"),
        errorPlacement: $("#descriptionContainer"),
    },
    category: {
        element: $("#category"),
        error: errorMessage(ERROR.menuCategory, "category"),
        emptyError: errorMessage(emptyError("Menu category"), "category"),
        errorId: errorMessageId("category"),
        errorPlacement: $("#categoryChoices"),
        parentId: "categoryChoices",
    },
    image: {
        element: $("#image"),
        errorPlacement: $("#imageName"),
        errorType: errorMessage(ERROR.imageType, "image"),
        errorSize: errorMessage(ERROR.imageSize, "image"),
        emptyError: errorMessage(emptyError("Menu image"), "image"),
        errorId: errorMessageId("image"),
        previewElement: $("#imagePreview"),
        previewName: $("#imageName"),
        uploadText: $("#imageUploadText"),
        uploadTrigger: $("#imagePreviewContainer"),
    },
};

// Destructure inputs
const { name, price, description, category, image } = inputs;
