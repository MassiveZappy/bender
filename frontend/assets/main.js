/**
 * Bender - Satirical News Site
 * Main JavaScript file for enhancing user experience
 */

document.addEventListener("DOMContentLoaded", function () {
    // Add container class to body content for better layout
    wrapBodyContent();

    // Enhance navigation links
    styleNavLinks();

    // Style form elements
    enhanceForms();

    // Improve article list display
    improveArticleList();

    // Add responsive menu for mobile
    createResponsiveMenu();

    // Add visual feedback for buttons
    addButtonFeedback();

    // Add fade-in animation for page content
    animatePageLoad();
});

/**
 * Wraps the body content in a container div for better styling
 */
function wrapBodyContent() {
    // Don't wrap if already wrapped or if this is in a skin
    if (
        document.querySelector(".container") ||
        document.querySelector("[data-skin]")
    ) {
        return;
    }

    const bodyChildren = Array.from(document.body.children);
    const container = document.createElement("div");
    container.className = "container";

    // Move all body children to the container
    while (document.body.firstChild) {
        container.appendChild(document.body.firstChild);
    }

    document.body.appendChild(container);

    // Add header section for navigation
    const header = document.createElement("header");
    const mainContent = document.createElement("main");

    // Find the h1 element if it exists
    const h1 = container.querySelector("h1");
    if (h1) {
        // Move the h1 to the header
        h1.style.textAlign = "center";
        header.appendChild(h1);
        container.insertBefore(header, container.firstChild);
    } else {
        // Create a header anyway
        const defaultTitle = document.createElement("h1");
        defaultTitle.textContent = document.title || "Bender";
        defaultTitle.style.textAlign = "center";
        header.appendChild(defaultTitle);
        container.insertBefore(header, container.firstChild);
    }

    // Create navigation section
    createNavigation(header);

    // Move remaining content to main
    while (container.children.length > 1) {
        mainContent.appendChild(container.children[1]);
    }
    container.appendChild(mainContent);
}

/**
 * Creates a navigation section
 */
function createNavigation(header) {
    // Check if we're on a page that should have navigation
    const nav = document.createElement("nav");
    const currentPage = window.location.pathname.split("/").pop();

    // Add navigation links based on context
    if (document.querySelector('a[href="logout.php"]')) {
        // User is logged in
        nav.innerHTML = `
            <a href="index.php" class="nav-link">Home</a>
            <a href="dashboard.php" class="nav-link">Dashboard</a>
            <a href="article.php" class="nav-link">New Article</a>
            <a href="logout.php" class="nav-link nav-link-danger">Logout</a>
        `;
        nav.style.display = "flex";
        nav.style.justifyContent = "center";

        // Check if user is admin
        if (document.querySelector('a[href="admin.php"]')) {
            // Insert admin link before logout
            const adminLink = document.createElement("a");
            adminLink.href = "admin.php";
            adminLink.className = "nav-link nav-link-admin";
            adminLink.textContent = "Admin";
            nav.insertBefore(adminLink, nav.lastChild);
        }
    } else {
        // User is not logged in
        nav.innerHTML = `
            <a href="index.php" class="nav-link">Home</a>
            <a href="login.php" class="nav-link">Login</a>
            <a href="signup.php" class="nav-link">Sign Up</a>
        `;
        nav.style.display = "flex";
        nav.style.justifyContent = "center";
    }

    header.appendChild(nav);
}

/**
 * Enhances the styling of navigation links
 */
function styleNavLinks() {
    // Add classes to existing navigation links
    const links = document.querySelectorAll("a");

    links.forEach((link) => {
        const href = link.getAttribute("href");
        if (!href) return;

        // Skip links that already have our styling
        if (link.classList.contains("nav-link")) return;

        // Style navigation links
        if (
            href === "index.php" ||
            href === "dashboard.php" ||
            href === "login.php" ||
            href === "signup.php" ||
            href === "logout.php" ||
            href === "admin.php" ||
            href === "article.php"
        ) {
            link.classList.add("btn");

            if (href === "logout.php") {
                link.classList.add("btn-danger");
            }
        }

        // Style action links
        if (href.includes("view_article.php")) {
            link.classList.add("btn");
            link.innerHTML = '<span class="icon">👁️</span> View';
        }

        if (href.includes("article.php?id=")) {
            link.classList.add("btn");
            link.innerHTML = '<span class="icon">✏️</span> Edit';
        }
    });

    // Style delete buttons
    const deleteButtons = document.querySelectorAll(
        'button[onclick*="delete"]',
    );
    deleteButtons.forEach((button) => {
        button.classList.add("btn-danger");
        button.innerHTML = '<span class="icon">🗑️</span> Delete';
    });
}

