<?php

/**
 * @file
 * Places blocks in the Evolve theme to match the D7 block layout.
 *
 * Re-runnable: existing evolve_* blocks with the same id are replaced.
 * Run: ddev drush php:script scripts/evolve-place-blocks.php
 */

use Drupal\block\Entity\Block;
use Drupal\block_content\Entity\BlockContent;

$theme = 'evolve';

// Core blocks auto-copied from Olivero that D7 never showed.
foreach (['account_menu', 'breadcrumbs', 'powered', 'site_branding', 'search'] as $id) {
  if ($block = Block::load("evolve_$id")) {
    $block->delete();
  }
}

$front = '<front>';
$blog = "/blog\n/blog/*";
$contact = '/contact';

// [d11 id => [plugin, region, weight, label (NULL hides it), pages, negate]].
// Custom block plugins are given as the D7 bid, which the migration kept as the D11 id.
$blocks = [
  'block_1' => [1, 'call_us', 0],
  'block_2' => [2, 'socials', 0],
  'block_3' => [3, 'copyright', 0],
  'block_5' => [5, 'socials_bottom', 0],
  'block_40' => [40, 'bottom_first', 0, 'GET IN TOUCH'],
  'block_4' => [4, 'bottom_first', 1, 'About'],
  'block_107' => [107, 'bottom_third', 0],
  'block_10' => [10, 'tagline', 0, NULL, $front],
  'block_11' => [11, 'feature_first', 0, NULL, $front],
  'block_12' => [12, 'feature_second', 0, NULL, $front],
  'block_13' => [13, 'feature_third', 0, NULL, $front],
  'block_68' => [68, 'content_top', 0, 'Why Choose LiquidCMS?', $front],
  'block_77' => [77, 'user1', 0, NULL, $front],
  'block_78' => [78, 'user2', 0, NULL, $front],
  'block_79' => [79, 'user3', 0, NULL, $front],
  'block_91' => [91, 'extra_bottom2', 0, 'Photo Gallery & Recent Posts', $front],
  'block_76' => [76, 'extra_bottom4', 0, 'Where To Next?', $front],
  'block_14' => [14, 'right_sidebar', -44, 'BLOG POSTS', $blog],
  'block_70' => [70, 'right_sidebar', 0, 'Contact Information', $contact],
  'block_71' => [71, 'right_sidebar', 1, NULL, $contact],
  'block_104' => [104, 'content', -78, NULL, $contact],
  'layerslider' => ['info:Home slider', 'banner', 0, NULL, $front],
  'main_menu' => ['system_menu_block:main', 'navigation', 0],
  'page_title' => ['page_title_block', 'page_title', 0, NULL, $front, TRUE],
  'content' => ['system_main_block', 'content', -77],
  'messages' => ['system_messages_block', 'highlighted', 0],
  'help' => ['help_block', 'help', 0],
  // Views blocks (views created by evolve-create-views.php).
  'views_latest_projects_block' => ['views_block:latest_projects-block', 'feature_fourth', 0, 'Cool Projects', $front],
  'views_latest_projects_block_1' => ['views_block:latest_projects-block_1', 'content', -71, 'Related Projects'],
  'views_testimonial_block' => ['views_block:testimonial-block', 'testimonial', 0, 'Some of Our Many Happy Clients', $front],
  'views_clients_block' => ['views_block:clients-block', 'clients', 0, NULL, $front],
  'views_gallery_block' => ['views_block:gallery-block', 'user18', 0, NULL, $front],
  'views_blog_block_5' => ['views_block:blog-block_5', 'user19', 0, NULL, $front],
  'views_blog_block_3' => ['views_block:blog-block_3', 'bottom_second', -73, 'FRESH OFF THE BLOG'],
  'views_blog_categories_block_1' => ['views_block:blog_categories-block_1', 'bottom_second', -42, 'Tags'],
  'views_blog_categories_block' => ['views_block:blog_categories-block', 'right_sidebar', -45, 'Categories', $blog],
  'views_team_block_2' => ['views_block:team-block_2', 'content_bottom_second', -17, 'Our Team', "/about\n/team"],
];

foreach ($blocks as $id => $def) {
  [$plugin, $region, $weight] = $def;
  $label = $def[3] ?? NULL;
  $pages = $def[4] ?? NULL;
  $negate = $def[5] ?? FALSE;

  $settings = ['label' => $label ?? $id, 'label_display' => $label ? 'visible' : '0'];
  if (is_int($plugin) || str_starts_with($plugin, 'info:')) {
    $content = is_int($plugin)
      ? BlockContent::load($plugin)
      : current(\Drupal::entityTypeManager()->getStorage('block_content')->loadByProperties(['info' => substr($plugin, 5)]));
    if (!$content) {
      echo "Missing custom block $plugin, skipped\n";
      continue;
    }
    $plugin = 'block_content:' . $content->uuid();
    $settings['label'] = $label ?? $content->label();
  }
  if (str_starts_with($plugin, 'views_block:')) {
    $settings += ['views_label' => $label ?? '', 'items_per_page' => 'none'];
  }
  if ($plugin === 'system_menu_block:main') {
    $settings += ['level' => 1, 'depth' => 0, 'expand_all_items' => TRUE];
  }

  $values = [
    'id' => "{$theme}_$id",
    'theme' => $theme,
    'region' => $region,
    'weight' => $weight,
    'plugin' => $plugin,
    'settings' => $settings,
    'visibility' => [],
  ];
  if ($pages) {
    $values['visibility']['request_path'] = [
      'id' => 'request_path',
      'pages' => $pages,
      'negate' => $negate,
    ];
  }
  if ($existing = Block::load($values['id'])) {
    $existing->delete();
  }
  Block::create($values)->save();
}
echo count($blocks) . " Evolve blocks placed\n";
