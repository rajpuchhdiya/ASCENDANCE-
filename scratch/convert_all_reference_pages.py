import os, re

ref_dir = r'c:\XAMPP\htdocs\Ascendance\as\project\ui_kits\reference'
theme_dir = r'c:\XAMPP\htdocs\Ascendance\wp-content\themes\ascendance'

mappings = {
    'drc-sovereign-rating.html': ('page-drc-sovereign-rating.php', 'DRC Sovereign Rating Desk'),
    'hub-us-drc-partnership.html': ('page-us-drc-partnership.php', 'US-DRC Partnership Hub'),
    'lobito-file-dossier.html': ('page-lobito-corridor.php', 'Lobito Corridor Intelligence Dossier'),
    'regulatory-reform-tracker.html': ('page-regulatory-reform-tracker.php', 'DRC Regulatory Reform Tracker'),
    'sar-registry.html': ('page-sar-registry.php', 'Strategic Asset Reserve Registry'),
    'spa-glossary.html': ('page-spa-glossary.php', 'US-DRC Strategic Partnership Glossary'),
}

url_replacements = [
    (r'\.\./marketing/pricing\.html', "<?php echo esc_url( home_url( '/pricing/' ) ); ?>"),
    (r'\.\./marketing/contact\.html', "<?php echo esc_url( home_url( '/contact/' ) ); ?>"),
    (r'\.\./marketing/faq\.html', "<?php echo esc_url( home_url( '/faq/' ) ); ?>"),
    (r'\.\./marketing/index\.html#contact', "<?php echo esc_url( home_url( '/contact/' ) ); ?>"),
    (r'\.\./marketing/index\.html', "<?php echo esc_url( home_url( '/' ) ); ?>"),
    (r'\.\./portal/index\.html#s/Registers', "<?php echo esc_url( home_url( '/cami-registry/' ) ); ?>"),
    (r'\.\./portal/index\.html', "<?php echo esc_url( home_url( '/' ) ); ?>"),
    (r'cami-registry\.html', "<?php echo esc_url( home_url( '/cami-registry/' ) ); ?>"),
    (r'sar-registry\.html', "<?php echo esc_url( home_url( '/sar-registry/' ) ); ?>"),
    (r'spa-glossary\.html', "<?php echo esc_url( home_url( '/spa-glossary/' ) ); ?>"),
    (r'drc-sovereign-rating\.html', "<?php echo esc_url( home_url( '/drc-sovereign-rating/' ) ); ?>"),
    (r'regulatory-reform-tracker\.html', "<?php echo esc_url( home_url( '/regulatory-reform-tracker/' ) ); ?>"),
    (r'hub-us-drc-partnership\.html', "<?php echo esc_url( home_url( '/us-drc-partnership/' ) ); ?>"),
    (r'lobito-file-dossier\.html', "<?php echo esc_url( home_url( '/lobito-corridor/' ) ); ?>"),
]

SKIP_SELECTORS_EXACT = {
    'body', 'html', '*', 'button', 'a', 'img',
    'html, a', 'a, button', 'button, a',
    '*, *::before, *::after', '*,*::before,*::after',
}

BARE_GLOBAL_RE = re.compile(
    r'^(body|html|\*|button|a|img)(\s*,\s*(body|html|\*|button|a|img))*$'
)


def extract_block_body(text, start_brace):
    """Walk from start_brace to find matching closing brace, return (body_str, end_pos)."""
    depth = 0
    j = start_brace
    while j < len(text):
        if text[j] == '{':
            depth += 1
        elif text[j] == '}':
            depth -= 1
            if depth == 0:
                return text[start_brace:j+1], j+1
        j += 1
    return text[start_brace:], len(text)


def extract_root_blocks(css):
    """
    Extract all :root and :root[...] variable blocks.
    Returns dict: {'default': 'var: val; ...', 'dark': 'var: val; ...', 'light': 'var: val; ...'}
    """
    blocks = {'default': '', 'dark': '', 'light': ''}
    for m in re.finditer(r':root(\[([^\]]*)\])?\s*\{', css):
        attr = m.group(2) or ''
        brace_start = css.find('{', m.start())
        body, _ = extract_block_body(css, brace_start)
        inner = body[1:-1].strip()
        if 'dark' in attr:
            blocks['dark'] += '\n' + inner
        elif 'light' in attr:
            blocks['light'] += '\n' + inner
        else:
            blocks['default'] += '\n' + inner
    return blocks


def extract_body_bg(css):
    """Extract the background/color from body {} to apply to .ref-page-wrap."""
    m = re.search(r'\bbody\s*\{([^}]*)\}', css)
    if m:
        return m.group(1).strip()
    return 'background:var(--cream,#F7F4EF); color:var(--text-primary,#1A1A2E); font-family:var(--font-serif,Georgia,serif);'


