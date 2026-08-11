<?php
/**
 * Save / Bookmark Button Template Part
 *
 * @package Ascendance
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_id = get_query_var( 'save_button_post_id', get_the_ID() );
if ( ! $post_id ) {
	return;
}

$is_logged_in = is_user_logged_in();
$user_id      = get_current_user_id();

$is_saved = false;
if ( $is_logged_in && $user_id ) {
	$saved_posts = array_filter( array_map( 'intval', (array) get_user_meta( $user_id, 'as_saved_posts', true ) ) );
	$is_saved    = in_array( (int) $post_id, $saved_posts, true );
}

$btn_class = $is_saved ? 'asc-save-btn saved' : 'asc-save-btn';
$icon_class = $is_saved ? 'fa-solid fa-bookmark' : 'fa-regular fa-bookmark';
$label      = $is_saved ? __( 'Saved', 'ascendance' ) : __( 'Save for later', 'ascendance' );
?>

<button 
	type="button" 
	class="<?php echo esc_attr( $btn_class ); ?>" 
	data-post-id="<?php echo esc_attr( $post_id ); ?>"
	data-logged-in="<?php echo $is_logged_in ? '1' : '0'; ?>"
	aria-label="<?php echo esc_attr( $label ); ?>"
	title="<?php echo esc_attr( $label ); ?>"
	style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; font-size: 11px; font-family: var(--font-mono, monospace); text-transform: uppercase; letter-spacing: 0.05em; border-radius: 2px; border: 1px solid var(--hairline, rgba(255,255,255,0.15)); background: rgba(255,255,255,0.03); color: var(--color-white, #fff); cursor: pointer; transition: all 0.2s ease;">
	<i class="<?php echo esc_attr( $icon_class ); ?> text-brand-red" style="font-size: 12px;"></i>
	<span class="save-label"><?php echo esc_html( $label ); ?></span>
</button>

<?php if ( ! wp_script_is( 'asc-save-button-script', 'enqueued' ) ) : ?>
<script id="asc-save-button-script">
document.addEventListener('click', function(e) {
	const btn = e.target.closest('.asc-save-btn');
	if (!btn) return;
	e.preventDefault();

	const isLoggedIn = btn.dataset.loggedIn === '1';
	if (!isLoggedIn) {
		alert('Please sign in to save articles to your reading list.');
		return;
	}

	const postId = btn.dataset.postId;
	if (!postId) return;

	const label = btn.querySelector('.save-label');
	const icon = btn.querySelector('i');
	btn.disabled = true;

	const ajaxUrl = '<?php echo esc_url_raw( admin_url( 'admin-ajax.php' ) ); ?>';
	const nonce = '<?php echo esc_js( wp_create_nonce( 'as_save_nonce' ) ); ?>';

	const fd = new FormData();
	fd.append('action', 'as_toggle_saved');
	fd.append('nonce', nonce);
	fd.append('post_id', postId);

	fetch(ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
		.then(res => res.json())
		.then(data => {
			btn.disabled = false;
			if (data.success) {
				if (data.data.action === 'saved') {
					btn.classList.add('saved');
					if (icon) icon.className = 'fa-solid fa-bookmark text-brand-red';
					if (label) label.textContent = 'Saved';
				} else {
					btn.classList.remove('saved');
					if (icon) icon.className = 'fa-regular fa-bookmark text-brand-red';
					if (label) label.textContent = 'Save for later';
				}
			} else {
				alert(data.data || 'Action failed.');
			}
		})
		.catch(err => {
			console.error(err);
			btn.disabled = false;
		});
});
</script>
<?php endif; ?>
