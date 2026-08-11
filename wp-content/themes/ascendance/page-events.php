<?php
/**
 * Template Name: Events
 * Page template for listing upcoming and past briefing events.
 *
 * @package Ascendance
 */

get_header();

// Query actual CPT events
$args = array(
    'post_type'      => 'event',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'ASC'
);
$events_query = new \WP_Query( $args );

// We calculate if there are actual posts to avoid showing blank
$has_real_events = $events_query->have_posts();
?>

<main id="primary" class="site-main">

    <!-- ═══ PAGE HERO ═════════════════════════════════════════ -->
    <section class="page-hero bg-navy-deep text-white py-16 md:py-24 border-b border-brand-divider-dark">
        <div class="container mx-auto px-6 md:px-8">
            <div class="page-hero-inner">
                <p class="page-hero-eyebrow text-xs font-mono uppercase tracking-widest text-brand-red mb-4 block"><?php esc_html_e( '// Intelligence Briefings & Forums', 'ascendance' ); ?></p>
                <h1 class="page-hero-title text-3xl md:text-5xl font-sans font-bold leading-tight mb-6"><?php esc_html_e( 'Strategic Events', 'ascendance' ); ?></h1>
                <p class="page-hero-desc text-base md:text-lg text-cream/80 max-w-[720px] leading-relaxed"><?php esc_html_e( 'Access closed-door roundtables, member briefings, and webinars led by our primary analyst desk. Gain testable insights on supply chains and infrastructure corridors in Central Africa.', 'ascendance' ); ?></p>
            </div>
        </div>
    </section>

    <!-- ═══ EVENTS MAIN CONTENT ═══════════════════════════════ -->
    <section class="section bg-navy py-16 md:py-24 border-b border-brand-divider-dark" id="events-main">
        <div class="container mx-auto px-6 md:px-8">
            <div class="contact-grid grid grid-cols-1 lg:grid-cols-[2fr_1fr] gap-12">
                
                <!-- Left: Events List -->
                <div class="flex flex-col gap-8">
                    <h2 class="section-title text-3xl font-sans font-bold text-white reveal"><?php esc_html_e( 'Upcoming Briefings & Sessions', 'ascendance' ); ?></h2>

                    <div class="flex flex-col gap-6">
                        <?php 
                        if ( $has_real_events ) : 
                            while ( $events_query->have_posts() ) : $events_query->the_post();
                                $post_id = get_the_ID();
                                // Retrieve metadata with ACF support or fallback
                                $event_date = get_field( 'event_date', $post_id ) ?: get_the_date( 'F j, Y // H:i T' );
                                $event_location = get_field( 'event_location', $post_id ) ?: __( 'Zoom Secured Webinar', 'ascendance' );
                                $event_type = get_field( 'event_type', $post_id ) ?: __( 'Webinar Briefing', 'ascendance' );
                                $registration_url = get_field( 'registration_url', $post_id ) ?: '#rsvp';
                                $speaker = get_field( 'event_speaker', $post_id ) ?: __( 'Ascendance Lead Analyst Desk', 'ascendance' );
                        ?>
                                <div class="terminal-card bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-6 rounded-sm border-l-[3px] border-l-brand-red shadow-sm reveal">
                                    <div class="flex justify-between items-center flex-wrap gap-2.5 mb-2">
                                        <span class="intel-card-type text-brand-red font-mono text-[10px] font-bold tracking-wider uppercase"><?php echo esc_html( $event_type ); ?></span>
                                        <span class="timeline-date font-mono text-[11px] text-brand-text-muted dark:text-cream/50"><?php echo esc_html( $event_date ); ?></span>
                                    </div>
                                    <h3 class="text-xl font-sans font-bold text-brand-text-primary dark:text-white mb-2"><?php the_title(); ?></h3>
                                    
                                    <p class="text-sm text-brand-text-muted dark:text-cream/70 leading-relaxed mb-4"><?php the_excerpt(); ?></p>
                                    
                                    <div class="flex justify-between items-center flex-wrap gap-4 border-t border-brand-divider-light dark:border-brand-divider-dark/10 pt-3 text-xs font-sans">
                                        <span class="text-brand-text-muted dark:text-cream/60"><i class="fa-solid fa-user-tie text-brand-red mr-1.5"></i><?php printf( __( 'Presenter: %s', 'ascendance' ), esc_html( $speaker ) ); ?></span>
                                        <span class="text-brand-text-muted dark:text-cream/60"><i class="fa-solid fa-location-dot text-brand-red mr-1.5"></i><?php echo esc_html( $event_location ); ?></span>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <a href="<?php echo esc_url( $registration_url ); ?>" class="btn btn-secondary rsvp-trigger-btn border border-brand-divider-light dark:border-brand-divider-dark text-brand-text-primary dark:text-cream hover:bg-brand-divider-light dark:hover:bg-brand-divider-dark py-2 px-4 text-xs font-bold font-sans" data-event="<?php echo esc_attr( get_the_title() ); ?>"><?php esc_html_e( 'Request Invitation', 'ascendance' ); ?></a>
                                    </div>
                                </div>
                        <?php 
                            endwhile; 
                            wp_reset_postdata(); 
                        else : 
                            // Render Fallback Mock Events if none published
                        ?>
                            <!-- Mock Event 1 -->
                            <div class="terminal-card bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-6 rounded-sm border-l-[3px] border-l-brand-red shadow-sm reveal">
                                <div class="flex justify-between items-center flex-wrap gap-2.5 mb-2">
                                    <span class="intel-card-type text-brand-red font-mono text-[10px] font-bold tracking-wider uppercase"><?php esc_html_e( 'Closed Roundtable', 'ascendance' ); ?></span>
                                    <span class="timeline-date font-mono text-[11px] text-brand-text-muted dark:text-cream/50"><?php esc_html_e( 'JULY 09, 2026 // 14:00 CET', 'ascendance' ); ?></span>
                                </div>
                                <h3 class="text-xl font-sans font-bold text-brand-text-primary dark:text-white mb-2"><?php esc_html_e( 'Sakania-Lobito Transit Corridor Roundtable', 'ascendance' ); ?></h3>
                                <p class="text-sm text-brand-text-muted dark:text-cream/70 leading-relaxed mb-4"><?php esc_html_e( 'An analyst-led briefing detailing tariff structures, custom delays, and security risk premiums along the newly awarded rail concession in the DRC copperbelt. Invite-only access for subscribers.', 'ascendance' ); ?></p>
                                
                                <div class="flex justify-between items-center flex-wrap gap-4 border-t border-brand-divider-light dark:border-brand-divider-dark/10 pt-3 text-xs font-sans">
                                    <span class="text-brand-text-muted dark:text-cream/60"><i class="fa-solid fa-user-tie text-brand-red mr-1.5"></i><?php esc_html_e( 'Presenter: Dr. Amara Osei', 'ascendance' ); ?></span>
                                    <span class="text-brand-text-muted dark:text-cream/60"><i class="fa-solid fa-location-dot text-brand-red mr-1.5"></i><?php esc_html_e( 'Zoom Secure Webinar', 'ascendance' ); ?></span>
                                </div>
                                
                                <div class="mt-4">
                                    <a href="#rsvp" class="btn btn-secondary rsvp-trigger-btn border border-brand-divider-light dark:border-brand-divider-dark text-brand-text-primary dark:text-cream hover:bg-brand-divider-light dark:hover:bg-brand-divider-dark py-2 px-4 text-xs font-bold font-sans" data-event="Sakania-Lobito Roundtable"><?php esc_html_e( 'Request Invitation', 'ascendance' ); ?></a>
                                </div>
                            </div>

                            <!-- Mock Event 2 -->
                            <div class="terminal-card bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-6 rounded-sm border-l-[3px] border-l-brand-red shadow-sm reveal">
                                <div class="flex justify-between items-center flex-wrap gap-2.5 mb-2">
                                    <span class="intel-card-type text-brand-red font-mono text-[10px] font-bold tracking-wider uppercase"><?php esc_html_e( 'Strategic Briefing', 'ascendance' ); ?></span>
                                    <span class="timeline-date font-mono text-[11px] text-brand-text-muted dark:text-cream/50"><?php esc_html_e( 'AUGUST 20, 2026 // 09:30 EST', 'ascendance' ); ?></span>
                                </div>
                                <h3 class="text-xl font-sans font-bold text-brand-text-primary dark:text-white mb-2"><?php esc_html_e( 'Critical Minerals Supply Chain Summit 2026', 'ascendance' ); ?></h3>
                                <p class="text-sm text-brand-text-muted dark:text-cream/70 leading-relaxed mb-4"><?php esc_html_e( 'Annual briefing outlining Western capital deployments, sovereign credit guarantees, and concessions inside the US-DRC strategic partnership. Gated for Professional and Enterprise tiers.', 'ascendance' ); ?></p>
                                
                                <div class="flex justify-between items-center flex-wrap gap-4 border-t border-brand-divider-light dark:border-brand-divider-dark/10 pt-3 text-xs font-sans">
                                    <span class="text-brand-text-muted dark:text-cream/60"><i class="fa-solid fa-user-tie text-brand-red mr-1.5"></i><?php esc_html_e( 'Presenter: Marcus Delacroix', 'ascendance' ); ?></span>
                                    <span class="text-brand-text-muted dark:text-cream/60"><i class="fa-solid fa-location-dot text-brand-red mr-1.5"></i><?php esc_html_e( 'Paris / Hybrid Link', 'ascendance' ); ?></span>
                                </div>
                                
                                <div class="mt-4">
                                    <a href="#rsvp" class="btn btn-secondary rsvp-trigger-btn border border-brand-divider-light dark:border-brand-divider-dark text-brand-text-primary dark:text-cream hover:bg-brand-divider-light dark:hover:bg-brand-divider-dark py-2 px-4 text-xs font-bold font-sans" data-event="Critical Minerals Supply Chain Summit"><?php esc_html_e( 'Request Invitation', 'ascendance' ); ?></a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right: RSVP / Inquiries Sidebar -->
                <aside class="contact-sidebar flex flex-col gap-6">
                    <div class="contact-form-wrapper bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-6 rounded-sm shadow-sm reveal" id="rsvp">
                        <h2 class="text-lg font-sans font-bold text-brand-text-primary dark:text-white mb-4"><?php esc_html_e( 'Request Event RSVP', 'ascendance' ); ?></h2>
                        
                        <form class="contact-form-native flex flex-col gap-4" id="events-rsvp-form">
                            <div class="form-group flex flex-col gap-1.5">
                                <label for="rsvp-name" class="font-sans text-xs font-medium text-brand-text-muted dark:text-cream/60"><?php esc_html_e( 'Full Name', 'ascendance' ); ?></label>
                                <input type="text" id="rsvp-name" name="name" class="w-full px-3 py-2 bg-white dark:bg-navy-deep border border-brand-divider-light dark:border-brand-divider-dark rounded-sm text-brand-text-primary dark:text-cream font-serif text-sm outline-none transition-all duration-150 focus:border-brand-red" placeholder="<?php esc_attr_e( 'Your name', 'ascendance' ); ?>" required>
                            </div>
                            
                            <div class="form-group flex flex-col gap-1.5">
                                <label for="rsvp-email" class="font-sans text-xs font-medium text-brand-text-muted dark:text-cream/60"><?php esc_html_e( 'Email Address', 'ascendance' ); ?></label>
                                <input type="email" id="rsvp-email" name="email" class="w-full px-3 py-2 bg-white dark:bg-navy-deep border border-brand-divider-light dark:border-brand-divider-dark rounded-sm text-brand-text-primary dark:text-cream font-serif text-sm outline-none transition-all duration-150 focus:border-brand-red" placeholder="<?php esc_attr_e( 'you@organisation.com', 'ascendance' ); ?>" required>
                            </div>

                            <div class="form-group flex flex-col gap-1.5">
                                <label for="rsvp-org" class="font-sans text-xs font-medium text-brand-text-muted dark:text-cream/60"><?php esc_html_e( 'Organization / Firm', 'ascendance' ); ?></label>
                                <input type="text" id="rsvp-org" name="organisation" class="w-full px-3 py-2 bg-white dark:bg-navy-deep border border-brand-divider-light dark:border-brand-divider-dark rounded-sm text-brand-text-primary dark:text-cream font-serif text-sm outline-none transition-all duration-150 focus:border-brand-red" placeholder="<?php esc_attr_e( 'Company or entity', 'ascendance' ); ?>" required>
                            </div>

                            <div class="form-group flex flex-col gap-1.5">
                                <label for="rsvp-event-select" class="font-sans text-xs font-medium text-brand-text-muted dark:text-cream/60"><?php esc_html_e( 'Select Briefing Session', 'ascendance' ); ?></label>
                                <select id="rsvp-event-select" name="event_session" class="w-full px-3 py-2 bg-white dark:bg-navy-deep border border-brand-divider-light dark:border-brand-divider-dark rounded-sm text-brand-text-primary dark:text-cream font-serif text-sm outline-none transition-all duration-150 focus:border-brand-red">
                                    <?php 
                                    if ( $has_real_events ) : 
                                        $events_query->rewind_posts();
                                        while ( $events_query->have_posts() ) : $events_query->the_post();
                                            ?>
                                            <option value="<?php echo esc_attr( get_the_title() ); ?>"><?php the_title(); ?></option>
                                            <?php
                                        endwhile;
                                        wp_reset_postdata();
                                    else :
                                        ?>
                                        <option value="Sakania-Lobito Transit Corridor Roundtable"><?php esc_html_e( 'Sakania-Lobito Transit Corridor Roundtable', 'ascendance' ); ?></option>
                                        <option value="Critical Minerals Supply Chain Summit 2026"><?php esc_html_e( 'Critical Minerals Supply Chain Summit 2026', 'ascendance' ); ?></option>
                                        <?php
                                    endif;
                                    ?>
                                    <option value="General Inquiry"><?php esc_html_e( 'Request Custom Briefing / Other Session', 'ascendance' ); ?></option>
                                </select>
                            </div>

                            <div class="form-group flex flex-col gap-1.5">
                                <label for="rsvp-note" class="font-sans text-xs font-medium text-brand-text-muted dark:text-cream/60"><?php esc_html_e( 'Access Inquiries & Questions', 'ascendance' ); ?></label>
                                <textarea id="rsvp-note" name="note" rows="4" class="w-full px-3 py-2 bg-white dark:bg-navy-deep border border-brand-divider-light dark:border-brand-divider-dark rounded-sm text-brand-text-primary dark:text-cream font-serif text-sm outline-none transition-all duration-150 focus:border-brand-red" placeholder="<?php esc_attr_e( 'Ask a question or request credentials check...', 'ascendance' ); ?>"></textarea>
                            </div>

                            <div id="events-rsvp-msg" style="display:none; margin-bottom:15px; padding:12px; border-radius:4px; font-weight:500; font-size:14px; font-family:var(--font-ui);"></div>
                            <button type="submit" class="btn btn-primary w-full flex justify-center items-center py-2.5 gap-2" id="rsvp-submit-btn">
                                <?php esc_html_e( 'Submit RSVP Inquiry', 'ascendance' ); ?>
                                <i class="fa-solid fa-ticket text-xs"></i>
							</button>
                        </form>
                    </div>

                    <!-- Security and Access Card -->
                    <div class="contact-info-card bg-white dark:bg-navy-mid border border-brand-divider-light dark:border-brand-divider-dark p-6 rounded-sm reveal reveal-delay-1">
                        <h3 class="text-xs uppercase tracking-widest text-brand-red font-sans font-bold mb-3"><?php esc_html_e( 'Credentials & Access Protocol', 'ascendance' ); ?></h3>
                        <p class="font-serif text-xs text-brand-text-muted dark:text-cream/70 leading-relaxed m-0">
                            <?php esc_html_e( 'Roundtables and briefings are encrypted. Professional and Enterprise subscribers receive secure calendar credentials automatically. Free or Guest tier accounts require manual credential auditing before invitation approval.', 'ascendance' ); ?>
                        </p>
                    </div>
                </aside>

			</div>
		</div>
    </section>

    <!-- Past Events Ledger -->
    <section class="section bg-navy-mid py-12 border-b border-brand-divider-dark">
        <div class="container mx-auto px-6 md:px-8">
            <h2 class="section-title text-2xl font-sans font-bold text-white mb-6 reveal"><?php esc_html_e( 'Past Briefings Registry', 'ascendance' ); ?></h2>
            
            <div class="flex flex-col gap-2.5">
                <div class="flex flex-col md:flex-row justify-between items-center bg-white dark:bg-navy border border-brand-divider-light dark:border-brand-divider-dark p-4 md:px-6 md:py-4 rounded-sm text-sm font-sans gap-4 shadow-sm">
                    <div class="flex items-center gap-4 flex-wrap">
                        <span class="text-brand-red font-mono text-[10px] font-bold uppercase tracking-wider"><?php esc_html_e( 'PAST EVENT', 'ascendance' ); ?></span>
                        <strong class="text-brand-text-primary dark:text-cream"><?php esc_html_e( 'US-DRC Strategic Partnership: Post-Election Analysis', 'ascendance' ); ?></strong>
                    </div>
                    <div class="text-brand-text-muted dark:text-cream/50 text-[11px] flex items-center gap-4">
                        <span><?php esc_html_e( 'Held: May 14, 2026', 'ascendance' ); ?></span>
                        <a href="<?php echo esc_url( home_url( '/intelligence/' ) ); ?>" class="text-brand-red hover:text-brand-red-light font-bold transition-colors duration-150"><?php esc_html_e( 'Read Digest →', 'ascendance' ); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>

