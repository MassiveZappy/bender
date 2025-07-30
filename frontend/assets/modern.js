/**
 * Bender - Modern JavaScript
 * A set of modern enhancements for the Bender application
 */

// Initialize when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
  // Apply modern layout and components
  modernizeLayout();

  // Setup responsive navigation
  setupNavigation();

  // Enhance forms
  enhanceForms();

  // Apply animations
  applyAnimations();

  // Setup article enhancements
  enhanceArticles();

  // Add responsive behaviors
  addResponsiveBehaviors();
});

/**
 * Apply modern layout structure to the page
 */
function modernizeLayout() {
  // Check if already modernized
  if (document.querySelector('.modern-layout')) return;

  // Add modern layout class to body
  document.body.classList.add('modern-layout');

  // Wrap content in proper containers if not already
  if (!document.querySelector('.container')) {
    // Collect all body children
    const bodyContent = Array.from(document.body.children);

    // Create container
    const container = document.createElement('div');
    container.className = 'container';

    // Create header
    const header = document.createElement('header');
    header.className = 'header';
    const headerContainer = document.createElement('div');
    headerContainer.className = 'header-container';
    header.appendChild(headerContainer);

    // Create logo/site title
    const logo = document.createElement('a');
    logo.href = 'index.php';
    logo.className = 'logo';
    logo.textContent = 'Bender';
    headerContainer.appendChild(logo);

    // Create navigation container
    const nav = document.createElement('nav');
    nav.className = 'nav';
    headerContainer.appendChild(nav);

    // Create mobile menu toggle
    const menuToggle = document.createElement('button');
    menuToggle.className = 'mobile-menu-toggle';
    menuToggle.innerHTML = '☰';
    menuToggle.setAttribute('aria-label', 'Toggle menu');
    headerContainer.appendChild(menuToggle);

    // Create main content area
    const main = document.createElement('main');
    main.className = 'main';

    // Create footer
    const footer = document.createElement('footer');
    footer.className = 'footer';
    const footerContainer = document.createElement('div');
    footerContainer.className = 'container';
    footerContainer.innerHTML = '<p class="text-center">Bender - Satirical News Generator</p>';
    footer.appendChild(footerContainer);

    // Add elements to DOM
    document.body.appendChild(header);
    document.body.appendChild(main);
    document.body.appendChild(footer);

    // Move original content to main
    bodyContent.forEach(child => {
      main.appendChild(child);
    });
  }

  // Find heading and move to appropriate place
  const heading = document.querySelector('h1, h2');
  if (heading && heading.textContent.includes('Bender')) {
    // This might be the page title, move it
    const logo = document.querySelector('.logo');
    if (logo) {
      logo.textContent = heading.textContent;
      heading.remove();
    }
  }

  // Add page title if not present
  if (!document.title || document.title === '') {
    document.title = 'Bender - Satirical News';
  }

  // Convert regular elements to modern UI components
  convertButtons();
  convertTables();
  convertLists();
  convertForms();
}

/**
 * Setup responsive navigation
 */
function setupNavigation() {
  // Find navigation links
  const navLinks = document.querySelectorAll('a[href="index.php"], a[href="dashboard.php"], a[href="login.php"], a[href="signup.php"], a[href="logout.php"], a[href="admin.php"]');

  // If nav exists, add links to it
  const nav = document.querySelector('.nav');
  if (nav && navLinks.length > 0) {
    // Clear existing links
    while (nav.firstChild) {
      nav.removeChild(nav.firstChild);
    }

    // Add nav links
    navLinks.forEach(link => {
      const newLink = document.createElement('a');
      newLink.href = link.href;
      newLink.className = 'nav-link';
      newLink.textContent = link.textContent;
      nav.appendChild(newLink);

      // Remove original link
      link.style.display = 'none';
    });
  }

  // Setup mobile menu toggle
  const menuToggle = document.querySelector('.mobile-menu-toggle');
  if (menuToggle && nav) {
    menuToggle.addEventListener('click', function() {
      nav.classList.toggle('show');
    });

    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
      if (!nav.contains(event.target) && !menuToggle.contains(event.target)) {
        nav.classList.remove('show');
      }
    });
  }

  // Highlight current page
  const currentPage = window.location.pathname.split('/').pop();
  const currentLink = document.querySelector(`.nav-link[href="${currentPage}"]`);
  if (currentLink) {
    currentLink.classList.add('active');
  }
}

