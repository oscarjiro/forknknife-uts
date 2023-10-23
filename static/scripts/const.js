// User constraints
export const USERNAME_MIN_LENGTH = 5;
export const USERNAME_MAX_LENGTH = 25;
export const USERNAME_REGEXP = new RegExp(
    `^(?!.*[.]{2,})[a-z\\d_\.]{${USERNAME_MIN_LENGTH - 1},${
        USERNAME_MAX_LENGTH - 1
    }}[a-z\\d_]$`
);
export const EMAIL_MAX_LENGTH = 255;
export const EMAIL_REGEXP = new RegExp(
    "(?:[a-z0-9!#$%&'*+\\/?^_`{|}~-]+(?:\\.[a-z0-9!#$%&'*+\\/?^_`{|}~-]+)*|\"(?:[\\x01-\\x08\\x0b\\x0c\\x0e-\\x1f\\x21\\x23-\\x5b\\x5d-\\x7f]|\\\\[\\x01-\\x09\\x0b\\x0c\\x0e-\\x7f])*\")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\\x01-\\x08\\x0b\\x0c\\x0e-\\x1f\\x21-\\x5a\\x53-\\x7f]|\\\\[\\x01-\\x09\\x0b\\x0c\\x0e-\\x7f])+)])"
);
export const FIRST_NAME_MAX_LENGTH = 25;
export const LAST_NAME_MAX_LENGTH = 25;
export const PASSWORD_MIN_LENGTH = 8;
export const PASSWORD_REGEXP = new RegExp(
    `^(?=.*[A-Z])(?=.*\\d)(?=.*[~\`!@#\\$%^&*()_\\-+={[}]|:;"'<,>.?\\/]).{${PASSWORD_MIN_LENGTH},}$`
);

// Menu constraints
export const MENU_NAME_MAX_LENGTH = 50;
export const MENU_DESCRIPTION_MAX_LENGTH = 150;
export const MENU_IMAGE_MAX_SIZE = 2 * 1024 * 1024;
export const MENU_CATEGORIES = [
    "Appetizers",
    "Main Courses",
    "Sandwiches & Burgers",
    "Soups & Salads",
    "Sides",
    "Desserts",
    "Beverages",
    "Coffee & Tea",
    "Alcoholic Drinks",
];
export const ISO_8601_DATE_REGEXP = new RegExp("^\\d{4}-\\d{2}-\\d{2}$");

// Error object
export const ERROR = {
    username: `Username must be between ${USERNAME_MIN_LENGTH} and ${USERNAME_MAX_LENGTH} characters inclusive and can only contain alphabets, numbers, underscores, and periods.`,
    firstName: `First name must be at most ${FIRST_NAME_MAX_LENGTH} characters long.`,
    lastName: `Last name must be at most ${LAST_NAME_MAX_LENGTH} characters long.`,
    email: "Please provide a valid email address.",
    imageType: "Uploaded file must be an image.",
    imageSize: `Uploaded image should not exceed ${
        MENU_IMAGE_MAX_SIZE / 1024 ** 2
    } MB.`,
    password: `Password must be at least ${PASSWORD_MIN_LENGTH} characters and must contain at least one uppercase letter, number, and special character.`,
    confirmPassword: "Password does not match.",
    gender: "Gender must be either male or female",
    menuName: `Menu name must be at most ${MENU_NAME_MAX_LENGTH} characters long.`,
    menuPrice: "Price must be larger than 0.",
    menuDescription: `Menu description must be at most ${MENU_DESCRIPTION_MAX_LENGTH} characters long.`,
    menuCategory: "Invalid menu category.",
    general: "An error occured. Please try again.",
    recaptcha: "Please verify that you are not a robot.",
};