<script>
jQuery(document).ready(function($) {
    // When clicking "Request Invitation", scroll to form and select correct option
    $('.rsvp-trigger-btn').click(function(e) {
        e.preventDefault();
        const eventName = $(this).data('event');
        
        // Match selection value in the dropdown
        if (eventName) {
            if ($('#rsvp-event-select option[value="' + eventName + '"]').length > 0) {
                $('#rsvp-event-select').val(eventName);
            } else {
                // Check for loose match if using fallback mock events
                if (eventName.indexOf('Roundtable') !== -1) {
                    $('#rsvp-event-select').val('Sakania-Lobito Transit Corridor Roundtable');
                } else if (eventName.indexOf('Summit') !== -1) {
                    $('#rsvp-event-select').val('Critical Minerals Supply Chain Summit 2026');
                } else {
                    $('#rsvp-event-select').val('General Inquiry');
                }
            }
        }

        // Scroll to RSVP form smoothly
        $('html, body').animate({
            scrollTop: $('#rsvp').offset().top - 100
        }, 600);
    });

    // Form submission simulation
    $('#events-rsvp-form').submit(function(e) {
        e.preventDefault();
        
        const btn = $('#rsvp-submit-btn');
        const msgDiv = $('#events-rsvp-msg');
        const originalHtml = btn.html();
        
        var fd = new FormData(this);
        fd.append('action', 'as_submit_rsvp');
        
        msgDiv.hide();
        btn.attr('disabled', true).html('<i class="fa-solid fa-spinner fa-spin mr-2"></i> Submitting...');
        
        fetch('<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>', {
            method: 'POST',
            body: fd
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            btn.attr('disabled', false).html(originalHtml);
            if (data.success) {
                msgDiv.css({
                    'display': 'block',
                    'color': '#155724',
                    'background-color': '#d4edda',
                    'border': '1px solid #c3e6cb'
                }).text('RSVP Submission Received! Our events desk will verify your credentials and send a secure briefing link to your email shortly.');
                $('#events-rsvp-form')[0].reset();
            } else {
                msgDiv.css({
                    'display': 'block',
                    'color': '#721c24',
                    'background-color': '#f8d7da',
                    'border': '1px solid #f5c6cb'
                }).text('Error: ' + (data.data || 'Failed to submit.'));
            }
        })
        .catch(function(err) {
            btn.attr('disabled', false).html(originalHtml);
            msgDiv.css({
                'display': 'block',
                'color': '#721c24',
                'background-color': '#f8d7da',
                'border': '1px solid #f5c6cb'
            }).text('An error occurred. Please try again.');
        });
    });
});
</script>

<?php 
get_footer(); 
?>
