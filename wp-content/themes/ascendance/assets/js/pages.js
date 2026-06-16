/**
 * Ascendance Intelligence Platform — Page JS
 * Handles: scroll reveal, FAQ accordion, filter tabs, mobile nav, newsletter form
 */
(function () {
    'use strict';

    /* ── Scroll Reveal (IntersectionObserver) ──────────────────── */
    function initScrollReveal() {
        const els = document.querySelectorAll('.reveal');
        if (!els.length) return;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

        els.forEach(el => observer.observe(el));
    }

    /* ── Mobile Nav Toggle ─────────────────────────────────────── */
    function initMobileNav() {
        const toggle = document.getElementById('menu-toggle');
        const nav    = document.getElementById('site-navigation');
        if (!toggle || !nav) return;

        toggle.addEventListener('click', () => {
            const expanded = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', String(!expanded));
            nav.classList.toggle('open');
        });

        // Close on outside click
        document.addEventListener('click', (e) => {
            if (!nav.contains(e.target) && !toggle.contains(e.target)) {
                nav.classList.remove('open');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    /* ── FAQ Accordion (native <details> + enhanced JS) ───────── */
    function initFaqAccordion() {
        const items = document.querySelectorAll('.faq-item');
        if (!items.length) return;

        items.forEach(item => {
            const summary = item.querySelector('.faq-question');
            if (!summary) return;

            summary.addEventListener('click', (e) => {
                e.preventDefault();
                const isOpen = item.classList.contains('open');

                // Close all others
                items.forEach(i => {
                    i.classList.remove('open');
                    i.removeAttribute('open');
                });

                if (!isOpen) {
                    item.classList.add('open');
                    item.setAttribute('open', '');
                }
            });
        });

        // FAQ category filter
        const catBtns = document.querySelectorAll('.faq-cat-btn');
        catBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                catBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                const cat = btn.dataset.cat;
                items.forEach(item => {
                    if (cat === 'all' || item.dataset.cat === cat) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    }

    /* ── Intelligence Hub Filter Tabs ─────────────────────────── */
    function initFilterTabs() {
        const tabs  = document.querySelectorAll('.filter-tab');
        const cards = document.querySelectorAll('.intel-card');
        if (!tabs.length) return;

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                const type = tab.dataset.type;
                cards.forEach(card => {
                    if (type === 'all' || card.dataset.postType === type) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });

        // Taxonomy select filters
        const selects = document.querySelectorAll('.filter-select');
        selects.forEach(select => {
            select.addEventListener('change', () => {
                const taxonomy = select.dataset.taxonomy;
                const value    = select.value;

                cards.forEach(card => {
                    // Only filter visible cards
                    if (card.style.display === 'none') return;

                    if (!value) {
                        card.style.opacity = '1';
                        card.style.pointerEvents = '';
                        return;
                    }

                    const cardTaxValues = (card.dataset[taxonomy] || '').split(',');
                    if (cardTaxValues.includes(value)) {
                        card.style.opacity = '1';
                        card.style.pointerEvents = '';
                    } else {
                        card.style.opacity = '0.25';
                        card.style.pointerEvents = 'none';
                    }
                });
            });
        });
    }

    /* ── Newsletter & Contact Form ─────────────────────────────── */
    function initForms() {
        // Newsletter inline form
        const nlForm = document.querySelector('.newsletter-form-inline, .newsletter-form-full');
        if (nlForm) {
            nlForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const emailInput = nlForm.querySelector('input[type="email"]');
                const btn        = nlForm.querySelector('button[type="submit"]');
                const email      = emailInput ? emailInput.value.trim() : '';

                if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    showFormMsg(nlForm, 'Please enter a valid email address.', 'error');
                    return;
                }

                // Replace with actual ESP endpoint when configured
                btn.textContent = 'Subscribed!';
                btn.disabled    = true;
                emailInput.value = '';
                showFormMsg(nlForm, 'Thank you! Your subscription is confirmed.', 'success');
            });
        }

        // Native contact form
        const contactForm = document.querySelector('.contact-form-native');
        if (contactForm) {
            contactForm.addEventListener('submit', (e) => {
                e.preventDefault();
                showFormMsg(contactForm, 'Message received. We\'ll respond within 2 business days.', 'success');
                contactForm.reset();
            });
        }
    }

    function showFormMsg(form, msg, type) {
        let msgEl = form.querySelector('.form-message');
        if (!msgEl) {
            msgEl = document.createElement('p');
            msgEl.className = 'form-message';
            form.appendChild(msgEl);
        }
        msgEl.textContent = msg;
        msgEl.style.cssText = `
            margin-top: 12px;
            font-family: var(--font-heading);
            font-size: 0.85rem;
            color: ${type === 'success' ? '#5BD65B' : '#F85149'};
            padding: 10px 14px;
            background: ${type === 'success' ? 'rgba(91,214,91,0.08)' : 'rgba(248,81,73,0.08)'};
            border-radius: 2px;
            border: 1px solid ${type === 'success' ? 'rgba(91,214,91,0.2)' : 'rgba(248,81,73,0.2)'};
        `;
    }

    /* ── Init ──────────────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', () => {
        initScrollReveal();
        initMobileNav();
        initFaqAccordion();
        initFilterTabs();
        initForms();
    });

})();