/**
 * Enhances form elements
 */
function enhanceForms() {
    // Add classes and structure to forms
    const forms = document.querySelectorAll("form");

    forms.forEach((form) => {
        // Skip if already enhanced
        if (form.classList.contains("enhanced-form")) return;
        form.classList.add("enhanced-form");
        form.style.textAlign = "center";
        form.style.maxWidth = "600px";
        form.style.margin = "0 auto 20px auto";

        // Add labels to inputs that don't have them
        const inputs = form.querySelectorAll(
            'input:not([type="submit"]), textarea, select',
        );

        inputs.forEach((input) => {
            // Skip if already has a label
            if (input.id && document.querySelector(`label[for="${input.id}"]`))
                return;

            // Create a form group
            const formGroup = document.createElement("div");
            formGroup.className = "form-group";
            formGroup.style.textAlign = "center";

            // Generate an ID if needed
            if (!input.id) {
                input.id = `input-${Math.random().toString(36).substring(2, 9)}`;
            }

            // Center the input itself
            input.style.margin = "0 auto";

            // Create label based on placeholder or name
            const labelText = input.placeholder || input.name || "";
            if (labelText) {
                const label = document.createElement("label");
                label.setAttribute("for", input.id);
                label.textContent =
                    labelText.charAt(0).toUpperCase() + labelText.slice(1);
                label.style.textAlign = "center";
                label.style.display = "block";
                formGroup.appendChild(label);
            }

            // Move input to the form group
            input.parentNode.insertBefore(formGroup, input);
            formGroup.appendChild(input);

            // Remove <br> tags that might follow inputs
            if (
                formGroup.nextSibling &&
                formGroup.nextSibling.nodeName === "BR"
            ) {
                formGroup.nextSibling.remove();
            }
        });

        // Style submit buttons
        const submitButtons = form.querySelectorAll(
            'input[type="submit"], button[type="submit"]',
        );
        submitButtons.forEach((button) => {
            button.classList.add("btn");
            button.style.margin = "15px auto 0 auto";
            button.style.display = "block";
        });
    });
}

/**
 * Improves the article list display
 */
function improveArticleList() {
    const articleLists = document.querySelectorAll("ul");

    articleLists.forEach((list) => {
        // Check if this looks like an article list
        const items = list.querySelectorAll("li");
        if (items.length === 0) return;

        let isArticleList = false;
        items.forEach((item) => {
            if (
                item.textContent.includes("View") ||
                item.textContent.includes("Delete") ||
                item.textContent.includes("Skin:")
            ) {
                isArticleList = true;
            }
        });

        // Add text-center class to the list for better alignment
        list.classList.add("text-center");

        if (isArticleList) {
            list.classList.add("article-list");

            items.forEach((item) => {
                // Skip if already enhanced
                if (item.classList.contains("enhanced-item")) return;
                item.classList.add("enhanced-item");

                // Extract and organize content
                const titleLink =
                    item.querySelector('a[href*="article.php?id="]') ||
                    item.querySelector("a");

                if (titleLink) {
                    titleLink.classList.add("article-title");
                    titleLink.style.textAlign = "center";
                    titleLink.style.display = "block";

                    // Create article meta section
                    const metaText = item.textContent.match(/\(Skin: [^)]+\)/);
                    if (metaText) {
                        const metaSpan = document.createElement("span");
                        metaSpan.className = "article-meta";
                        metaSpan.textContent = metaText[0];
                        metaSpan.style.display = "block";
                        metaSpan.style.textAlign = "center";

                        // Remove the text from the main item
                        item.innerHTML = item.innerHTML.replace(
                            metaText[0],
                            "",
                        );

                        // Add it back in the span
                        titleLink.after(metaSpan);
                    }

                    // Create actions container
                    const actions = document.createElement("div");
                    actions.className = "article-actions";
                    actions.style.display = "flex";
                    actions.style.justifyContent = "center";
                    actions.style.width = "100%";
                    actions.style.marginTop = "10px";

                    // Move view/edit/delete buttons to actions
                    const viewLink = item.querySelector(
                        'a[href*="view_article.php"]',
                    );
                    const deleteButton = item.querySelector(
                        'button[onclick*="delete"]',
                    );

                    if (viewLink) actions.appendChild(viewLink);
                    if (deleteButton) actions.appendChild(deleteButton);

                    item.appendChild(actions);
                }
            });
        }
    });
}