/**
 * Convert buttons to modern style
 */
function convertButtons() {
  // Convert buttons
  document.querySelectorAll('button, input[type="submit"], input[type="button"]').forEach(button => {
    if (button.classList.contains('btn')) return;

    button.classList.add('btn');

    // Determine button type
    if (button.textContent && button.textContent.includes('Delete')) {
      button.classList.add('btn-danger');
    } else {
      button.classList.add('btn-primary');
    }
  });

  // Convert link buttons
  document.querySelectorAll('a').forEach(link => {
    if (link.classList.contains('btn') || link.classList.contains('nav-link') || link.classList.contains('logo')) return;

    // If it looks like an action link, convert to button
    if (
      link.href.includes('delete') ||
      link.href.includes('edit') ||
      link.href.includes('create') ||
      link.href.includes('view') ||
      link.textContent.includes('Sign Up') ||
      link.textContent.includes('Login') ||
      link.textContent.includes('Logout') ||
      link.textContent.includes('Create')
    ) {
      link.classList.add('btn');

      if (link.href.includes('delete')) {
        link.classList.add('btn-danger');
      } else if (link.href.includes('view')) {
        link.classList.add('btn-secondary');
      } else {
        link.classList.add('btn-primary');
      }
    }
  });
}

/**
 * Convert tables to modern style
 */
function convertTables() {
  document.querySelectorAll('table').forEach(table => {
    if (table.classList.contains('table')) return;

    table.classList.add('table', 'table-hover');

    // Add thead and tbody if not present
    if (!table.querySelector('thead') && table.rows.length > 0) {
      const thead = document.createElement('thead');
      const tbody = document.createElement('tbody');

      // Assume first row is header
      thead.appendChild(table.rows[0]);

      // Move remaining rows to tbody
      while (table.rows.length > 0) {
        tbody.appendChild(table.rows[0]);
      }

      table.appendChild(thead);
      table.appendChild(tbody);
    }
  });
}

/**
 * Convert lists to modern style
 */
function convertLists() {
  // Convert regular lists
  document.querySelectorAll('ul').forEach(list => {
    if (list.classList.contains('list-group') || list.classList.contains('article-list')) return;

    // Check if this is an article list
    const listItems = list.querySelectorAll('li');
    let isArticleList = false;

    for (const item of listItems) {
      if (
        item.textContent.includes('Article') ||
        item.innerHTML.includes('view_article.php') ||
        item.innerHTML.includes('deleteArticle')
      ) {
        isArticleList = true;
        break;
      }
    }

    if (isArticleList) {
      list.classList.add('article-list');

      // Convert list items
      listItems.forEach(item => {
        item.classList.add('article-item');

        // Structure article item
        const title = item.querySelector('a');
        if (title) {
          title.classList.add('article-title');
        }

        // Move action buttons to a container
        const buttons = item.querySelectorAll('a, button');
        if (buttons.length > 1) {
          const actions = document.createElement('div');
          actions.className = 'd-flex mt-2';

          buttons.forEach(button => {
            if (button !== title) {
              actions.appendChild(button);
            }
          });

          item.appendChild(actions);
        }
      });
    } else {
      // Regular list
      list.classList.add('list-group');

      listItems.forEach(item => {
        item.classList.add('list-group-item');
      });
    }
  });
}

/**
 * Enhance forms with modern styling
 */
function enhanceForms() {
  document.querySelectorAll('form').forEach(form => {
    // Skip if already enhanced
    if (form.classList.contains('modern-form')) return;
    form.classList.add('modern-form');

    // Add proper form-group structure
    const inputs = form.querySelectorAll('input:not([type="submit"]), select, textarea');
    inputs.forEach(input => {
      // Skip if already wrapped
      if (input.parentElement.classList.contains('form-group')) return;

      // Add form-control class
      input.classList.add('form-control');

      // Create form group
      const formGroup = document.createElement('div');
      formGroup.className = 'form-group';

      // Create label if needed
      if (input.placeholder) {
        const label = document.createElement('label');
        label.className = 'form-label';
        label.textContent = input.placeholder;

        // Generate ID if not present
        if (!input.id) {
          input.id = `input-${Math.random().toString(36).substring(2, 9)}`;
        }
        label.htmlFor = input.id;

        formGroup.appendChild(label);
      }

      // Move input to form group
      input.parentNode.insertBefore(formGroup, input);
      formGroup.appendChild(input);

      // Remove <br> that might follow inputs
      if (formGroup.nextElementSibling && formGroup.nextElementSibling.tagName === 'BR') {
        formGroup.nextElementSibling.remove();
      }
    });

    // Style submit buttons
    const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
    submitButtons.forEach(button => {
      button.classList.add('btn', 'btn-primary', 'mt-3');
    });
  });
}

