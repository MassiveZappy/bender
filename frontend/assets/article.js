/**
 * Bender - Article Editor Enhancements
 * JavaScript specifically for the article.php page
 */

document.addEventListener("DOMContentLoaded", function() {
    // Only run on article.php page
    if (document.querySelector('#markdown-editor')) {
        // Apply full-width layout
        applyFullWidthLayout();

        // Enhance SimpleMDE editor
        enhanceEditor();

        // Improve form layout
        improveFormLayout();

        // Organize buttons and actions
        organizeActions();

        // Add responsive behaviors
        addResponsiveBehaviors();

        // Add keyboard shortcuts
        addKeyboardShortcuts();
    }
});

/**
 * Apply full-width layout to the page
 */
function applyFullWidthLayout() {
    // Add class to body for specific styling
    document.body.classList.add('article-editor-page');

    // Create main container if not exists
    if (!document.querySelector('.container')) {
        const bodyContent = Array.from(document.body.children);
        const container = document.createElement('div');
        container.className = 'container';

        // Move all body children to container
        while (document.body.firstChild) {
            container.appendChild(document.body.firstChild);
        }

        document.body.appendChild(container);
    }

    // Add main content wrapper
    if (!document.querySelector('main')) {
        const container = document.querySelector('.container');
        const mainContent = document.createElement('main');
        mainContent.className = 'article-editor-main';

        // Move container children to main
        while (container.firstChild) {
            mainContent.appendChild(container.firstChild);
        }

        container.appendChild(mainContent);
    }

    // Set full width styles
    document.documentElement.style.setProperty('--editor-width', '100%');
}

/**
 * Enhance the SimpleMDE editor
 */
function enhanceEditor() {
    // Check if SimpleMDE is already initialized
    if (window.simplemde) {
        const editor = window.simplemde;

        // Add fullscreen button click handler
        const fullscreenButton = document.querySelector('.editor-toolbar .fa-arrows-alt');
        if (fullscreenButton) {
            fullscreenButton.parentElement.addEventListener('click', function() {
                document.body.classList.toggle('editor-fullscreen');
            });
        }

        // Add custom toolbar buttons if needed
        customizeEditorToolbar(editor);

        // Improve preview mode
        enhancePreviewMode(editor);
    } else {
        // If SimpleMDE isn't loaded yet, wait and try again
        setTimeout(enhanceEditor, 500);
    }
}

/**
 * Add custom buttons to the editor toolbar
 */
function customizeEditorToolbar(editor) {
    // This function can be expanded if needed
    // Currently using the default SimpleMDE toolbar
}

/**
 * Enhance the preview mode of the editor
 */
function enhancePreviewMode(editor) {
    // Improve styling for the preview pane
    const previewToggle = document.querySelector('.editor-toolbar .fa-eye');
    if (previewToggle) {
        previewToggle.parentElement.addEventListener('click', function() {
            // Add a class to the body when preview is active
            setTimeout(function() {
                if (document.querySelector('.editor-preview') ||
                    document.querySelector('.editor-preview-side')) {
                    document.body.classList.add('preview-active');
                } else {
                    document.body.classList.remove('preview-active');
                }
            }, 100);
        });
    }
}

/**
 * Improve the form layout
 */
function improveFormLayout() {
    const form = document.querySelector('form[method="POST"]');
    if (!form) return;

    // Add labels to inputs that don't have them
    const inputs = form.querySelectorAll('input:not([type="hidden"]), select, textarea');

    inputs.forEach(input => {
        // Skip if already processed
        if (input.classList.contains('enhanced')) return;
        input.classList.add('enhanced');

        // Skip the markdown editor as it's handled by SimpleMDE
        if (input.id === 'markdown-editor') return;

        const wrapper = document.createElement('div');
        wrapper.className = 'form-group';

        // Generate label from placeholder if no label exists
        if (input.placeholder && !input.previousElementSibling?.tagName !== 'LABEL') {
            // Skip if we already have a label
            if (!document.querySelector(`label[for="${input.id || input.name}"]`)) {
                const label = document.createElement('label');
                label.setAttribute('for', input.id || input.name);
                label.textContent = input.placeholder;

                // Insert label before input
                input.parentNode.insertBefore(wrapper, input);
                wrapper.appendChild(label);
                wrapper.appendChild(input);
            }
        }

        // Remove <br> tags
        if (input.nextElementSibling && input.nextElementSibling.tagName === 'BR') {
            input.nextElementSibling.remove();
        }
    });

    // Add description text for fields that need it
    addFieldDescriptions();
}

