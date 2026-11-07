<?php

declare(strict_types=1);

namespace Drupal\liquid_timesheets\Plugin\Action;

use Drupal\Core\Action\Attribute\Action;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\liquid_timesheets\InvoiceNumber;

/**
 * Mark client invoiced, then move on to the next invoice number.
 *
 * Export the CSV first: it prints the current number.
 */
#[Action(
  id: 'timesheet_mark_client_invoiced',
  label: new TranslatableMarkup('Mark client invoiced'),
  type: 'timesheet',
)]
class MarkClientInvoiced extends InvoicedFlagActionBase {

  protected const FIELD = 'client_invoiced';

  protected const VALUE = TRUE;

  /**
   * Client field that, when ticked, stops the invoice number moving on.
   */
  public const SKIP_FIELD = 'field_skip_invoice_number';

  /**
   * {@inheritdoc}
   *
   * One bulk run is one invoice, so the number goes up once, unless every
   * timesheet in the run belongs to a client that isn't invoiced through
   * Xero (e.g. Tek Systems).
   */
  public function executeMultiple(array $entities): void {
    parent::executeMultiple($entities);
    foreach ($entities as $entity) {
      if (!static::skipsInvoiceNumber($entity)) {
        InvoiceNumber::increment();
        return;
      }
    }
  }

  /**
   * Whether a timesheet's client has "Don't advance invoice number" ticked.
   *
   * @param \Drupal\liquid_timesheets\Entity\Timesheet $timesheet
   *   The timesheet.
   */
  public static function skipsInvoiceNumber($timesheet): bool {
    $client = $timesheet->get('project')->entity?->get('client')->entity;
    return $client
      && $client->hasField(self::SKIP_FIELD)
      && (bool) $client->get(self::SKIP_FIELD)->value;
  }

}
