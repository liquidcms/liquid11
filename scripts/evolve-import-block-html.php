<?php

/**
 * @file
 * Replaces migrated custom block bodies with the HTML D7 rendered for them.
 *
 * D7 expanded dexp shortcodes at render time; D11 has no shortcode filter,
 * so we store the expanded HTML, converted from Bootstrap 3 to 5. Inline pie
 * graph scripts become data attributes handled by evolve-d11.js.
 *
 * Input: scripts/d7-blocks-rendered.json (D7 check_markup() output by bid).
 * Run: ddev drush php:script scripts/evolve-import-block-html.php
 *
 * One-time migration step: re-running overwrites any edits made on D11 since.
 */

use Drupal\block_content\Entity\BlockContent;

require_once __DIR__ . '/bs3-to-bs5.php';

$rendered = json_decode(file_get_contents(__DIR__ . '/d7-blocks-rendered.json'), TRUE);
$count = 0;
foreach ($rendered as $bid => $data) {
  $block = BlockContent::load($bid);
  if (!$block) {
    continue;
  }
  $html = str_replace(
    ['/sites/all/files/', '/sites/all/themes/evolve/assets/'],
    ['/sites/default/files/', '/themes/custom/evolve/vendor/evolve/assets/'],
    $data['html']
  );
  // Pie graph: <canvas id=X> + <script>...('X',60,color...)</script>.
  $html = preg_replace_callback('#(<canvas id="([^"]+)"[^>]*)>(.*?)<script.*?dexp_shortcodes_piegraph\(\'\2\',(\d+),color.*?</script>#s', function ($m) {
    preg_match("#var color = '([^']+)'#", $m[0], $c);
    return $m[1] . ' data-percent="' . $m[4] . '" data-color="' . ($c[1] ?? '#FFF') . '">' . $m[3];
  }, $html);
  $html = evolve_bs3_to_bs5($html);
  $block->set('body', ['value' => trim($html), 'format' => 'raw_html']);
  $block->save();
  $count++;
}
echo "$count custom blocks updated with D7-rendered HTML\n";

// The homepage Revolution slider was a dexp_layerslider block in D7, not a
// custom block. Its rendered markup is kept as a custom block; evolve-slider.js starts it.
$slider_html = evolve_bs3_to_bs5(file_get_contents(__DIR__ . '/d7-home-slider.html'));
$existing = \Drupal::entityTypeManager()->getStorage('block_content')->loadByProperties(['info' => 'Home slider']);
$slider = $existing ? reset($existing) : BlockContent::create(['type' => 'basic', 'info' => 'Home slider']);
$slider->set('body', ['value' => $slider_html, 'format' => 'raw_html']);
$slider->save();
echo "Home slider block saved (id {$slider->id()})\n";
