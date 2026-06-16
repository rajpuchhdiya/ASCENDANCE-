<?php
/**
 * The template for displaying the custom home/front page
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Ascendance
 */

get_header();
?>

<main id="primary" class="site-main">

	<!-- Hero Section -->
	<section class="hero-section">
		<div class="container">
			<span class="hero-tagline reveal active"><?php esc_html_e( 'Introducing Ascendance', 'ascendance' ); ?></span>
			<h1 class="hero-title reveal active">
				Ascend to the Next Tier<br>of <span>Digital Design</span>
			</h1>
			<p class="hero-desc reveal active">
				<?php esc_html_e( 'A premium WordPress experience crafted for creators, agencies, and builders who refuse to compromise on aesthetics and performance. Engineered with vanilla CSS and responsive structures.', 'ascendance' ); ?>
			</p>
			<div class="hero-ctas reveal active">
				<a href="#features" class="btn btn-primary"><?php esc_html_e( 'Explore Features', 'ascendance' ); ?></a>
				<a href="#about" class="btn btn-secondary"><?php esc_html_e( 'About Designer', 'ascendance' ); ?></a>
			</div>
		</div>
	</section>

	<!-- Features Section -->
	<section id="features" class="features-section">
		<div class="container">
			<div class="section-header reveal">
				<span class="section-subtitle"><?php esc_html_e( 'Exclusive Capabilities', 'ascendance' ); ?></span>
				<h2 class="section-title"><?php esc_html_e( 'Engineered for Visual Perfection', 'ascendance' ); ?></h2>
			</div>

			<div class="grid-features">
				<div class="feature-card reveal">
					<div class="feature-icon">
						<i class="fa-solid fa-bolt"></i>
					</div>
					<h3><?php esc_html_e( 'Ultra Performance', 'ascendance' ); ?></h3>
					<p><?php esc_html_e( 'Slick, lightning-fast rendering built on optimized vanilla structure. No heavy builders or code bloat.', 'ascendance' ); ?></p>
				</div>

				<div class="feature-card reveal">
					<div class="feature-icon">
						<i class="fa-solid fa-palette"></i>
					</div>
					<h3><?php esc_html_e( 'Premium Aesthetics', 'ascendance' ); ?></h3>
					<p><?php esc_html_e( 'Sophisticated dark mode, vibrant neon accents, glowing borders, and flawless glassmorphic cards.', 'ascendance' ); ?></p>
				</div>

				<div class="feature-card reveal">
					<div class="feature-icon">
						<i class="fa-solid fa-mobile-screen"></i>
					</div>
					<h3><?php esc_html_e( 'Fully Responsive', 'ascendance' ); ?></h3>
					<p><?php esc_html_e( 'Flawless layouts engineered to look outstanding on mobile, tablet, and desktop viewports.', 'ascendance' ); ?></p>
				</div>

				<div class="feature-card reveal">
					<div class="feature-icon">
						<i class="fa-solid fa-code"></i>
					</div>
					<h3><?php esc_html_e( 'Clean Architecture', 'ascendance' ); ?></h3>
					<p><?php esc_html_e( 'Strictly adhering to WordPress standards with clean PHP code, dynamic content loops, and semantic HTML.', 'ascendance' ); ?></p>
				</div>
			</div>
		</div>
	</section>

	<!-- About / Showcase Section -->
	<section id="about" class="about-section">
		<div class="container">
			<div class="about-grid">
				<div class="about-content reveal">
					<span class="section-subtitle"><?php esc_html_e( 'Ascendance Vision', 'ascendance' ); ?></span>
					<h2 class="section-title" style="margin-bottom: 1.5rem; text-align: left;"><?php esc_html_e( 'Crafted With Absolute Precision', 'ascendance' ); ?></h2>
					<p style="color: var(--text-secondary); margin-bottom: 1.2rem;">
						<?php esc_html_e( 'This theme represents the peak of custom WordPress development. We bypass complex drag-and-drop builders to deliver direct, blazing-fast front-end layouts written with clean code.', 'ascendance' ); ?>
					</p>
					<p style="color: var(--text-secondary);">
						<?php esc_html_e( 'Designed by Raj, Ascendance utilizes glassmorphic backdrops, smooth gradient overflows, and scroll-reveal triggers to keep users deeply engaged.', 'ascendance' ); ?>
					</p>

					<div class="stats-grid">
						<div class="stat-item">
							<div class="stat-number">99%</div>
							<div class="stat-label"><?php esc_html_e( 'Core Web Vitals', 'ascendance' ); ?></div>
						</div>
						<div class="stat-item">
							<div class="stat-number">0.2s</div>
							<div class="stat-label"><?php esc_html_e( 'Initial Load Time', 'ascendance' ); ?></div>
						</div>
					</div>
				</div>

				<div class="about-image reveal">
					<div class="about-img-wrapper">
						<?php
						$showcase_img = get_template_directory_uri() . '/assets/images/showcase.png';
						?>
						<img src="<?php echo esc_url( $showcase_img ); ?>" alt="Ascendance Showcase Mockup" onerror="this.src='https://placehold.co/600x450/080710/ffffff?text=Ascendance+Showcase';">
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Blog / Recent Posts Section -->
	<section id="blog" class="posts-section">
		<div class="container">
			<div class="section-header reveal">
				<span class="section-subtitle"><?php esc_html_e( 'Our Journal', 'ascendance' ); ?></span>
				<h2 class="section-title"><?php esc_html_e( 'Latest Insights & Design News', 'ascendance' ); ?></h2>
			</div>

			<div class="posts-grid">
				<?php
				$latest_posts = new WP_Query(
					array(
						'posts_per_page'      => 3,
						'post_status'         => 'publish',
						'ignore_sticky_posts' => true,
					)
				);

				if ( $latest_posts->have_posts() ) :
					while ( $latest_posts->have_posts() ) :
						$latest_posts->the_post();
						?>
						<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card reveal' ); ?>>
							<div class="post-thumb">
								<?php if ( has_post_thumbnail() ) : ?>
									<?php the_post_thumbnail( 'medium_large' ); ?>
								<?php else : ?>
									<img src="https://placehold.co/600x400/080710/ffffff?text=<?php echo urlencode( get_the_title() ); ?>" alt="<?php the_title_attribute(); ?>">
								<?php endif; ?>
								
								<span class="post-category">
									<?php
									$categories = get_the_category();
									if ( ! empty( $categories ) ) {
										echo esc_html( $categories[0]->name );
									} else {
										echo esc_html__( 'Article', 'ascendance' );
									}
									?>
								</span>
							</div>

							<div class="post-content">
								<div class="post-meta">
									<span><i class="fa-regular fa-calendar"></i> <?php echo get_the_date(); ?></span>
									<span><i class="fa-regular fa-user"></i> <?php the_author(); ?></span>
								</div>
								<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<div class="post-excerpt">
									<?php the_excerpt(); ?>
								</div>
								<a href="<?php the_permalink(); ?>" class="read-more">
									<?php esc_html_e( 'Read More', 'ascendance' ); ?> <i class="fa-solid fa-arrow-right"></i>
								</a>
							</div>
						</article>
						<?php
					endwhile;
					wp_reset_postdata();
				else :
					// Show beautiful static placeholder cards if there are no posts in the database yet
					for ( $i = 1; $i <= 3; $i ++ ) :
						?>
						<article class="post-card reveal">
							<div class="post-thumb">
								<img src="https://placehold.co/600x400/080710/ffffff?text=Sample+Article+<?php echo $i; ?>" alt="Placeholder Post">
								<span class="post-category"><?php echo $i === 1 ? 'Design' : ( $i === 2 ? 'Coding' : 'Aesthetics' ); ?></span>
							</div>
							<div class="post-content">
								<div class="post-meta">
									<span><i class="fa-regular fa-calendar"></i> <?php echo date( 'M d, Y' ); ?></span>
									<span><i class="fa-regular fa-user"></i> Raj</span>
								</div>
								<h3><a href="#"><?php echo $i === 1 ? 'Mastering Glassmorphism in Modern Websites' : ( $i === 2 ? 'Building Lightning Fast Themes with Vanilla CSS' : 'Why Visual Aesthetics Drive User Retention' ); ?></a></h3>
								<div class="post-excerpt">
									<p><?php echo $i === 1 ? 'Discover how to implement sleek CSS backdrop filters and borders to create gorgeous interfaces.' : ( $i === 2 ? 'Learn our key strategies for writing performant PHP templates and optimizing style packages.' : 'Explore the psychological connection between color harmony, layouts, and visitor trust.' ); ?></p>
								</div>
								<a href="#" class="read-more"><?php esc_html_e( 'Read More', 'ascendance' ); ?> <i class="fa-solid fa-arrow-right"></i></a>
							</div>
						</article>
						<?php
					endfor;
				endif;
				?>
			</div>
		</div>
	</section>

	<!-- Call To Action Section / Newsletter -->
	<section id="cta" class="cta-section">
		<div class="container">
			<div class="cta-box reveal">
				<h2><?php esc_html_e( 'Join the Ascendance Movement', 'ascendance' ); ?></h2>
				<p><?php esc_html_e( 'Get notified when new templates, design systems, and components are released. Subscribe to Raj\'s exclusive dev logs.', 'ascendance' ); ?></p>
				<form class="cta-form" onsubmit="event.preventDefault(); alert('Subscribed successfully!');">
					<input type="email" placeholder="<?php esc_attr_e( 'Enter your email address', 'ascendance' ); ?>" required>
					<button type="submit"><?php esc_html_e( 'Subscribe', 'ascendance' ); ?></button>
				</form>
			</div>
		</div>
	</section>

</main><!-- #primary -->

<?php
get_footer();
