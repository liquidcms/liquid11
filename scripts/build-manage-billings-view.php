<?php

/**
 * @file
 * Builds the Manage billings view (page + Xero CSV export).
 *
 * Re-runnable: deletes and recreates views.view.manage_billings. The result
 * is also shipped as liquid_timesheets/config/optional/views.view.manage_billings.yml.
 *   ddev drush php:script scripts/build-manage-billings-view.php
 */

use Drupal\views\Entity\View;

View::load('manage_billings')?->delete();
$view = View::create([
  'id' => 'manage_billings',
  'label' => 'Manage billings',
  'description' => 'Timesheet billing: filter, mark invoiced, export to Xero.',
  'base_table' => 'timesheet',
  'base_field' => 'id',
]);
$exe = $view->getExecutable();
$exe->initDisplay();
$def = $exe->displayHandlers->get('default');
$def->setOption('access', ['type' => 'perm', 'options' => ['perm' => 'administer timesheets']]);
$def->setOption('pager', ['type' => 'none', 'options' => ['offset' => 0]]);
$def->setOption('exposed_form', ['type' => 'basic', 'options' => ['submit_button' => 'Filter', 'reset_button' => TRUE]]);
$def->setOption('title', 'Manage billings');
$def->setOption('style', ['type' => 'table', 'options' => ['sticky' => TRUE, 'empty_table' => TRUE]]);
$def->setOption('row', ['type' => 'fields']);

$add = function (string $type, string $table, string $field, array $options = [], ?string $id = NULL, string $display = 'default') use ($exe) {
  return $exe->addHandler($display, $type, $table, $field, $options, $id);
};
$plain_ref = ['type' => 'entity_reference_label', 'settings' => ['link' => FALSE]];

// Relationships.
$add('relationship', 'timesheet', 'project', ['required' => TRUE]);
$add('relationship', 'timesheet_project', 'client', ['relationship' => 'project', 'required' => TRUE]);
$add('relationship', 'timesheet', 'uid', ['required' => FALSE]);

// Filters (exposed, as on the Atrium page).
$exposed = fn(string $id, string $label, array $extra = []) => [
  'exposed' => TRUE,
  'expose' => [
    'operator_id' => $id . '_op',
    'label' => $label,
    'identifier' => $id,
    'remember' => TRUE,
    'multiple' => !empty($extra['multiple']),
  ] + ($extra['expose'] ?? []),
] + array_diff_key($extra, ['multiple' => 1, 'expose' => 1]);

$add('filter', 'timesheet_project', 'client', $exposed('client', 'Client', ['relationship' => 'project', 'sub_handler' => 'default:node', 'sub_handler_settings' => ['target_bundles' => ['clients' => 'clients'], 'sort' => ['field' => 'title', 'direction' => 'ASC'], 'auto_create' => FALSE], 'operator' => 'or', 'widget' => 'select', 'multiple' => TRUE, 'value' => []]), 'client');
$add('filter', 'timesheet', 'project', $exposed('project', 'Project', ['sub_handler' => 'default:timesheet_project', 'sub_handler_settings' => ['sort' => ['field' => 'title', 'direction' => 'ASC'], 'auto_create' => FALSE], 'operator' => 'or', 'widget' => 'select', 'multiple' => TRUE, 'value' => []]), 'project');
$add('filter', 'timesheet_project', 'billing_rate', $exposed('rate', 'Billing rates to exclude', ['relationship' => 'project', 'sub_handler' => 'default:taxonomy_term', 'sub_handler_settings' => ['target_bundles' => ['billing_rate' => 'billing_rate'], 'sort' => ['field' => 'name', 'direction' => 'ASC'], 'auto_create' => FALSE], 'operator' => 'not', 'widget' => 'select', 'multiple' => TRUE, 'value' => []]), 'billing_rate');
$add('filter', 'timesheet', 'uid', $exposed('staff', 'Staff', ['sub_handler' => 'default:user', 'sub_handler_settings' => ['sort' => ['field' => 'name', 'direction' => 'ASC'], 'auto_create' => FALSE], 'operator' => 'or', 'widget' => 'select', 'multiple' => TRUE, 'value' => []]), 'uid');
$add('filter', 'timesheet', 'imported', $exposed('imported', 'Imported', ['value' => 'All']), 'imported');
$add('filter', 'timesheet', 'client_invoiced', $exposed('client_invoiced', 'Client invoiced', ['value' => '0']), 'client_invoiced');
$add('filter', 'timesheet', 'work_date', $exposed('date', 'Date', [
  'operator' => 'between',
  'value' => ['type' => 'date', 'min' => '', 'max' => '', 'value' => ''],
]), 'work_date');
$add('filter', 'timesheet', 'description__value', $exposed('details', 'Details contains', ['operator' => 'contains', 'value' => '']), 'description');

