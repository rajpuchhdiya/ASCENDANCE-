/**
 * Ascendance theme client-side interactivity scripts.
 * Written for performance, responsiveness, and visual styling.
 */

document.addEventListener('DOMContentLoaded', () => {
    initHeaderScroll();
    initMobileMenu();
    initScrollReveal();
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
