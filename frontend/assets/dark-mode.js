/**
 * Bender - Dark Mode functionality v1.1
 *
 * This script handles dark mode toggling and persistence using localStorage
 * It also respects user's system preferences when first visiting
 * No keyboard shortcuts or animations
 */

document.addEventListener("DOMContentLoaded", function () {
    // Get the toggle checkbox
    const darkModeToggle = document.getElementById("darkModeToggle");
    const toggleText = document.querySelector(".toggle-text");

    // Remove any existing notifications (to ensure old ones don't appear)
    document
        .querySelectorAll('[style*="position: fixed"][style*="z-index: 1000"]')
        .forEach((el) => {
            if (el.textContent.includes("Alt+D")) {
                el.remove();
            }
        });

    // Function to check if user prefers dark color scheme
    function prefersColorSchemeDark() {
        return (
            window.matchMedia &&
            window.matchMedia("(prefers-color-scheme: dark)").matches
        );
    }

    // Check for saved user preference, if any, otherwise use system preference
    const userPreference = localStorage.getItem("darkMode");
    const isDarkMode =
        userPreference !== null
            ? userPreference === "true"
            : prefersColorSchemeDark();

    // Set initial state based on determined preference
    if (isDarkMode) {
        document.documentElement.classList.add("dark-mode");
        if (darkModeToggle) darkModeToggle.checked = true;
        if (toggleText) toggleText.textContent = "Light";
    } else {
        if (toggleText) toggleText.textContent = "Dark";
    }

    // Listen for toggle changes
    if (darkModeToggle) {
        darkModeToggle.addEventListener("change", function () {
            if (this.checked) {
                enableDarkMode();
            } else {
                disableDarkMode();
            }
        });
    }

    /**
     * Enable Dark Mode
     */
    function enableDarkMode() {
        document.documentElement.classList.add("dark-mode");
        localStorage.setItem("darkMode", "true");
        if (toggleText) toggleText.textContent = "Light";

        // Dispatch an event that other scripts can listen for
        document.dispatchEvent(
            new CustomEvent("darkModeChange", { detail: { isDarkMode: true } }),
        );
    }

    /**
     * Disable Dark Mode
     */
    function disableDarkMode() {
        document.documentElement.classList.remove("dark-mode");
        localStorage.setItem("darkMode", "false");
        if (toggleText) toggleText.textContent = "Dark";

        // Dispatch an event that other scripts can listen for
        document.dispatchEvent(
            new CustomEvent("darkModeChange", {
                detail: { isDarkMode: false },
            }),
        );
    }

    // Listen for system preference changes
    if (window.matchMedia) {
        window
            .matchMedia("(prefers-color-scheme: dark)")
            .addEventListener("change", (e) => {
                // Only apply if user hasn't explicitly set a preference
                if (localStorage.getItem("darkMode") === null) {
                    if (e.matches) {
                        enableDarkMode();
                        if (darkModeToggle) darkModeToggle.checked = true;
                    } else {
                        disableDarkMode();
                        if (darkModeToggle) darkModeToggle.checked = false;
                    }
                }
            });
    }
});
