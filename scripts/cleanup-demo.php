<?php

/**
 * @file
 * Removes Evolve demo content left over from the D7 migration.
 *
 * Run: ddev drush php:script scripts/cleanup-demo.php
 */

$etm = \Drupal::entityTypeManager();

// 1. Demo nodes: every unpublished node in the D7 site was theme demo content.
$node_storage = $etm->getStorage('node');
$nids = $node_storage->getQuery()->accessCheck(FALSE)->condition('status', 0)->execute();
if ($nids) {
  $node_storage->delete($node_storage->loadMultiple($nids));
}
echo count($nids) . " unpublished demo nodes deleted\n";

// 2. Demo custom blocks. Keep only the D7 blocks (by D7 bid) used on real pages.
$keep = [
  1, 2, 3, 4, 5, 40, 107,              // Site-wide: call us, socials, copyright, footer about, get in touch, Drupal Assoc.
  10, 11, 12, 13, 68, 76, 77, 78, 79,  // Homepage sections.
  80, 81, 82, 83, 84, 91,              // Homepage pie graphs, gallery/recent posts.
  14, 70, 71, 104,                     // Blog sidebar, contact page.
];
$map = \Drupal::database()
  ->select('migrate_map_upgrade_d7_custom_block', 'm')
  ->fields('m', ['sourceid1', 'destid1'])
  ->execute()
  ->fetchAllKeyed();
$delete = [];
foreach ($map as $bid => $id) {
  if ($id && !in_array((int) $bid, $keep, TRUE)) {
    $delete[] = $id;
  }
}
$block_storage = $etm->getStorage('block_content');
$blocks = $block_storage->loadMultiple($delete);
if ($blocks) {
  $block_storage->delete($blocks);
}
echo count($blocks) . " demo custom blocks deleted, " . count($keep) . " kept\n";
