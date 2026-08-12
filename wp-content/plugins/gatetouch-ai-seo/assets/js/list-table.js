/* assets/js/list-table.js */
jQuery(function($) {
    'use strict';

    const { ajax_url, nonce } = gatetouchList;

    /**
     * Handle Row Actions (Quick AI Actions)
     */
    $(document).on('click', '.gatetouch-row-action', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const action = $btn.data('action');
        const post_id = $btn.data('id');
        const $row = $btn.closest('tr');

        if ($btn.hasClass('is-loading')) return;

        // Visual Feedback
        $btn.addClass('is-loading').css('opacity', 0.5);
        $row.addClass('gatetouch-is-optimizing');

        // Map UI actions to backend AJAX actions
        let ajaxAction = 'gatetouch_analyze_seo'; // Default
        if (action === 'optimize') ajaxAction = 'gatetouch_generate_meta';
        
        $.post(ajax_url, {
            action: ajaxAction,
            nonce: nonce, // Note: The list table might need its own nonce handler or use global
            post_id: post_id
        })
        .done(function(res) {
            $btn.removeClass('is-loading').css('opacity', 1);
            $row.removeClass('gatetouch-is-optimizing');
            
            if (res.success) {
                // Flash success color on the row
                $row.css('background', '#f0fdf4');
                setTimeout(() => $row.css('background', ''), 2000);
                
                // Update columns if data returned
                if (res.data && res.data.analysis) {
                    const score = res.data.analysis.score;
                    const $scoreBox = $row.find('.gatetouch-list-score');
                    $scoreBox.text(score);
                    
                    let color = '#ef4444';
                    if (score >= 80) color = '#10b981';
                    else if (score >= 50) color = '#f59e0b';
                    $scoreBox.css('--score-color', color);
                }
            } else {
                alert('GateTouch Error: ' + (res.data || 'Action failed'));
            }
        })
        .fail(function() {
            $btn.removeClass('is-loading').css('opacity', 1);
            $row.removeClass('gatetouch-is-optimizing');
            alert('Server error occurred.');
        });
    });
});
