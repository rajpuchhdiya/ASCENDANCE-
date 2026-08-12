import re

ref_file = r'c:\XAMPP\htdocs\Ascendance\as\project\ui_kits\reference\cami-registry.html'
dest_file = r'c:\XAMPP\htdocs\Ascendance\wp-content\themes\ascendance\page-cami-registry.php'

with open(ref_file, 'r', encoding='utf-8') as f:
    html = f.read()

# Extract from <style> to </style>
style_match = re.search(r'<style>(.*?)</style>', html, re.DOTALL)
css_content = style_match.group(1) if style_match else ''

# Extract body content: from <div class="masthead"> up to <footer> (we exclude footer since get_footer() is called)
body_match = re.search(r'(<div class="masthead">.*?)(?:<footer>)', html, re.DOTALL)
if body_match:
    body_content = body_match.group(1)
else:
    # Fallback
    body_match = re.search(r'(<div class="masthead">.*?)(?:<script>)', html, re.DOTALL)
    body_content = body_match.group(1) if body_match else ''

# Extract script content: from <script> to </script>
script_match = re.search(r'<script>(.*?)</script>', html, re.DOTALL)
script_content = script_match.group(1) if script_match else ''

php_template = f"""<?php
/**
 * Template Name: CAMI Mining Cadastre Registry
 *
 * @package Ascendance
 */

get_header();
?>

<div class="as-page-wrap cami-app-wrap">
<style>
{css_content}

/* Overrides for integration with theme header/footer */
.as-page-wrap.cami-app-wrap {{
  min-height: 80vh;
}}
.plat-nav {{
  display: none !important; /* Managed by WordPress main navigation */
}}
footer .as-lockup, footer .ftr-cols, footer .signoff {{
  display: none !important; /* Theme footer.php handles main footer */
}}
.vintage-item b {{
  color: var(--red) !important;
}}
.tab.on {{
  color: var(--red) !important;
  border-bottom-color: var(--red) !important;
}}
.chip.on, .pg.cur, thead th {{
  background: var(--red) !important;
  border-color: var(--red) !important;
}}
thead th {{
  color: #ffffff !important;
}}
</style>

{body_content}

<script>
{script_content}
</script>
</div>

<?php
get_footer();
"""

with open(dest_file, 'w', encoding='utf-8') as f:
    f.write(php_template)

print("Successfully converted cami-registry.html into page-cami-registry.php")
