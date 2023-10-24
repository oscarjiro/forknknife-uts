import { ERROR } from "./const.js";
import {
    getValue,
    checkUsername,
    checkPassword,
    toggleViewPassword,
    errorMessageId,
    onInputHandler,
    showError,
    fadeInMain,
    checkEmail,
    checkFirstName,
    checkLastName,
    checkGender,
    inputChoicesEventListener,
    checkDate,
} from "./utils.js";
import { errorMessage, emptyError } from "./components.js";

$(document).ready(() => {
    // Username on input check
    username.element.on("input", () => {
        onInputHandler(
            username.element,
            username.errorPlacement,
            checkUsername,
            username.error,
            ERROR.username,
            username.errorId
        );
    });

    // Email on input check
    email.element.on("input", () =>
        onInputHandler(
            email.element,
            email.errorPlacement,
            checkEmail,
            email.error,
            ERROR.email,
            email.errorId
        )
    );

    // First name on input check
    firstName.element.on("input", () =>
        onInputHandler(
            firstName.element,
            firstName.errorPlacement,
            checkFirstName,
            firstName.error,
            ERROR.firstName,
            firstName.errorId
        )
    );

    // Last name on input check
    lastName.element.on("input", () =>
        onInputHandler(
            lastName.element,
            lastName.errorPlacement,
            checkLastName,
            lastName.error,
            ERROR.lastName,
            lastName.errorId
        )
    );

    // Gender on change check
    gender.element.change(() =>
        onInputHandler(
            gender.element,
            gender.errorPlacement,
            checkGender,
            gender.error,
            ERROR.gender,
            gender.errorId
        )
    );

    // Birthdate on input check
    birthdate.element.on("input", () =>
        onInputHandler(
            birthdate.element,
            birthdate.errorPlacement,
            checkDate,
            birthdate.error,
            ERROR.birthdate,
            birthdate.errorId
        )
    );

    // Gender buttons
    inputChoicesEventListener(gender.parentId, gender.element);

    // Role buttons
    inputChoicesEventListener(role.parentId, role.element);

    // Password on input check
    password.element.on("input", () => {
        onInputHandler(
            password.element,
            password.errorPlacement,
            checkPassword,
            password.error,
            ERROR.password,
            password.errorId
        );

        // Check confirm password
        handleConfirmPassword(
            getValue(password.element),
            getValue(confirmPassword.element)
        );
    });

    // Confirm password on input check
    confirmPassword.element.on("input", () =>
        handleConfirmPassword(
            getValue(password.element),
            getValue(confirmPassword.element)
        )
    );

    // Password visibility toggle
    password.toggle.click(() =>
        toggleViewPassword(password.toggle, password.element)
    );
    confirmPassword.toggle.click(() =>
        toggleViewPassword(confirmPassword.toggle, confirmPassword.element)
    );

    // Form submission handler
    $("#registerForm").submit((event) => formHandler(event));

    // Fade in main
    fadeInMain();
});

// Elements
const inputs = {
    firstName: {
        element: $("#firstName"),
        errorPlacement: $("#firstName"),
        error: errorMessage(ERROR.firstName, "firstName"),
        emptyError: errorMessage(emptyError("First name"), "firstName"),
        errorId: errorMessageId("firstName"),
    },
    lastName: {
        element: $("#lastName"),
        errorPlacement: $("#lastName"),
        error: errorMessage(ERROR.lastName, "lastName"),
        emptyError: errorMessage(emptyError("Last name"), "lastName"),
        errorId: errorMessageId("lastName"),
    },
    username: {
        element: $("#username"),
        errorPlacement: $("#username"),
        error: errorMessage(ERROR.username, "username"),
        emptyError: errorMessage(emptyError("username"), "username"),
        errorId: errorMessageId("username"),
    },
    email: {
        element: $("#email"),
        errorPlacement: $("#email"),
        error: errorMessage(ERROR.email, "email"),
        emptyError: errorMessage(emptyError("email"), "email"),
        errorId: errorMessageId("email"),
    },
    password: {
        element: $("#password"),
        errorPlacement: $("#passwordInput"),
        error: errorMessage(ERROR.password, "password"),
        emptyError: errorMessage(emptyError("password"), "password"),
        errorId: errorMessageId("password"),
        toggle: $("#toggleViewPassword"),
    },
    confirmPassword: {
        element: $("#confirmPassword"),
        errorPlacement: $("#confirmPasswordInput"),
        error: errorMessage(ERROR.confirmPassword, "confirmPassword"),
        errorId: errorMessageId("confirmPassword"),
        toggle: $("#toggleViewConfirmPassword"),
    },
    role: {
        element: $("#role"),
        parentId: "roleChoices",
    },
    gender: {
        element: $("#gender"),
        errorPlacement: $("#genderChoices"),
        error: errorMessage(ERROR.gender, "gender"),
        emptyError: errorMessage(emptyError("gender"), "gender"),
        errorId: errorMessageId("gender"),
        parentId: "genderChoices",
    },
    birthdate: {
        element: $("#date"),
        errorPlacement: $("#date"),
        error: errorMessage("Invalid date.", "date"),
        emptyError: errorMessage(emptyError("Birthdate"), "date"),
        errorId: errorMessageId("date"),
    },
};

