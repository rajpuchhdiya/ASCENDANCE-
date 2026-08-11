<?php
/**
 * Private Subscriber Notes Template Part
 *
 * @package Ascendance
 */

if ( ! is_user_logged_in() ) {
    return;
}

$user_id = get_current_user_id();
$post_id = get_the_ID();
$existing_note_data = null;
if ( class_exists( 'Ascendance\Core\Member_Dashboard' ) ) {
    $existing_note_data = \Ascendance\Core\Member_Dashboard::get_instance()->get_user_notes( $user_id, $post_id );
}
$existing_text = ( $existing_note_data && isset( $existing_note_data['note'] ) ) ? $existing_note_data['note'] : '';
$last_updated  = ( $existing_note_data && isset( $existing_note_data['updated_at'] ) ) ? human_time_diff( $existing_note_data['updated_at'] ) . ' ago' : '';
?>

<div class="as-rail-panel as-private-notes-panel" style="margin-top: 24px; background: rgba(15,30,53,0.03); border-left: 3px solid var(--accent, #BC1B1D); padding: 16px; border-radius: 2px;">
    <div class="as-rail-head" style="display: flex; justify-content: space-between; align-items: center; font-size: 11px; font-weight: 700; font-family: var(--font-mono); text-transform: uppercase; color: var(--accent, #BC1B1D); margin-bottom: 8px;">
        <span><i class="fa-solid fa-lock" style="margin-right: 4px;"></i> Private Subscriber Notes</span>
        <span id="note-save-time" style="font-size: 9px; color: var(--ink-3); text-transform: none;"><?php echo esc_html( $last_updated ); ?></span>
    </div>
    <p style="font-size: 11px; color: var(--ink-3); margin: 0 0 10px 0; line-height: 1.4;">
        Your private analysis notes for this briefing. Notes are encrypted and accessible only to your account.
    </p>
    <textarea
        id="asc-private-note-text"
        rows="4"
        maxlength="2000"
        placeholder="Type private annotations or research takeaways..."
        style="width: 100%; font-size: 12px; font-family: var(--font-sans); padding: 8px; border: 1px solid var(--hairline, #ccc); border-radius: 2px; background: #fff; color: #111; resize: vertical; box-sizing: border-box;"
    ><?php echo esc_textarea( $existing_text ); ?></textarea>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px;">
        <span id="asc-note-msg" style="font-size: 10px; font-family: var(--font-mono); color: #27AE60;"></span>
        <div style="display: flex; gap: 6px;">
            <?php if ( $existing_text ) : ?>
            <button type="button" id="asc-btn-delete-note" class="btn btn-ghost" style="font-size: 10px; padding: 4px 8px; color: #C0392B;">Delete</button>
            <?php endif; ?>
            <button type="button" id="asc-btn-save-note" class="btn btn-primary" style="font-size: 10px; padding: 4px 12px;">Save Note</button>
        </div>
    </div>
</div>

<script>
(function() {
    const postId = <?php echo (int) $post_id; ?>;
    const ajaxUrl = '<?php echo esc_url_raw( admin_url( 'admin-ajax.php' ) ); ?>';
    const nonce = '<?php echo esc_js( wp_create_nonce( 'as_save_nonce' ) ); ?>';
    const saveBtn = document.getElementById('asc-btn-save-note');
    const delBtn = document.getElementById('asc-btn-delete-note');
    const textarea = document.getElementById('asc-private-note-text');
    const msg = document.getElementById('asc-note-msg');
    const timeLabel = document.getElementById('note-save-time');

    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            saveBtn.disabled = true;
            if (msg) msg.textContent = 'Saving...';

            const fd = new FormData();
            fd.append('action', 'asc_save_user_note');
            fd.append('security', nonce);
            fd.append('post_id', postId);
            fd.append('note', textarea.value);

            fetch(ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(r => r.json())
                .then(res => {
                    saveBtn.disabled = false;
                    if (res.success) {
                        if (msg) msg.textContent = 'Note saved!';
                        if (timeLabel) timeLabel.textContent = 'Just now';
                        setTimeout(() => { if (msg) msg.textContent = ''; }, 3000);
                    } else {
                        if (msg) msg.textContent = 'Error saving note.';
                    }
                })
                .catch(() => {
                    saveBtn.disabled = false;
                    if (msg) msg.textContent = 'Connection error.';
                });
        });
    }

    if (delBtn) {
        delBtn.addEventListener('click', function() {
            if (!confirm('Are you sure you want to delete your private note for this briefing?')) return;
            delBtn.disabled = true;
            
            const fd = new FormData();
            fd.append('action', 'asc_delete_user_note');
            fd.append('security', nonce);
            fd.append('post_id', postId);

            fetch(ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        textarea.value = '';
                        if (msg) msg.textContent = 'Note deleted.';
                        if (timeLabel) timeLabel.textContent = '';
                        delBtn.style.display = 'none';
                        setTimeout(() => { if (msg) msg.textContent = ''; }, 3000);
                    }
                })
                .catch(() => { delBtn.disabled = false; });
        });
    }
})();
</script>
