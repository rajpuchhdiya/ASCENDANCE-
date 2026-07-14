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

    /* ── Intelligence Hub AJAX Filter ─────────────────────────── */
    function initFilterTabs() {
        const tabs      = document.querySelectorAll('.filter-tab');
        const selects   = document.querySelectorAll('.filter-select');
        const grid      = document.getElementById('intel-hub-grid');
        const pagWrap   = document.getElementById('intel-pagination');
        const loading   = document.getElementById('intel-loading');
        const countEl   = document.getElementById('intel-results-count');

        if (!grid) return; // not on intelligence hub page

        // Current filter state
        let state = { type: 'all', topic: '', region: '', page: 1 };

        /* ── Core AJAX fetch ──────────────────────────────────── */
        function fetchIntel() {
            if (!window.ascendance_params) return;

            // Show loading overlay
            if (loading) {
                loading.style.opacity = '1';
                loading.style.pointerEvents = 'auto';
            }
            grid.style.opacity = '0.4';
            grid.style.transition = 'opacity 0.2s ease';

            const formData = new FormData();
            formData.append('action', 'ascendance_intel_filter');
            formData.append('nonce',  ascendance_params.nonce);
            formData.append('intel-type', state.type);
            formData.append('topic',      state.topic);
            formData.append('region',     state.region);
            formData.append('page',       state.page);

            fetch(ascendance_params.ajax_url, {
                method: 'POST',
                body: formData,
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;

                // Inject cards
                grid.innerHTML = data.data.html;
                grid.style.opacity = '1';

                // Inject pagination
                if (pagWrap) {
                    pagWrap.innerHTML = data.data.pagination
                        ? `<div class="archive-pagination-inner">${data.data.pagination}</div>`
                        : '';
                    // Bind pagination link clicks
                    bindPaginationLinks();
                }

                // Update results count
                if (countEl) {
                    countEl.textContent = data.data.total + ' result' + (data.data.total === 1 ? '' : 's');
                }

                // Hide loading overlay
                if (loading) {
                    loading.style.opacity = '0';
                    loading.style.pointerEvents = 'none';
                }

                // Re-trigger scroll reveal on new cards
                initScrollReveal();

                // Smooth scroll to grid top
                grid.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            })
            .catch(() => {
                grid.style.opacity = '1';
                if (loading) {
                    loading.style.opacity = '0';
                    loading.style.pointerEvents = 'none';
                }
            });
        }

        /* ── Pagination link intercept ────────────────────────── */
        function bindPaginationLinks() {
            if (!pagWrap) return;
            const links = pagWrap.querySelectorAll('a.page-numbers');
            links.forEach(link => {
                link.addEventListener('click', e => {
                    e.preventDefault();

                    // Extract page number from href  (/page/3/ or ?paged=3)
                    const href = link.href || '';
                    let pageNum = 1;

                    const matchSlash = href.match(/\/page\/(\d+)/);
                    const matchQuery = href.match(/[?&]paged?=(\d+)/);

                    if (matchSlash) pageNum = parseInt(matchSlash[1], 10);
                    else if (matchQuery) pageNum = parseInt(matchQuery[1], 10);
                    else if (link.classList.contains('prev')) pageNum = Math.max(1, state.page - 1);
                    else if (link.classList.contains('next')) pageNum = state.page + 1;

                    state.page = pageNum;

                    // Highlight current-page button optimistically
                    pagWrap.querySelectorAll('.page-numbers').forEach(n => n.classList.remove('current'));
                    link.classList.add('current');

                    fetchIntel();
                });
            });
        }

        /* ── Filter tab binding ───────────────────────────────── */
        tabs.forEach(tab => {
            tab.addEventListener('click', e => {
                e.preventDefault();
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                state.type = tab.dataset.type || 'all';
                state.page = 1; // reset to page 1 on filter change
                fetchIntel();
            });
        });

        /* ── Select dropdown binding ──────────────────────────── */
        selects.forEach(select => {
            select.addEventListener('change', () => {
                const tax = select.dataset.taxonomy;
                if (tax === 'topic')  state.topic  = select.value;
                if (tax === 'region') state.region = select.value;
                state.page = 1; // reset to page 1 on filter change
                fetchIntel();
            });
        });

        // Bind pagination links on initial page load
        bindPaginationLinks();

        /* ── "Clear Filters" button — event delegation ────────── */
        // Use document-level delegation so it works on AJAX-injected buttons
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('[data-action="intel-reset"]');
            if (!btn) return;

            // Reset JS state
            state = { type: 'all', topic: '', region: '', page: 1 };

            // Reset UI controls
            tabs.forEach(t => t.classList.remove('active'));
            const allTab = document.querySelector('.filter-tab[data-type="all"]');
            if (allTab) allTab.classList.add('active');
            selects.forEach(s => { s.value = ''; });

            fetchIntel();
        });
    }

    /* ── Newsletter & Contact Form ─────────────────────────────── */
    function initForms() {
        // Newsletter inline/full forms
        const nlForms = document.querySelectorAll('.newsletter-form-inline, .newsletter-form-full, #ascendance-newsletter-form');
        nlForms.forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                const emailInput = form.querySelector('input[type="email"]');
                const nameInput  = form.querySelector('input[name="first_name"]');
                const btn        = form.querySelector('button[type="submit"]');
                const email      = emailInput ? emailInput.value.trim() : '';
                const firstName  = nameInput ? nameInput.value.trim() : '';

                if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    showFormMsg(form, 'Please enter a valid email address.', 'error');
                    return;
                }

                btn.disabled = true;
                const origText = btn.textContent;
                btn.textContent = 'Subscribing...';

                const ajaxUrl = (typeof ascendance_params !== 'undefined') ? ascendance_params.ajax_url : '/Ascendance/wp-admin/admin-ajax.php';
                
                const formData = new FormData();
                formData.append('action', 'ascendance_subscribe');
                formData.append('email', email);
                formData.append('first_name', firstName);

                fetch(ajaxUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        btn.textContent = 'Subscribed!';
                        if (emailInput) emailInput.value = '';
                        if (nameInput) nameInput.value = '';
                        showFormMsg(form, data.data.message || 'Thank you! Your subscription is confirmed.', 'success');
                        
                        // Push newsletter signup event to GTM dataLayer
                        window.dataLayer = window.dataLayer || [];
                        window.dataLayer.push({
                            'event': 'newsletter_signup',
                            'form_id': form.id || 'unknown'
                        });
                    } else {
                        btn.textContent = origText;
                        btn.disabled = false;
                        showFormMsg(form, data.data.message || 'Subscription failed. Please try again.', 'error');
                    }
                })
                .catch(err => {
                    btn.textContent = origText;
                    btn.disabled = false;
                    showFormMsg(form, 'Connection error. Please try again later.', 'error');
                });
            });
        });

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