/**
 * Apply animations to page elements
 */
function applyAnimations() {
  // Fade in effect for page elements
  document.querySelectorAll('.card, .article-item, .list-group-item').forEach((el, index) => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'opacity 0.3s ease, transform 0.3s ease';

    setTimeout(() => {
      el.style.opacity = '1';
      el.style.transform = 'translateY(0)';
    }, 100 + (index * 50));
  });

  // Button hover effects
  document.querySelectorAll('.btn').forEach(btn => {
    btn.addEventListener('mouseenter', function() {
      this.style.transform = 'translateY(-2px)';
    });

    btn.addEventListener('mouseleave', function() {
      this.style.transform = 'translateY(0)';
    });
  });
}

/**
 * Enhance article display and editing
 */
function enhanceArticles() {
  // Check if we're on an article page
  const contentTextarea = document.querySelector('textarea[name="content"]');
  if (contentTextarea) {
    // Article editing page
    const form = contentTextarea.closest('form');
    if (form) {
      form.classList.add('article-form');

      // Make sure the editor has proper styling
      if (window.SimpleMDE) {
        // SimpleMDE is being used - enhance it
        document.querySelectorAll('.CodeMirror').forEach(editor => {
          editor.style.minHeight = '400px';
          editor.style.borderRadius = 'var(--border-radius)';
        });
      }
    }
  }

  // For article view pages
  const articleContent = document.querySelector('.article-content');
  if (articleContent) {
    // Add better styling to the article content
    articleContent.querySelectorAll('img').forEach(img => {
      img.classList.add('img-fluid', 'rounded', 'my-3');
    });

    articleContent.querySelectorAll('blockquote').forEach(quote => {
      quote.classList.add('border-left', 'pl-3', 'my-4');
    });
  }
}

/**
 * Add responsive behaviors for different screen sizes
 */
function addResponsiveBehaviors() {
  // Handle window resize
  function handleResize() {
    const isMobile = window.innerWidth < 768;
    document.body.classList.toggle('mobile-view', isMobile);

    // Adjust elements based on screen size
    if (isMobile) {
      // Mobile-specific adjustments
      document.querySelectorAll('.btn').forEach(btn => {
        if (!btn.classList.contains('btn-sm') && !btn.classList.contains('btn-lg')) {
          btn.classList.add('btn-sm');
        }
      });
    } else {
      // Desktop-specific adjustments
      document.querySelectorAll('.btn.btn-sm').forEach(btn => {
        if (!btn.getAttribute('data-original-small')) {
          btn.classList.remove('btn-sm');
        }
      });
    }
  }

  // Mark buttons that should stay small
  document.querySelectorAll('.btn.btn-sm').forEach(btn => {
    btn.setAttribute('data-original-small', 'true');
  });

  // Initial call
  handleResize();

  // Add event listener
  window.addEventListener('resize', handleResize);

  // Improve form usability on mobile
  if ('ontouchstart' in window) {
    document.querySelectorAll('input, select, textarea').forEach(input => {
      input.addEventListener('focus', function() {
        this.parentElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
      });
    });
  }
}

// Add a helper function to detect when fonts are loaded
function fontLoaded() {
  document.documentElement.classList.add('fonts-loaded');
}

// Load fonts
if ('fonts' in document) {
  Promise.all([
    document.fonts.load('1em Inter'),
    document.fonts.load('1em Fira Code')
  ]).then(fontLoaded);
} else {
  // Fallback for browsers that don't support the Font Loading API
  setTimeout(fontLoaded, 1000);
}
