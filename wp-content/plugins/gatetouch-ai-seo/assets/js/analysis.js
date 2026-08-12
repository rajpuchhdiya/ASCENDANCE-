jQuery(document).ready(function($) {
    'use strict';

    const ajax_url = (window.gatetouchAdmin && window.gatetouchAdmin.ajax_url) || window.ajaxurl || '';
    const nonce = (window.gatetouchAdmin && window.gatetouchAdmin.nonce) || '';

    function renderChecklistItem(issue, defaultEditUrl) {
        let template = $('#riq-checklist-template').html();
        if (!template) return '';

        let status = issue.type === 'error' ? 'fail' : (issue.type === 'warning' ? 'warn' : 'pass');
        if (issue.status) status = issue.status;

        // Map titles to specific high-fidelity icons based on the new screenshot
        let iconType = 'info';
        const title = (issue.title || '').toLowerCase();

        if (status === 'pass') {
            iconType = 'check';
        } else if (title.includes('image') || title.includes('img')) {
            iconType = 'image';
        } else if (title.includes('content') || title.includes('description')) {
            iconType = 'file';
        } else if (title.includes('kw') || title.includes('keyword')) {
            if (title.includes('title')) iconType = 'warning';
            else iconType = 'hashtag';
        } else if (status === 'fail') {
            iconType = 'warning';
        }

        let iconSvg = '';
        const strokeWidth = 2.5;
        const size = 20;

        switch (iconType) {
            case 'check':
                iconSvg = `<svg width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="${strokeWidth}" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>`;
                break;
            case 'image':
                iconSvg = `<svg width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="${strokeWidth}" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>`;
                break;
            case 'file':
                iconSvg = `<svg width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="${strokeWidth}" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>`;
                break;
            case 'hashtag':
                iconSvg = `<svg width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="${strokeWidth}" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="9" x2="20" y2="9"></line><line x1="4" y1="15" x2="20" y2="15"></line><line x1="10" y1="3" x2="8" y2="21"></line><line x1="16" y1="3" x2="14" y2="21"></line></svg>`;
                break;
            case 'warning':
                iconSvg = `<svg width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="${strokeWidth}" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>`;
                break;
            default: // info
                iconSvg = `<svg width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="${strokeWidth}" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>`;
        }

        let action_btn = '';
        if (issue.action_btn) {
            action_btn = `<a href="${issue.action_btn.link}" class="gatetouch-btn gatetouch-btn--ghost gatetouch-btn--sm" style="border:1px solid #cbd5e1;">${issue.action_btn.text}</a>`;
        } else if (defaultEditUrl && status !== 'pass') {
            action_btn = `<a href="${defaultEditUrl}" class="gatetouch-btn gatetouch-btn--ghost gatetouch-btn--sm" style="border:1px solid #cbd5e1;">Fix Issue</a>`;
        }

        let learnMore = issue.learn_more || '#';

        return template.replaceAll('{{id}}', issue.id || Math.random().toString(36).substr(2, 9))
                       .replace('{{status}}', status)
                       .replace('{{icon}}', iconSvg)
                       .replace('{{title}}', issue.title)
                       .replace('{{fix_beginner}}', issue.fix_beginner || issue.explanation || 'Review your SEO settings for this page.')
                       .replace('{{action_btn}}', action_btn)
                       .replace('{{learn_more}}', learnMore);
    }

    function sortChecks(checks) {
        if (!checks || !Array.isArray(checks)) return [];
        const order = { 'fail': 0, 'error': 0, 'warn': 1, 'warning': 1, 'pass': 2 };
        return [...checks].sort((a, b) => {
            const sA = a.status || (a.type === 'error' ? 'fail' : (a.type === 'warning' ? 'warn' : 'pass'));
            const sB = b.status || (b.type === 'error' ? 'fail' : (b.type === 'warning' ? 'warn' : 'pass'));
            return (order[sA] ?? 2) - (order[sB] ?? 2);
        });
    }

    function runAudit(triggerButton) {
        const scoreVal = $('#gatetouch-home-score');
        const labelVal = $('#gatetouch-home-label');
        const homeChecks = $('#gatetouch-home-checks');
        const refreshButton = triggerButton ? $(triggerButton) : $();

        const circle    = $('#gatetouch-score-circle');
        const issueCount = $('#gatetouch-issue-count');
        const warnCount  = $('#gatetouch-warn-count');
        const goodCount  = $('#gatetouch-good-count');
        let buttonState = 'complete';

        function restoreRefreshButton() {
            if (!refreshButton.length) return;

            refreshButton
                .prop('disabled', false)
                .text(buttonState === 'complete' ? 'Analysis Complete' : 'Analysis Failed');

            setTimeout(function() {
                refreshButton.html(refreshButton.data('original-html'));
            }, 1200);
        }

        if (refreshButton.length) {
            refreshButton.data('original-html', refreshButton.data('original-html') || refreshButton.html());
            refreshButton.prop('disabled', true).html('<span class="riq-spinner"></span> Analyzing...');
        }

        // Reset UI
        homeChecks.html('<div class="gatetouch-card" style="padding:50px; text-align:center; color:#64748b;"><div class="riq-spinner" style="margin:0 auto 20px;"></div>Analyzing your homepage...</div>');

        if (!ajax_url || !nonce) {
            buttonState = 'failed';
            homeChecks.html('<div class="gatetouch-card" style="padding:40px; text-align:center; color:#ef4444;">Configuration error: audit scripts could not find the WordPress AJAX endpoint.</div>');
            restoreRefreshButton();
            return;
        }

        $.post(ajax_url, {
            action: 'gatetouch_run_homepage_audit',
            nonce: nonce
        }, function(res) {
            if (res.success) {
                const data = res.data;
                buttonState = 'complete';

                // Animate Score
                animateValue(scoreVal, 0, data.score, 1000);

                labelVal.text(data.label).css('color', data.color);
                circle.css({ 'stroke': data.color, 'stroke-dasharray': data.score + ', 100' });

                let html = '';
                let issues = 0;
                let warnings = 0;
                let good = 0;

                const all = sortChecks([...(data.site_issues || []), ...(data.checks || [])]);

                if (all.length > 0) {
                    all.forEach(item => {
                        html += renderChecklistItem(item, data.edit_url);
                        const s = item.status || (item.type === 'error' ? 'fail' : (item.type === 'warning' ? 'warn' : 'pass'));
                        if (s === 'fail') issues++;
                        else if (s === 'warn') warnings++;
                        else good++;
                    });
                    homeChecks.html(html);
                } else {
                    homeChecks.html('<div class="gatetouch-empty-state" style="padding:60px; text-align:center;"><div style="font-size:40px; margin-bottom:15px;">✅</div><h3 style="margin:0; font-size:18px;">Perfect Score!</h3><p style="color:#64748b;">No optimization issues found on your homepage.</p></div>');
                }

                issueCount.text(issues);
                warnCount.text(warnings);
                goodCount.text(good);
            } else {
                buttonState = 'failed';
                homeChecks.html('<div class="gatetouch-card" style="padding:40px; text-align:center; color:#ef4444;">Error: ' + (res.data || 'Unknown error') + '</div>');
            }
        }).fail(function(xhr) {
            buttonState = 'failed';
            homeChecks.html('<div class="gatetouch-card" style="padding:40px; text-align:center; color:#ef4444;">Server Error: Could not complete the audit.</div>');
        }).always(function() {
            restoreRefreshButton();
        });
    }

    function animateValue($el, start, end, duration) {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            $el.text(Math.floor(progress * (end - start) + start));
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }

    function loadFullAudit() {
        const tableBody = $('#gatetouch-audit-results');

        $.post(ajax_url, {
            action: 'gatetouch_fetch_audit_results',
            nonce: nonce
        }, function(res) {
            if (res.success && res.data) {
                let html = '';
                res.data.forEach(row => {
                    let checksHtml = '';
                    if (row.checks && row.checks.length > 0) {
                        const sorted = sortChecks(row.checks);
                        sorted.forEach(check => {
                            checksHtml += renderChecklistItem(check, row.edit_url);
                        });
                    } else {
                        checksHtml = '<div style="padding:20px; text-align:center; color:#94a3b8;">No detailed issues found. Run an audit to scan this page.</div>';
                    }

                    const scoreColor = row.score >= 80 ? '#10b981' : (row.score >= 50 ? '#f59e0b' : '#ef4444');

                    html += `
                        <tr class="riq-site-row" data-id="${row.id}">
                            <td style="padding:15px 20px;">
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <span class="riq-row-toggle">▶</span>
                                    <div>
                                        <div style="font-weight:700; color:var(--riq-text);">${row.title}</div>
                                        <div style="font-size:11px; color:var(--riq-text-light);">${row.url}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding:15px 20px; text-align:center;">
                                <span class="gatetouch-score-pill" style="background:${scoreColor}">
                                    ${row.score}
                                </span>
                            </td>
                            <td style="padding:15px 20px;">
                                <span class="gatetouch-badge" style="background:#f1f5f9; color:#64748b; text-transform:none;">${row.schema || 'No Schema'}</span>
                            </td>
                            <td style="padding:15px 20px; text-align:right;">
                                <div style="display:flex; justify-content:flex-end; gap:8px;">
                                    <a href="${row.edit_url}" class="gatetouch-btn gatetouch-btn--ghost gatetouch-btn--sm">Edit</a>
                                    <button type="button" class="gatetouch-btn gatetouch-btn--primary gatetouch-btn--sm riq-scan-single" data-id="${row.id}">Audit</button>
                                </div>
                            </td>
                        </tr>
                        <tr id="riq-checks-row-${row.id}" style="display:none; background:#f8fafc;">
                            <td colspan="4" style="padding:0;">
                                <div style="padding:30px 40px; border-top:1px solid #e2e8f0; border-bottom:2px solid #e2e8f0;">
                                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:20px;">
                                        <div style="width:32px; height:32px; background:var(--riq-ai-gradient); border-radius:8px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:16px;">🧠</div>
                                        <h4 style="margin:0; font-size:15px; font-weight:800; color:var(--riq-text);">Page Optimization Analysis</h4>
                                    </div>
                                    <div class="gatetouch-checklist-container">
                                        ${checksHtml}
                                    </div>
                                </div>
                            </td>
                        </tr>
                    `;
                });
                tableBody.html(html);

                if (window.riqActiveAuditRowId) {
                    const activeRow = $('#riq-checks-row-' + window.riqActiveAuditRowId);
                    const activeIcon = $('.riq-site-row[data-id="' + window.riqActiveAuditRowId + '"]').find('.riq-row-toggle');
                    if (activeRow.length) {
                        activeRow.show();
                        activeIcon.css('transform', 'rotate(90deg)');
                    }
                }
            } else {
                tableBody.html('<tr><td colspan="4" style="padding:60px; text-align:center; color:#64748b;">No audit data found. Dispatch a site-wide scan to begin.</td></tr>');
            }
        });
    }

    // Event Handlers
    $(document).on('click', '.gatetouch-checklist-header', function() {
        const item = $(this).closest('.gatetouch-checklist-item');
        item.toggleClass('active').find('.gatetouch-checklist-body').slideToggle(250);
    });

    $(document).on('click', '.riq-scan-single', function(e) {
        e.stopPropagation();
        const btn = $(this);
        const post_id = btn.data('id');
        btn.addClass('loading').text('Scanning...');
        window.riqActiveAuditRowId = post_id;

        $.post(ajax_url, {
            action: 'gatetouch_audit_single_post',
            post_id: post_id,
            nonce: nonce
        }, function(res) {
            btn.removeClass('loading').text('Audit');
            loadFullAudit();
        });
    });

    $(document).on('click', '.riq-site-row', function(e) {
        if ($(e.target).closest('a, button').length) return;

        const id = $(this).data('id');
        const checksRow = $('#riq-checks-row-' + id);
        const toggleIcon = $(this).find('.riq-row-toggle');

        if (checksRow.is(':visible')) {
            checksRow.slideUp(200);
            toggleIcon.css('transform', 'rotate(0deg)');
            if (window.riqActiveAuditRowId === id) window.riqActiveAuditRowId = null;
        } else {
            // Close other rows for enterprise feel
            $('.riq-site-row .riq-row-toggle').css('transform', 'rotate(0deg)');
            $('[id^="riq-checks-row-"]').slideUp(200);

            checksRow.slideDown(200);
            toggleIcon.css('transform', 'rotate(90deg)');
            window.riqActiveAuditRowId = id;
        }
    });

    $('.gatetouch-page-tab').on('click', function(e) {
        e.preventDefault();
        const target = $(this).data('target');

        $('.gatetouch-page-tab').removeClass('active');
        $(this).addClass('active');

        $('.gatetouch-sub-tab-content').hide();
        $('#' + target).fadeIn(300);

        if (target === 'tab-site') loadFullAudit();
    });

    $('#gatetouch-trigger-full-scan').on('click', function() {
        const btn = $(this);
        const originalText = btn.text();
        btn.prop('disabled', true).html('<span class="riq-spinner"></span> Initializing...');

        $.post(ajax_url, {
            action: 'gatetouch_get_all_audit_ids',
            nonce: nonce
        }, function(res) {
            if (res.success && res.data > 0) {
                const total = res.data;
                btn.html(`<span class="riq-spinner"></span> Scanning 0/${total}`);

                // Fetch IDs to process
                $.post(ajax_url, {
                    action: 'gatetouch_get_audit_id_list',
                    nonce: nonce
                }, function(resIds) {
                    if (resIds.success && resIds.data.length > 0) {
                        processScanBatch(resIds.data, 0, total, btn, originalText);
                    }
                });
            } else {
                btn.prop('disabled', false).text(originalText);
                alert('No content found to scan.');
            }
        });
    });

    function processScanBatch(ids, current, total, btn, originalText) {
        if (ids.length === 0) {
            btn.html('✅ Scan Complete').css({'background': '#10b981', 'color': 'white'});
            setTimeout(() => {
                btn.prop('disabled', false).text(originalText).css({'background': '', 'color': ''});
                loadFullAudit();
            }, 2000);
            return;
        }

        const postId = ids.shift();
        const nextCount = current + 1;
        btn.html(`<span class="riq-spinner"></span> Scanning ${nextCount}/${total}`);

        $.post(ajax_url, {
            action: 'gatetouch_audit_single_post',
            post_id: postId,
            nonce: nonce
        }, function() {
            processScanBatch(ids, nextCount, total, btn, originalText);
        }).fail(function() {
            // Skip on error but continue
            processScanBatch(ids, nextCount, total, btn, originalText);
        });
    }

    $(document).on('click', '#gatetouch-refresh-home', function(e) {
        e.preventDefault();
        runAudit(this);
    });

    // Headline Analyzer
    $('#gatetouch-headline-analyze-btn').on('click', function() {
        const headline = $('#gatetouch-headline-input').val();
        if (!headline) return;

        const btn = $(this);
        btn.prop('disabled', true).html('<span class="riq-spinner"></span>');

        $.post(ajax_url, {
            action: 'gatetouch_analyze_headline',
            headline: headline,
            nonce: nonce
        }, function(res) {
            btn.prop('disabled', false).text('Analyze');
            if (res.success) {
                $('#gatetouch-headline-result').html(res.data);
            }
        });
    });

    // Competitor Analysis
    $('#gatetouch-analyze-competitor-btn').on('click', function() {
        const url = $('#gatetouch-competitor-url').val();
        if (!url) return;

        const btn = $(this);
        btn.prop('disabled', true).html('<span class="riq-spinner"></span> Analyzing...');
        $('#gatetouch-competitor-results').html('<div class="gatetouch-card" style="padding:80px; text-align:center;"><div class="riq-spinner" style="margin:0 auto 20px;"></div>Processing deep competitor audit...</div>');

        $.post(ajax_url, {
            action: 'gatetouch_analyze_competitor',
            url: url,
            nonce: nonce
        }, function(res) {
            btn.prop('disabled', false).text('Analyze Site');
            if (res.success) {
                // Rendering logic already handled in PHP if we wanted, but let's keep it consistent
                $('#gatetouch-competitor-results').html(res.data);
            } else {
                 $('#gatetouch-competitor-results').html(`<div class="gatetouch-card" style="padding:40px; text-align:center; color:#ef4444;">${res.data}</div>`);
            }
        });
    });

    // Crawler
    $('#gatetouch-start-crawl-btn').on('click', function() {
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="riq-spinner"></span> Crawling...');

        $.post(ajax_url, {
            action: 'gatetouch_trigger_crawl',
            nonce: nonce
        }, function(res) {
            if (res.success) {
                 location.reload(); // Simplest for crawler state
            }
        });
    });

    // Initializations
    runAudit();
    if ($('.gatetouch-page-tab.active').data('target') === 'tab-site') loadFullAudit();

    $(document).on('change', '#riq-toggle-passed', function() {
        const checked = $(this).is(':checked');
        if (checked) {
            $('.gatetouch-checklist-container').removeClass('riq-hide-passed');
        } else {
            $('.gatetouch-checklist-container').addClass('riq-hide-passed');
        }
    });
});
