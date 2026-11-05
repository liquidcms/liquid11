<?php

/**
 * @file
 * Recreates the D7 nodequeues as entityqueues and copies their ordering.
 *
 * Reads the D7 database through the 'migrate' connection in settings.php.
 * Node ids were preserved by the migration. Re-runnable.
 * Run: ddev drush php:script scripts/migrate-nodequeues.php
 */

use Drupal\Core\Database\Database;
use Drupal\entityqueue\Entity\EntityQueue;
use Drupal\entityqueue\Entity\EntitySubqueue;

$d7 = Database::getConnection('default', 'migrate');
$queues = $d7->query('SELECT qid, name, title FROM {nodequeue_queue}')->fetchAll();

foreach ($queues as $q) {
  $bundles = $d7->query('SELECT type FROM {nodequeue_types} WHERE qid = :qid', [':qid' => $q->qid])->fetchCol();
  if (!$queue = EntityQueue::load($q->name)) {
    $queue = EntityQueue::create([
      'id' => $q->name,
      'label' => $q->title,
      'handler' => 'simple',
      'entity_settings' => [
        'target_type' => 'node',
        'handler' => 'default:node',
        'handler_settings' => ['target_bundles' => array_combine($bundles, $bundles)],
      ],
      'queue_settings' => ['min_size' => 0, 'max_size' => 0, 'act_as_queue' => FALSE, 'reverse' => FALSE],
    ]);
    $queue->save();
  }
  $nids = $d7->query('SELECT nid FROM {nodequeue_nodes} WHERE qid = :qid ORDER BY position', [':qid' => $q->qid])->fetchCol();
  $subqueue = EntitySubqueue::load($q->name);
  $subqueue->set('items', array_map(fn($nid) => ['target_id' => $nid], $nids));
  $subqueue->save();
  echo "{$q->name}: " . count($nids) . " items\n";
}
