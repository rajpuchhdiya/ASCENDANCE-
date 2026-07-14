/**
 * Ascendance theme client-side interactivity scripts.
 * Written for performance, responsiveness, and visual styling.
 */

document.addEventListener('DOMContentLoaded', () => {
    initHeaderScroll();
    initMobileMenu();
    initScrollReveal();
    initHeaderSearch();
    initHeaderAccountDropdown();
    initThemeToggle();
});

/**
 * 1. Toggle glassmorphism styling on header when scrolling down
 */
function initHeaderScroll() {
    const header = document.getElementById('masthead');
    if (!header) return;

    const toggleHeaderClass = () => {
        if (window.scrollY > 40) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    };

    // Check on initial page load
    toggleHeaderClass();

    // Listen on scroll events
    window.addEventListener('scroll', toggleHeaderClass);
}

/**
 * 2. Mobile navigation menu drawer toggle (Burger Menu)
 */
function initMobileMenu() {
    const toggleBtn = document.getElementById('menu-toggle');
    const navMenu = document.getElementById('site-navigation');
    
    if (!toggleBtn || !navMenu) return;

    const toggleIcon = toggleBtn.querySelector('i');

    const toggleMenu = (event) => {
        event.stopPropagation();
        const isOpen = navMenu.classList.toggle('open');
        toggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        
        if (isOpen) {
            toggleIcon.className = 'fa-solid fa-xmark';
        } else {
            toggleIcon.className = 'fa-solid fa-bars';
        }
    };

    const closeMenu = () => {
        if (navMenu.classList.contains('open')) {
            navMenu.classList.remove('open');
            toggleBtn.setAttribute('aria-expanded', 'false');
            toggleIcon.className = 'fa-solid fa-bars';
        }
    };

    toggleBtn.addEventListener('click', toggleMenu);

    // Close menu when clicking outside
    document.addEventListener('click', (event) => {
        if (!navMenu.contains(event.target) && !toggleBtn.contains(event.target)) {
            closeMenu();
        }
    });

    // Close menu when clicking on nav link (for single-page anchors)
    const navLinks = navMenu.querySelectorAll('a');
    navLinks.forEach(link => {
        link.addEventListener('click', closeMenu);
    });
}

/**
 * 3. Scroll Reveal Animation triggers using Intersection Observer
 */
function initScrollReveal() {
    const revealElements = document.querySelectorAll('.reveal');
    if (revealElements.length === 0) return;

    // Fallback: If Intersection Observer is not supported, reveal all immediately
    if (!('IntersectionObserver' in window)) {
        revealElements.forEach(el => el.classList.add('active'));
        return;
    }

    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.12 // Trigger when 12% of the element is visible
    };

    const observerCallback = (entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                // Unobserve once revealed to keep layout performant
                observer.unobserve(entry.target);
            }
        });
    };

    const observer = new IntersectionObserver(observerCallback, observerOptions);

    revealElements.forEach(el => {
        observer.observe(el);
    });
}

/**
 * 4. Expandable search icon/field toggles
 */
function initHeaderSearch() {
    const searchWrap = document.querySelector('.header-search-wrap');
    if (!searchWrap) return;
    
    const searchInput = searchWrap.querySelector('.search-field');
    const searchBtn = searchWrap.querySelector('.search-submit');
    
    searchBtn.addEventListener('click', (e) => {
        if (!searchWrap.classList.contains('active')) {
            e.preventDefault();
            searchWrap.classList.add('active');
            searchInput.focus();
        } else if (searchInput.value.trim() === '') {
            e.preventDefault();
            searchWrap.classList.remove('active');
            searchInput.blur();
        }
    });
    
    // Close search when clicking outside
    document.addEventListener('click', (e) => {
        if (!searchWrap.contains(e.target)) {
            searchWrap.classList.remove('active');
        }
    });

    searchInput.addEventListener('focus', () => {
        searchWrap.classList.add('active');
    });

    searchInput.addEventListener('blur', () => {
        if (searchInput.value.trim() === '') {
            searchWrap.classList.remove('active');
        }
    });
}

/**
 * 5. Desktop Account dropdown toggle
 */
function initHeaderAccountDropdown() {
    const dropdown = document.querySelector('.header-account-dropdown');
    if (!dropdown) return;
    
    const toggle = dropdown.querySelector('.account-toggle');
    
    toggle.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdown.classList.toggle('open');
    });
    
    document.addEventListener('click', (e) => {
        if (!dropdown.contains(e.target)) {
            dropdown.classList.remove('open');
        }
    });
}

/**
 * 6. Dynamic Theme Toggle (Dark/Light Mode)
 */
function initThemeToggle() {
    const desktopToggle = document.getElementById('theme-toggle');
    const mobileToggle = document.getElementById('theme-toggle-mobile');

    if (!desktopToggle && !mobileToggle) return;

    const getTheme = () => {
        return document.documentElement.getAttribute('data-theme') || 'light';
    };

    const updateToggleUI = (theme) => {
        const desktopIcon = desktopToggle ? desktopToggle.querySelector('i') : null;
        const mobileIcon = mobileToggle ? mobileToggle.querySelector('i') : null;
        const mobileText = mobileToggle ? mobileToggle.querySelector('span') : null;

        if (theme === 'dark') {
            if (desktopIcon) {
                desktopIcon.className = 'fa-solid fa-sun';
            }
            if (mobileIcon) {
                mobileIcon.className = 'fa-solid fa-sun';
            }
            if (mobileText) {
                mobileText.textContent = 'Light Mode';
            }
        } else {
            if (desktopIcon) {
                desktopIcon.className = 'fa-solid fa-moon';
            }
            if (mobileIcon) {
                mobileIcon.className = 'fa-solid fa-moon';
            }
            if (mobileText) {
                mobileText.textContent = 'Dark Mode';
            }
        }
    };

    const setTheme = (theme) => {
        document.documentElement.setAttribute('data-theme', theme);
        if (theme === 'dark') {
            document.documentElement.classList.add('dark-theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark-theme', 'dark');
        }
        try {
            localStorage.setItem('theme', theme);
        } catch (e) {
            // Local storage might be blocked in some browser settings
        }
        updateToggleUI(theme);
    };

    const toggleTheme = () => {
        const currentTheme = getTheme();
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        setTheme(newTheme);
    };

    if (desktopToggle) {
        desktopToggle.addEventListener('click', toggleTheme);
    }
    if (mobileToggle) {
        mobileToggle.addEventListener('click', toggleTheme);
    }

    // Initialize UI to match current applied state
    updateToggleUI(getTheme());
}