def scope_css(raw_css, scope='.ref-page-wrap'):
    """
    Scope all CSS selectors under scope, dropping global resets.
    Handles @media recursively.
    """
    # Remove @import
    css = re.sub(r'@import\s+url\([^)]+\)[^;]*;?\s*\n?', '', raw_css)

    result = []
    pos = 0
    length = len(css)

    while pos < length:
        # Skip whitespace
        while pos < length and css[pos] in ' \t\r\n':
            pos += 1
        if pos >= length:
            break

        brace = css.find('{', pos)
        if brace == -1:
            break

        selector_text = css[pos:brace].strip()
        block_body, end_pos = extract_block_body(css, brace)
        pos = end_pos

        # @keyframes / @font-face — keep as-is
        if re.match(r'@(keyframes|font-face|charset)', selector_text, re.I):
            result.append(f'{selector_text} {block_body}')
            continue

        # @media — recursively scope contents
        if re.match(r'@media', selector_text, re.I):
            inner = block_body[1:-1]
            scoped_inner = scope_css(inner, scope)
            if scoped_inner.strip():
                result.append(f'{selector_text} {{\n{scoped_inner}\n}}')
            continue

        # :root blocks — skip (handled separately)
        if re.match(r':root', selector_text):
            continue

        # Pure global selectors — skip entirely
        sel_lower = selector_text.strip().lower().replace(' ', '')
        if sel_lower in [s.lower().replace(' ', '') for s in SKIP_SELECTORS_EXACT]:
            continue
        if BARE_GLOBAL_RE.match(selector_text.strip().lower()):
            continue

        # Scope multi-selectors
        parts = [s.strip() for s in selector_text.split(',')]
        scoped_parts = []
        for part in parts:
            if not part:
                continue
            if part.startswith(scope):
                scoped_parts.append(part)
            else:
                scoped_parts.append(f'{scope} {part}')

        if scoped_parts:
            result.append(f'{", ".join(scoped_parts)} {block_body}')

    return '\n'.join(result)


for src_name, (dest_name, template_title) in mappings.items():
    src_path = os.path.join(ref_dir, src_name)
    dest_path = os.path.join(theme_dir, dest_name)

    with open(src_path, 'r', encoding='utf-8') as f:
        html = f.read()

    # Extract CSS
    style_match = re.search(r'<style>(.*?)</style>', html, re.DOTALL)
    raw_css = style_match.group(1) if style_match else ''

    # Extract :root variable blocks (default, dark, light)
    root_blocks = extract_root_blocks(raw_css)

    # Extract body background/color to apply to .ref-page-wrap
    body_base = extract_body_bg(raw_css)

    # Scope all other CSS
    scoped_css = scope_css(raw_css)

    # Extract inner HTML body content (between <body> and <footer>/<script>)
    body_match = re.search(r'<body[^>]*>(.*?)(?:<footer[\s>]|<script[\s>])', html, re.DOTALL)
    if not body_match:
        body_match = re.search(r'(<div class="masthead">.*?)(?:<footer|<script)', html, re.DOTALL)
    if not body_match:
        body_match = re.search(r'(<main[^>]*>.*?)(?:<footer|<script)', html, re.DOTALL)

    body_content = body_match.group(1).strip() if body_match else ''

    # Remove platform chrome elements (theme handles these)
    body_content = re.sub(r'<nav class="plat-nav">.*?</nav>', '', body_content, flags=re.DOTALL)
    body_content = re.sub(r'<div class="plat-head">.*?</div>', '', body_content, flags=re.DOTALL)
    body_content = re.sub(r'<a class="as-lockup"[^>]*>.*?</a>', '', body_content, flags=re.DOTALL)

    # Extract script blocks
    script_matches = re.findall(r'<script>(.*?)</script>', html, re.DOTALL)
    # Skip the pre-paint theme script (first inline <script> in <head>) - theme handles this
    # Keep only functional page scripts (after <body>)
    body_scripts = re.findall(r'<script>(.*?)</script>', body_match.group(0) if body_match else '', re.DOTALL)
    script_content = '\n'.join(body_scripts).strip()

    # Apply URL replacements
    for pattern, repl in url_replacements:
        body_content = re.sub(pattern, repl, body_content)
        script_content = re.sub(pattern, repl, script_content)

    script_block = f"<script>\n{script_content}\n</script>" if script_content else ""

    # Build scoped CSS variable blocks
    default_vars = root_blocks['default']
    dark_vars = root_blocks['dark']
    light_vars = root_blocks['light']

    dark_block = ''
    if dark_vars.strip():
        dark_block = f"""
/* Dark mode overrides - scoped to inner content only */
html[data-theme="dark"] .ref-page-wrap,
:root[data-theme="dark"] .ref-page-wrap {{
{dark_vars}
  {body_base.replace('var(--cream', 'var(--cream').replace('#F7F4EF', '#17140f').replace('#FFFFFF', '#221e17')}
  transition: background .25s ease, color .25s ease;
}}"""

    light_block = ''
    if light_vars.strip():
        light_block = f"""
/* Light mode overrides - scoped to inner content only */
html[data-theme="light"] .ref-page-wrap,
:root[data-theme="light"] .ref-page-wrap {{
{light_vars}
}}"""

    php_code = f"""<?php
/**
 * Template Name: {template_title}
 *
 * @package Ascendance
 */

get_header();
?>

<div class="as-page-wrap ref-page-wrap">
<style>
/* ====== {template_title} ====== */

/* ---- Scoped CSS variables (default / light mode) ---- */
.ref-page-wrap {{
{default_vars}
  /* Page base - replaces body{{ }} which was stripped to protect theme header/footer */
  {body_base}
  min-height: 80vh;
  transition: background .25s ease, color .25s ease;
}}
{dark_block}
{light_block}

/* Hide platform chrome - theme handles navigation */
.ref-page-wrap .plat-nav,
.ref-page-wrap .plat-head,
.ref-page-wrap .plat-top,
.ref-page-wrap .plat-actions {{
  display: none !important;
}}

/* ---- All page styles - fully scoped to .ref-page-wrap ---- */
{scoped_css}
</style>

{body_content}

{script_block}
</div>

<?php
get_footer();
"""

    with open(dest_path, 'w', encoding='utf-8') as f:
        f.write(php_code)
    print(f"OK: {dest_name}")

print("\nAll 6 templates converted with proper dark mode support!")
