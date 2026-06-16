<?php
/**
 * The template for displaying comments
 *
 * This is the template that displays the area of the page that contains both the current comments
 * and the comment form.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Ascendance
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="comments-area">

	<?php if ( have_comments() ) : ?>
		<h2 class="comments-title">
			<?php
			$ascendance_comment_count = get_comments_number();
			if ( '1' === $ascendance_comment_count ) {
				printf(
					/* translators: 1: title. */
					esc_html__( 'One thought on &ldquo;%1$s&rdquo;', 'ascendance' ),
					'<span>' . wp_kses_post( get_the_title() ) . '</span>'
				);
			} else {
				printf(
					/* translators: 1: comment count number, 2: title. */
					esc_html( _nx( '%1$s thought on &ldquo;%2$s&rdquo;', '%1$s thoughts on &ldquo;%2$s&rdquo;', $ascendance_comment_count, 'comments title', 'ascendance' ) ),
					number_format_i18n( $ascendance_comment_count ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'<span>' . wp_kses_post( get_the_title() ) . '</span>'
				);
			}
			?>
		</h2>

		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'      => 'ol',
					'short_ping' => true,
					'callback'   => 'ascendance_comment_callback',
				)
			);
			?>
		</ol>

		<?php
		the_comments_navigation(
			array(
				'prev_text' => esc_html__( 'Older Comments', 'ascendance' ),
				'next_text' => esc_html__( 'Newer Comments', 'ascendance' ),
			)
		);

		// If comments are closed and there are comments, let's leave a little note, shall we?
		if ( ! comments_open() ) :
			?>
			<p class="no-comments" style="color: var(--text-muted); font-style: italic; text-align: center; margin-top: 2rem;"><?php esc_html_e( 'Comments are closed.', 'ascendance' ); ?></p>
			<?php
		endif;

	endif; // Check for have_comments().

	// Customize comment form arguments to match our style classes
	$comment_form_args = array(
		'class_form'         => 'comment-form',
		'title_reply'        => esc_html__( 'Leave a Comment', 'ascendance' ),
		'title_reply_to'     => esc_html__( 'Leave a Reply to %s', 'ascendance' ),
		'cancel_reply_link'  => esc_html__( 'Cancel Reply', 'ascendance' ),
		'label_submit'       => esc_html__( 'Post Comment', 'ascendance' ),
		'submit_button'      => '<input name="%1$s" type="submit" id="%2$s" class="%3$s" value="%4$s" />',
		'submit_field'       => '<p class="form-submit">%1$s %2$s</p>',
		'format'             => 'html5',
		'comment_field'      => '<p class="comment-form-comment"><label for="comment">' . esc_html__( 'Comment', 'ascendance' ) . ' <span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="8" maxlength="65525" required="required" placeholder="' . esc_attr__( 'Write your comment here...', 'ascendance' ) . '"></textarea></p>',
		'fields'             => array(
			'author' => '<p class="comment-form-author"><label for="author">' . esc_html__( 'Name', 'ascendance' ) . ' <span class="required">*</span></label><input id="author" name="author" type="text" value="" size="30" maxlength="245" required="required" placeholder="' . esc_attr__( 'Your name', 'ascendance' ) . '" /></p>',
			'email'  => '<p class="comment-form-email"><label for="email">' . esc_html__( 'Email', 'ascendance' ) . ' <span class="required">*</span></label><input id="email" name="email" type="email" value="" size="30" maxlength="100" aria-describedby="email-notes" required="required" placeholder="' . esc_attr__( 'Your email', 'ascendance' ) . '" /></p>',
		),
	);

	comment_form( $comment_form_args );
	?>

</div><!-- #comments -->

<?php
/**
 * Callback function to render comments nicely in our design format
 */
if ( ! function_exists( 'ascendance_comment_callback' ) ) :
	function ascendance_comment_callback( $comment, $args, $depth ) {
		$GLOBALS['comment'] = $comment;
		?>
		<li <?php comment_class( empty( $args['has_children'] ) ? '' : 'parent' ); ?> id="comment-<?php comment_ID(); ?>">
			<article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
				<footer class="comment-meta">
					<div class="comment-author vcard" style="display: flex; align-items: center; gap: 12px;">
						<?php
						if ( 0 != $args['avatar_size'] ) {
							echo get_avatar( $comment, $args['avatar_size'], '', '', array( 'class' => 'avatar' ) );
						}
						?>
						<span class="fn comment-author-name"><?php comment_author_link(); ?></span>
					</div>

					<div class="comment-metadata">
						<a href="<?php echo esc_url( htmlspecialchars( get_comment_link( $comment->comment_ID ) ) ); ?>">
							<time datetime="<?php comment_time( 'c' ); ?>">
								<?php
								/* translators: 1: date, 2: time */
								printf( esc_html__( '%1$s at %2$s', 'ascendance' ), get_comment_date(), get_comment_time() );
								?>
							</time>
						</a>
						<?php edit_comment_link( esc_html__( '(Edit)', 'ascendance' ), '&nbsp;&nbsp;', '' ); ?>
					</div>
				</footer>

				<?php if ( '0' == $comment->comment_approved ) : ?>
					<p class="comment-awaiting-moderation" style="font-style: italic; color: var(--accent-purple); font-size: 0.85rem; margin-bottom: 0.5rem;">
						<?php esc_html_e( 'Your comment is awaiting moderation.', 'ascendance' ); ?>
					</p>
				<?php endif; ?>

				<div class="comment-content">
					<?php comment_text(); ?>
				</div>

				<div class="reply" style="margin-top: 1rem; text-align: right;">
					<?php
					comment_reply_link(
						array_merge(
							$args,
							array(
								'add_below' => 'div-comment',
								'depth'     => $depth,
								'max_depth' => $args['max_depth'],
								'reply_text' => sprintf( '<i class="fa-solid fa-reply"></i> %s', esc_html__( 'Reply', 'ascendance' ) ),
							)
						)
					);
					?>
				</div>
			</article>
		<?php
	}
endif;
?>