/**
 * Creates a responsive menu for mobile devices
 */
function createResponsiveMenu() {
    const nav = document.querySelector("nav");
    if (!nav) return;

    // Add responsive menu container
    const menuContainer = document.createElement("div");
    menuContainer.className = "mobile-menu-container";
    menuContainer.style.textAlign = "center";

    // Create menu toggle button for mobile
    const menuToggle = document.createElement("button");
    menuToggle.className = "menu-toggle";
    menuToggle.innerHTML = "☰";
    menuToggle.setAttribute("aria-label", "Toggle Menu");

    // Add event listener for menu toggle
    menuToggle.addEventListener("click", () => {
        nav.classList.toggle("mobile-menu-open");
    });

    // Insert menu toggle before nav
    nav.parentNode.insertBefore(menuContainer, nav);
    menuContainer.appendChild(menuToggle);
    menuContainer.appendChild(nav);

    // Add media query listener for menu
    const mediaQuery = window.matchMedia("(max-width: 768px)");
    function handleMobileChange(e) {
        if (e.matches) {
            document.body.classList.add("mobile-view");
        } else {
            document.body.classList.remove("mobile-view");
            nav.classList.remove("mobile-menu-open");
        }
    }

    // Initial check
    handleMobileChange(mediaQuery);
    // Add listener
    mediaQuery.addEventListener("change", handleMobileChange);
}

/**
 * Adds visual feedback for button clicks
 */
function addButtonFeedback() {
    const buttons = document.querySelectorAll(
        'button, .btn, input[type="submit"]',
    );

    buttons.forEach((button) => {
        button.addEventListener("click", function (e) {
            // Skip for links that navigate away
            if (
                this.tagName === "A" &&
                this.getAttribute("href") &&
                !this.getAttribute("href").startsWith("#")
            ) {
                return;
            }

            // Add ripple effect
            const ripple = document.createElement("span");
            ripple.className = "ripple";
            this.appendChild(ripple);

            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);

            ripple.style.width = ripple.style.height = `${size}px`;
            ripple.style.left = `${e.clientX - rect.left - size / 2}px`;
            ripple.style.top = `${e.clientY - rect.top - size / 2}px`;

            // Remove after animation
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });

    // Add styles for ripple effect
    const style = document.createElement("style");
    style.textContent = `
        button, .btn, input[type="submit"] {
            position: relative;
            overflow: hidden;
        }
        .ripple {
            position: absolute;
            border-radius: 50%;
            transform: scale(0);
            animation: ripple 0.6s linear;
            background-color: rgba(255, 255, 255, 0.7);
            pointer-events: none;
        }
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
}

/**
 * Adds a subtle fade-in animation for page content
 */
function animatePageLoad() {
    const main = document.querySelector("main");
    if (main) {
        main.style.opacity = "0";
        main.style.transition = "opacity 0.3s ease-in-out";

        setTimeout(() => {
            main.style.opacity = "1";
        }, 100);
    }
}

/**
 * Utility functions
 */

// Check if an element is in viewport
function isInViewport(element) {
    const rect = element.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <=
            (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <=
            (window.innerWidth || document.documentElement.clientWidth)
    );
}

// Debounce function for performance
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
