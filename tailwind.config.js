import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.vue",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Ahora estos colores se SUMAN a los de Tailwind
                "pro-orange": "#e27a11",
                "pro-green": "#008b3e",
                "pro-blue": "#203a71",
                "pro-red": "#842522",
                "pro-gray": "#606060",
            },
            backgroundImage: {
                // Degradado oficial del manual
                "pro-gradient":
                    "linear-gradient(180deg, #e27a11 0%, #842522 100%)",
            },
        },
    },

    plugins: [forms],
};
