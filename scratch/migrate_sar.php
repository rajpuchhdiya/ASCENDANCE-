<?php
$html = file_get_contents('C:\XAMPP\htdocs\Ascendance\as\project\ui_kits\reference\sar-registry.html');

// Extract CSS
preg_match('/<style>(.*?)<\/style>/s', $html, $cssMatch);
$css = $cssMatch[1] ?? '';

// Extract view-registry (excluding the generic footer parts, keeping ftr-meta)
preg_match('/<div id="view-registry">(.*?)<footer>/s', $html, $registryMatch);
$registry = $registryMatch[1] ?? '';

preg_match('/<div class="signoff">.*?<\/div>\s*<div class="ftr-meta">.*?<\/div>/s', $html, $metaMatch1);
$meta1 = $metaMatch1[0] ?? '';

// Extract view-profile (excluding the generic footer parts, keeping ftr-meta)
preg_match('/<div id="view-profile" class="hidden">(.*?)<footer>/s', $html, $profileMatch);
$profile = $profileMatch[1] ?? '';

preg_match_all('/<div class="signoff">.*?<\/div>\s*<div class="ftr-meta">.*?<\/div>/s', $html, $metaMatches);
$meta2 = $metaMatches[0][1] ?? '';

// Extract JS
preg_match('/<script>\s*\/\* ---------- theme control(.*?)(<\/script>)/s', $html, $jsMatch);
$js = "/* ---------- theme control" . ($jsMatch[1] ?? '');

// Update the links in JS to point to WP
$js = str_replace('../portal/index.html#a/kinshasa-didnt-choose', "<?php echo esc_url( home_url( '/us-drc-partnership/the-fifth-model-chemaf/' ) ); ?>", $js);
$js = str_replace('../marketing/index.html', "<?php echo esc_url( home_url( '/advisory/' ) ); ?>", $js);

// Build final PHP content
$finalPhp = "<?php\n/**\n * Template Name: Strategic Asset Reserve Registry\n *\n * @package Ascendance\n */\n\nget_header();\n?>\n\n";
$finalPhp .= "<div class=\"as-page-wrap ref-page-wrap\">\n<style>\n" . $css . "\n</style>\n\n";
$finalPhp .= "<!-- ==================== REGISTRY VIEW ==================== -->\n";
$finalPhp .= "<div id=\"view-registry\">\n" . $registry;
$finalPhp .= "\n<div style=\"padding: 20px 36px;\">\n" . $meta1 . "\n</div>\n</div>\n\n";

$finalPhp .= "<!-- ==================== PROFILE VIEW ==================== -->\n";
$finalPhp .= "<div id=\"view-profile\" class=\"hidden\">\n" . $profile;
$finalPhp .= "\n<div style=\"padding: 20px 36px;\">\n" . $meta2 . "\n</div>\n</div>\n\n";

$finalPhp .= "<script>\n" . $js . "\n</script>\n";
$finalPhp .= "</div>\n\n<?php\nget_footer();\n";

file_put_contents('C:\XAMPP\htdocs\Ascendance\wp-content\themes\ascendance\page-sar-registry.php', $finalPhp);
echo "Done replacing page-sar-registry.php\n";
