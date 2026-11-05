<?php

/**
 * @file
 * Imports Atrium (D6) staff, clients, projects and timesheets.
 *
 * Run import-atrium-rates.php first. Re-runnable: D6 nid/uid => D11 id maps
 * are kept in state and existing items are updated in place. Run with:
 *   ddev drush php:script scripts/import-atrium-timesheets.php
 * Needs the atrium ddev project running (db host ddev-atrium-db).
 */

use Drupal\Core\Database\Database;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

Database::addConnectionInfo('atrium', 'default', [
  'driver' => 'mysql',
  'host' => 'ddev-atrium-db',
  'database' => 'db',
  'username' => 'db',
  'password' => 'db',
  'prefix' => '',
]);
$d6 = Database::getConnection('default', 'atrium');
$etm = \Drupal::entityTypeManager();
$state = \Drupal::state();
$terms = $state->get('liquid_timesheets.atrium_term_map', []);
if (!$terms) {
  exit("Run scripts/import-atrium-rates.php first.\n");
}
$tz = new \DateTimeZone('America/Toronto');

// --- Staff: everyone who logged time. peter is the D11 admin (uid 1); the
// rest become blocked accounts so their entries keep a name.
$users = $state->get('liquid_timesheets.atrium_user_map', [0 => 0, 5 => 1]);
$rates = $d6->query("SELECT v.uid, v.value FROM {profile_values} v JOIN {profile_fields} f ON f.fid = v.fid WHERE f.name = 'profile_rate'")->fetchAllKeyed();
// Full names are the titles of the content_profile nodes.
$names = $d6->query("SELECT uid, title FROM {node} WHERE type = 'profile'")->fetchAllKeyed();
$rows = $d6->query("SELECT DISTINCT u.uid, u.name, u.mail, u.created FROM {users} u JOIN {node} n ON n.uid = u.uid WHERE n.type = 'timesheet' AND u.uid > 0");
foreach ($rows as $row) {
  $account = isset($users[$row->uid]) ? User::load($users[$row->uid]) : NULL;
  if (!$account) {
    $existing = $etm->getStorage('user')->loadByProperties(['name' => $row->name]);
    $account = $existing ? reset($existing) : User::create([
      'name' => $row->name,
      'mail' => $row->mail,
      'status' => 0,
      'created' => $row->created,
    ]);
  }
  if (!empty($names[$row->uid]) && $account->get('field_full_name')->isEmpty()) {
    $account->set('field_full_name', $names[$row->uid]);
  }
  if (isset($rates[$row->uid]) && is_numeric($rates[$row->uid])) {
    $account->set('field_hourly_rate', $rates[$row->uid]);
  }
  $account->save();
  $users[$row->uid] = (int) $account->id();
}
$state->set('liquid_timesheets.atrium_user_map', $users);
echo 'staff: ' . count($users) . "\n";

