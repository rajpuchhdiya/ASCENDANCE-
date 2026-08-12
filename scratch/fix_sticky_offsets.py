"""
Post-process all 6 generated reference page templates to:
1. Fix sticky elements top offset so they appear below the theme header (not behind it)
2. Fix any broken selectors caused by CSS comments inline with selectors
"""
import os, re

theme_dir = r'c:\XAMPP\htdocs\Ascendance\wp-content\themes\ascendance'

templates = [
    'page-drc-sovereign-rating.php',
    'page-us-drc-partnership.php',
    'page-lobito-corridor.php',
    'page-regulatory-reform-tracker.php',
    'page-sar-registry.php',
    'page-spa-glossary.php',
]

# Theme header height (px). Accounts for sticky theme header + nav.
# Logged-in users also have WP admin bar (32px), so we use a CSS var approach.
# We use 110px as a safe default that works for the current theme header height.
HEADER_OFFSET = '110px'

# When logged in, WP adds 32px admin bar, so actual offset needs adjustment.
# We handle this via CSS: use calc(110px + var(--wp-admin--admin-bar--height, 0px))
STICKY_TOP = 'calc(110px + var(--wp-admin--admin-bar--height, 0px))'

# For thead sticky (needs to be below both section-nav AND header)
THEAD_STICKY_TOP = 'calc(158px + var(--wp-admin--admin-bar--height, 0px))'

for tpl_name in templates:
    path = os.path.join(theme_dir, tpl_name)
    with open(path, 'r', encoding='utf-8') as f:
        content = f.read()

    original = content

    # --- Fix 1: broken scope from inline CSS comments ---
    # Pattern: ".ref-page-wrap /* some comment */\n.selector {"
    # Should become: ".ref-page-wrap .selector {"
    content = re.sub(
        r'(\.ref-page-wrap)\s*/\*[^*]*\*/\s*\n(\.[a-zA-Z])',
        r'\1 \2',
        content
    )

    # --- Fix 2: update all sticky top:0 values to be below theme header ---
    # Match: position:sticky; top:0  or  position: sticky; top: 0
    content = re.sub(
        r'(position\s*:\s*sticky\s*;?\s*top\s*:\s*)0(px)?(\s*;)',
        rf'\g<1>{STICKY_TOP}\3',
        content
    )
    # Also handle: top:0; ... position:sticky ordering
    content = re.sub(
        r'(top\s*:\s*)0(px)?(\s*;[^}}]*position\s*:\s*sticky)',
        rf'\g<1>{STICKY_TOP}\3',
        content
    )

    # --- Fix 3: thead th sticky should be below section-nav + header ---
    # The thead has top:62px in original (62 = section-nav height)
    # New value should be header_offset + section-nav height (~48px)
    content = re.sub(
        r'(thead\s+th[^}]*top\s*:\s*)\d+px',
        rf'\g<1>{THEAD_STICKY_TOP}',
        content
    )

    if content != original:
        with open(path, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"OK (patched): {tpl_name}")
    else:
        print(f"OK (no changes): {tpl_name}")

print("\nDone! Sticky offsets updated for all templates.")