// Sorts.
$add('sort', 'timesheet', 'work_date', ['order' => 'ASC', 'granularity' => 'day']);
$add('sort', 'timesheet', 'changed', ['order' => 'ASC']);

// Page fields.
$add('field', 'timesheet', 'timesheet_bulk_form', [
  'label' => '',
  'action_title' => 'Action',
  'include_exclude' => 'include',
  'selected_actions' => [
    'timesheet_mark_imported',
    'timesheet_mark_client_invoiced',
    'timesheet_mark_client_invoiced_imported',
    'timesheet_unmark_imported',
    'timesheet_unmark_client_invoiced',
  ],
], 'timesheet_bulk_form');
$add('field', 'timesheet', 'work_date', ['label' => 'Date', 'type' => 'datetime_default', 'settings' => ['format_type' => 'html_date']], 'work_date');
$add('field', 'node_field_data', 'title', ['label' => 'Client', 'relationship' => 'client', 'settings' => ['link_to_entity' => FALSE]], 'client');
$add('field', 'timesheet_project', 'title', ['label' => 'Project', 'relationship' => 'project', 'settings' => ['link_to_entity' => TRUE]], 'project');
$add('field', 'timesheet', 'hours', ['label' => 'Hours', 'type' => 'number_decimal', 'settings' => ['scale' => 2, 'thousand_separator' => '']], 'hours');
$add('field', 'timesheet', 'imported', ['label' => 'Imported', 'settings' => ['format' => 'yes-no']], 'imported');
$add('field', 'timesheet', 'client_invoiced', ['label' => 'Client invoiced', 'settings' => ['format' => 'yes-no']], 'client_invoiced');
$add('field', 'timesheet', 'description__value', ['label' => 'Details', 'type' => 'text_default'], 'description');
$add('field', 'timesheet', 'uid', ['label' => 'Employee'] + $plain_ref, 'uid');
$add('field', 'timesheet_project', 'billing_rate', ['label' => 'Rate', 'relationship' => 'project'] + $plain_ref, 'billing_rate');
$add('field', 'timesheet', 'operations', ['label' => 'Edit'], 'operations');

$add('header', 'timesheet', 'export_link', ['display_id' => 'xero_csv', 'label' => 'Download Xero CSV', 'empty' => FALSE], 'export_link');
$add('header', 'timesheet', 'hours_total', [], 'hours_total');
$add('footer', 'timesheet', 'hours_total', [], 'hours_total');
$add('empty', 'views', 'area_text_custom', ['empty' => TRUE, 'content' => 'No timesheets match these filters.'], 'area_text_custom');

// Page display.
$page = $exe->newDisplay('page', 'Manage billings', 'page_1');
$page->setOption('path', 'admin/content/billing');
$page->setOption('menu', ['type' => 'normal', 'title' => 'Billing', 'description' => 'Filter timesheets, mark invoiced, export to Xero.', 'menu_name' => 'admin', 'parent' => 'system.admin_content', 'weight' => 22, 'context' => '', 'expanded' => FALSE]);