/**
 * Add helpful descriptions to certain fields
 */
function addFieldDescriptions() {
    const fieldDescriptions = {
        'content': 'Use Markdown syntax for formatting. You can use the toolbar above for common formatting options.',
        'tags': 'Separate tags with commas. The first tag will be used as the primary category.',
        'publication_datetime': 'When the article should appear to be published.'
    };

    // Add descriptions to fields
    for (const [fieldName, description] of Object.entries(fieldDescriptions)) {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (field) {
            const descEl = document.createElement('div');
            descEl.className = 'field-description';
            descEl.textContent = description;

            // For content field, add description after the SimpleMDE editor
            if (fieldName === 'content') {
                const editorContainer = document.querySelector('.CodeMirror').parentElement;
                editorContainer.parentNode.insertBefore(descEl, editorContainer.nextSibling);
            } else {
                // For other fields, add after the field
                const parent = field.closest('.form-group') || field.parentNode;
                parent.appendChild(descEl);
            }
        }
    }
}

/**
 * Organize the buttons and actions
 */
function organizeActions() {
    // Get the form and find all buttons and action links
    const form = document.querySelector('form[method="POST"]');
    if (!form) return;

    const submitButton = form.querySelector('button[type="submit"]');
    if (!submitButton) return;

    // Create action containers
    const actionsContainer = document.createElement('div');
    actionsContainer.className = 'article-actions';

    // Create left side buttons (primary actions)
    const leftButtons = document.createElement('div');
    leftButtons.className = 'button-group-left';

    // Create right side buttons (secondary actions)
    const rightButtons = document.createElement('div');
    rightButtons.className = 'button-group-right';

    // Organize the buttons
    if (submitButton) {
        leftButtons.appendChild(submitButton);
    }

    // Find the delete form and add it to the right buttons
    const deleteForm = document.querySelector('form[action="delete_article.php"]');
    if (deleteForm) {
        rightButtons.appendChild(deleteForm);
    }

    // Find the view article link and add it to the right buttons
    const viewLink = document.querySelector('a[href*="view_article.php"]');
    if (viewLink) {
        viewLink.className = 'btn';
        rightButtons.appendChild(viewLink);
    }

    // Find the back to dashboard link
    const dashboardLink = document.querySelector('a[href="dashboard.php"]');
    if (dashboardLink) {
        dashboardLink.className = 'btn';
        rightButtons.appendChild(dashboardLink);
    }

    // Add button groups to actions container
    actionsContainer.appendChild(leftButtons);
    actionsContainer.appendChild(rightButtons);

    // Add actions container to the page
    const main = document.querySelector('main') || form.parentNode;
    main.appendChild(actionsContainer);
}

/**
 * Add responsive behaviors
 */
function addResponsiveBehaviors() {
    // Handle window resize
    const handleResize = () => {
        const isMobile = window.innerWidth < 768;
        document.body.classList.toggle('mobile-view', isMobile);

        // Adjust editor height based on screen height
        const editor = document.querySelector('.CodeMirror');
        if (editor) {
            const viewportHeight = window.innerHeight;
            const editorPosition = editor.getBoundingClientRect().top;
            const editorHeight = viewportHeight - editorPosition - 100; // Leave room for buttons

            editor.style.height = Math.max(300, editorHeight) + 'px';
        }
    };

    // Initial call
    handleResize();

    // Add event listener
    window.addEventListener('resize', handleResize);
}

/**
 * Add keyboard shortcuts
 */
function addKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl+S or Cmd+S to save
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            const submitButton = document.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.click();
            }
        }
    });
}