// --- Clients (Atrium groups) => unpublished clients nodes, matched by title.
$clients = $state->get('liquid_timesheets.atrium_client_map', []);
$rows = $d6->query("SELECT n.nid, n.title, n.created, n.changed, g.field_currency_value AS currency,
    (SELECT tn.tid FROM {term_node} tn JOIN {term_data} t ON t.tid = tn.tid WHERE tn.vid = n.vid AND t.vid = 7 LIMIT 1) AS tax
  FROM {node} n LEFT JOIN {content_type_group} g ON g.vid = n.vid WHERE n.type = 'group'");
$currency = [];
foreach ($etm->getStorage('taxonomy_term')->loadByProperties(['vid' => 'currency']) as $term) {
  $currency[$term->label()] = $term->id();
}
$created = 0;
foreach ($rows as $row) {
  $node = isset($clients[$row->nid]) ? Node::load($clients[$row->nid]) : NULL;
  if (!$node) {
    $existing = $etm->getStorage('node')->loadByProperties(['type' => 'clients', 'title' => $row->title]);
    $node = $existing ? reset($existing) : Node::create([
      'type' => 'clients',
      'title' => $row->title,
      'status' => 0,
      'uid' => 1,
      'created' => $row->created,
    ]);
    $created += $node->isNew() ? 1 : 0;
  }
  $node->set('field_tax_rate', $row->tax ? ($terms[$row->tax] ?? NULL) : NULL);
  $node->set('field_currency', $currency[$row->currency ?: 'CAD'] ?? NULL);
  if ($node->isNew()) {
    $node->get('path')->pathauto = 0;
  }
  $node->setNewRevision(FALSE);
  $node->save();
  $clients[$row->nid] = (int) $node->id();
}
$state->set('liquid_timesheets.atrium_client_map', $clients);
echo 'clients: ' . count($clients) . " ($created new)\n";

// --- Projects.
$projects = $state->get('liquid_timesheets.atrium_project_map', []);
$storage = $etm->getStorage('timesheet_project');
$rows = $d6->query("SELECT n.nid, n.uid, n.title, n.created, n.changed, r.body, oa.group_nid,
    p.field_default_value AS is_default, p.field_archived_value AS archived,
    (SELECT tn.tid FROM {term_node} tn JOIN {term_data} t ON t.tid = tn.tid WHERE tn.vid = n.vid AND t.vid = 3 LIMIT 1) AS rate
  FROM {node} n
  JOIN {node_revisions} r ON r.vid = n.vid
  LEFT JOIN {content_type_casetracker_basic_project} p ON p.vid = n.vid
  LEFT JOIN {og_ancestry} oa ON oa.nid = n.nid
  WHERE n.type = 'casetracker_basic_project'");
foreach ($rows as $row) {
  $project = isset($projects[$row->nid]) ? $storage->load($projects[$row->nid]) : NULL;
  $project ??= $storage->create();
  $project->set('title', $row->title);
  $project->set('client', $clients[$row->group_nid] ?? NULL);
  $project->set('billing_rate', $row->rate ? ($terms[$row->rate] ?? NULL) : NULL);
  $project->set('description', ['value' => $row->body, 'format' => 'plain_text']);
  $project->set('is_default', (bool) $row->is_default);
  $project->set('archived', (bool) $row->archived);
  $project->set('uid', $users[$row->uid] ?? 1);
  $project->set('created', $row->created);
  $project->save();
  $projects[$row->nid] = (int) $project->id();
}
$state->set('liquid_timesheets.atrium_project_map', $projects);
echo 'projects: ' . count($projects) . "\n";

// --- Timesheets. Dates are stored as Toronto midnight in UTC.
$sheets = $state->get('liquid_timesheets.atrium_timesheet_map', []);
$storage = $etm->getStorage('timesheet');
$rows = $d6->query("SELECT n.nid, n.uid, n.title, n.created, n.changed, r.body,
    d.field_project_nid AS project, d.field_ts_date_value AS ts_date, d.field_hours_value AS hours,
    d.field_imported_value AS imported, d.field_clientinvoiced_value AS client_invoiced
  FROM {node} n
  JOIN {node_revisions} r ON r.vid = n.vid
  JOIN {content_type_timesheet} d ON d.vid = n.vid
  WHERE n.type = 'timesheet' ORDER BY n.nid");
$count = 0;
foreach ($rows as $row) {
  $sheet = isset($sheets[$row->nid]) ? $storage->load($sheets[$row->nid]) : NULL;
  $sheet ??= $storage->create();
  $sheet->set('title', mb_substr($row->title, 0, 255));
  $sheet->set('description', ['value' => $row->body, 'format' => 'plain_text']);
  $sheet->set('project', $projects[$row->project] ?? NULL);
  $sheet->set('work_date', (new \DateTime('@' . $row->ts_date))->setTimezone($tz)->format('Y-m-d'));
  $sheet->set('hours', $row->hours ?? 0);
  $sheet->set('imported', (bool) $row->imported);
  $sheet->set('client_invoiced', (bool) $row->client_invoiced);
  $sheet->set('uid', $users[$row->uid] ?? 0);
  $sheet->set('created', $row->created);
  $sheet->save();
  // Save sets "changed" to now; put the Atrium value back.
  \Drupal::database()->update('timesheet')->fields(['changed' => $row->changed])->condition('id', $sheet->id())->execute();
  $sheets[$row->nid] = (int) $sheet->id();
  if (++$count % 500 === 0) {
    echo "  timesheets: $count\n";
    $state->set('liquid_timesheets.atrium_timesheet_map', $sheets);
    $storage->resetCache();
  }
}
$state->set('liquid_timesheets.atrium_timesheet_map', $sheets);
$storage->resetCache();
echo 'timesheets: ' . count($sheets) . "\n";
