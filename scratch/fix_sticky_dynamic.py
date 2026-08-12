"""
Fix sticky bar offset to sit below the actual theme header height (measured dynamically via JS).
Also lower z-index so sticky bars go behind theme header on scroll-up.
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

# JS snippet: measures theme header + admin bar height, sets --page-sticky-top CSS var
STICKY_JS = """
<script>
(function () {
  function setHeaderOffset() {
    var header = document.querySelector('.site-header') || document.getElementById('masthead') || document.querySelector('header');
    var adminBar = document.getElementById('wpadminbar');
    var total = 0;
    if (adminBar) total += adminBar.getBoundingClientRect().height;
    if (header)   total += header.getBoundingClientRect().height;
    if (total < 60) total = 110; // fallback
    document.documentElement.style.setProperty('--page-sticky-top', total + 'px');
  }
  setHeaderOffset();
  window.addEventListener('resize', setHeaderOffset);
})();
</script>
"""

for tpl_name in templates:
    path = os.path.join(theme_dir, tpl_name)
    with open(path, 'r', encoding='utf-8') as f:
        content = f.read()

    original = content

    # --- 1: Replace any calc(110px...) or top:0 sticky values with CSS var ---
    # Already patched: replace calc(110px + ...) with var(--page-sticky-top, 110px)
    content = re.sub(
        r'top\s*:\s*calc\(\d+px\s*\+\s*var\(--wp-admin--admin-bar--height,[^)]*\)\)',
        'top: var(--page-sticky-top, 120px)',
        content
    )
    # Handle any remaining top:0 in sticky context (shouldn't exist but just in case)
    content = re.sub(
        r'(position\s*:\s*sticky[^}]*top\s*:\s*)0px?',
        r'\g<1>var(--page-sticky-top, 120px)',
        content
    )

    # --- 2: Lower z-index of page sticky bars so theme header stays on top ---
    # section-nav has z-index:50, controls z-index:20 etc. — reduce so theme header wins
    content = re.sub(r'(\.ref-page-wrap\s+\.section-nav[^}]*z-index\s*:\s*)\d+', r'\g<1>30', content)
    content = re.sub(r'(\.ref-page-wrap\s+\.controls[^}]*z-index\s*:\s*)\d+',    r'\g<1>30', content)
    content = re.sub(r'(\.ref-page-wrap\s+\.trk-controls[^}]*z-index\s*:\s*)\d+', r'\g<1>30', content)
    content = re.sub(r'(\.ref-page-wrap\s+\.p-topbar[^}]*z-index\s*:\s*)\d+',    r'\g<1>30', content)

    # --- 3: Inject the JS snippet just before </div> that closes .ref-page-wrap ---
    if STICKY_JS.strip() not in content:
        content = content.replace('</div>\n\n<?php\nget_footer', STICKY_JS + '\n</div>\n\n<?php\nget_footer')

    if content != original:
        with open(path, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"OK (patched): {tpl_name}")
    else:
        print(f"OK (no changes): {tpl_name}")

print("\nDone! Dynamic sticky offset applied to all templates.")