// Xero CSV export: same filters, its own fields in Xero's column order.
$xero = $exe->newDisplay('data_export', 'Xero CSV', 'xero_csv');
$xero->setOption('path', 'admin/content/billing/xero.csv');
$xero->setOption('displays', ['page_1' => 'page_1']);
$xero->setOption('filename', 'xero-[date:custom:Y-m-d].csv');
// Only used by batch exports, but the default (private://) isn't configured here.
$xero->setOption('export_filesystem', 'public');
foreach (['fields', 'header', 'footer', 'style', 'row', 'pager'] as $section) {
  $xero->setOverride($section, FALSE);
}
$xero->setOption('header', []);
$xero->setOption('footer', []);
$xero->setOption('fields', []);
$xero->setOption('pager', ['type' => 'none', 'options' => ['offset' => 0]]);
$xero->setOption('row', ['type' => 'data_field', 'options' => []]);
$xero->setOption('style', [
  'type' => 'data_export',
  'options' => ['formats' => ['csv' => 'csv'], 'csv_settings' => ['delimiter' => ',', 'enclosure' => '"', 'escape_char' => '\\', 'strip_tags' => TRUE, 'trim' => TRUE, 'encoding' => 'utf8', 'utf8_bom' => '0', 'use_serializer_encode_only' => FALSE]],
]);

$x = fn(string $table, string $field, array $options, string $id) => $exe->addHandler('xero_csv', 'field', $table, $field, $options, $id);
$mdY = ['type' => 'datetime_custom', 'settings' => ['date_format' => 'm/d/Y']];
$x('node_field_data', 'title', ['label' => 'ContactName', 'relationship' => 'client', 'settings' => ['link_to_entity' => FALSE]], 'contact');
$x('timesheet', 'work_date', ['label' => 'Date'] + $mdY, 'work_date');
$x('timesheet', 'uid', ['label' => 'Employee'] + $plain_ref, 'uid');
$x('timesheet', 'invoice_date', ['label' => 'InvoiceDate', 'offset' => 'now', 'format' => 'd-m-Y'], 'invoice_date');
$x('timesheet', 'due_date', ['label' => 'DueDate', 'offset' => '+15 days', 'format' => 'd-m-Y'], 'due_date');
$x('timesheet_project', 'billing_rate', ['label' => 'InventoryItemCode', 'relationship' => 'project'] + $plain_ref, 'item_code');
$x('timesheet', 'description__value', [
  'label' => 'Description',
  'type' => 'basic_string',
  'alter' => ['alter_text' => TRUE, 'text' => '{{ work_date }} - {{ uid }} - {{ description }}'],
], 'description');
$x('timesheet', 'hours', ['label' => 'Quantity', 'type' => 'number_decimal', 'settings' => ['scale' => 2, 'thousand_separator' => '']], 'quantity');
$x('user__field_hourly_rate', 'field_hourly_rate', ['label' => 'UnitAmount', 'relationship' => 'uid', 'type' => 'number_decimal', 'settings' => ['scale' => 2, 'thousand_separator' => '', 'prefix_suffix' => FALSE]], 'unit_amount');
$x('node__field_tax_rate', 'field_tax_rate', ['label' => 'TaxType', 'relationship' => 'client'] + $plain_ref, 'tax_type');
$x('views', 'nothing', ['label' => 'AccountCode', 'alter' => ['text' => '4002', 'alter_text' => TRUE], 'hide_alter_empty' => FALSE], 'account_code');
// Current setting; "Mark client invoiced" bumps it, so export first.
$x('timesheet', 'next_invoice_number', ['label' => 'InvoiceNumber'], 'invoice_number');
$x('node__field_currency', 'field_currency', ['label' => 'Currency', 'relationship' => 'client'] + $plain_ref, 'currency');
$exe->storage->save();

echo "manage_billings built\n";
