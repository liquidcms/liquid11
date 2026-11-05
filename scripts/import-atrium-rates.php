<?php

/**
 * @file
 * Imports Atrium (D6) Billing Rate (vid 3) and Tax Type (vid 7) terms.
 *
 * Re-runnable: terms are matched by name. Run with:
 *   ddev drush php:script scripts/import-atrium-rates.php
 * Needs the atrium ddev project running (db host ddev-atrium-db).
 */

use Drupal\Core\Database\Database;
use Drupal\taxonomy\Entity\Term;

Database::addConnectionInfo('atrium', 'default', [
  'driver' => 'mysql',
  'host' => 'ddev-atrium-db',
  'database' => 'db',
  'username' => 'db',
  'password' => 'db',
  'prefix' => '',
]);
$d6 = Database::getConnection('default', 'atrium');

// Atrium vid => [D11 vid, tax % by term name].
$vocabs = [
  3 => ['billing_rate', []],
  7 => ['tax_rate', ['ON - HST on Sales' => 13, 'Tax Exempt' => 0]],
];

$storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
$map = [];
foreach ($vocabs as $d6_vid => [$vid, $percents]) {
  $rows = $d6->query('SELECT tid, name, description, weight FROM {term_data} WHERE vid = :vid ORDER BY weight, name', [':vid' => $d6_vid]);
  foreach ($rows as $row) {
    $existing = $storage->loadByProperties(['vid' => $vid, 'name' => $row->name]);
    $term = $existing ? reset($existing) : Term::create(['vid' => $vid, 'name' => $row->name]);
    $term->set('description', ['value' => $row->description, 'format' => 'plain_text']);
    $term->set('weight', $row->weight);
    if (isset($percents[$row->name])) {
      $term->set('field_tax_percent', $percents[$row->name]);
    }
    $new = $term->isNew();
    $term->save();
    $map[$row->tid] = (int) $term->id();
    echo ($new ? 'created ' : 'updated ') . "$vid: {$row->name} (D6 {$row->tid} -> {$term->id()})\n";
  }
}

// D6 tid => D11 tid, for the client/project imports.
\Drupal::state()->set('liquid_timesheets.atrium_term_map', $map);