// Destructure
const {
    username,
    email,
    firstName,
    lastName,
    password,
    confirmPassword,
    role,
    gender,
    birthdate,
} = inputs;

// Ensure password confirmation matches
const handleConfirmPassword = (passwordValue, confirmPasswordValue) => {
    const validConfirmPassword = confirmPasswordValue === passwordValue;
    if (!validConfirmPassword && $(confirmPassword.errorId).length === 0) {
        confirmPassword.errorPlacement.after(confirmPassword.error);
    } else if (validConfirmPassword && $(confirmPassword.errorId).length > 0) {
        $(confirmPassword.errorId).remove();
    }
};

// Form handler
const formHandler = (event) => {
    // Check for form validity
    const usernameValue = getValue(username.element);
    const emailValue = getValue(email.element);
    const firstNameValue = getValue(firstName.element);
    const lastNameValue = getValue(lastName.element);
    const birthdateValue = getValue(birthdate.element);
    const genderValue = getValue(gender.element);
    const passwordValue = getValue(password.element);

    const validUsername = checkUsername(usernameValue);
    const validEmail = checkEmail(emailValue);
    const validFirstName = checkFirstName(firstNameValue);
    const validLastName = checkLastName(lastNameValue);
    const validGender = checkGender(genderValue);
    const validBirthdate = checkDate(birthdateValue);
    const validPassword = checkPassword(passwordValue);
    const validConfirmPassword =
        getValue(confirmPassword.element) === passwordValue;
    const validForm =
        validUsername &&
        validEmail &&
        validFirstName &&
        validLastName &&
        validPassword &&
        validGender &&
        validConfirmPassword;

    // If invalid, prevent submission
    if (!validForm) {
        event.preventDefault();

        // Add errors to invalid fields
        showError(
            usernameValue,
            username.errorPlacement,
            validUsername,
            "username",
            username.error,
            username.emptyError,
            ERROR.username,
            username.errorId
        );

        showError(
            emailValue,
            email.errorPlacement,
            validEmail,
            "email",
            email.error,
            email.emptyError,
            ERROR.email,
            email.errorId
        );

        showError(
            firstNameValue,
            firstName.errorPlacement,
            validFirstName,
            "First name",
            firstName.error,
            firstName.emptyError,
            ERROR.firstName,
            firstName.errorId
        );

        showError(
            lastNameValue,
            lastName.errorPlacement,
            validLastName,
            "Last name",
            lastName.error,
            lastName.emptyError,
            ERROR.lastName,
            lastName.errorId
        );

        showError(
            birthdateValue,
            birthdate.errorPlacement,
            validBirthdate,
            "Birthdate",
            birthdate.error,
            birthdate.emptyError,
            "Invalid date.",
            birthdate.errorId
        );

        showError(
            genderValue,
            gender.errorPlacement,
            validGender,
            "gender",
            gender.error,
            gender.emptyError,
            ERROR.gender,
            gender.errorId
        );

        showError(
            passwordValue,
            password.errorPlacement,
            validPassword,
            "password",
            password.error,
            password.emptyError,
            ERROR.password,
            password.errorId
        );

        if (!validConfirmPassword && $(confirmPassword.errorId).length === 0) {
            $("#confirmPasswordInput").after(confirmPassword.error);
        }
    }
};
