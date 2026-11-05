<?php

declare(strict_types=1);

namespace Drupal\liquid_timesheets\Plugin\Action;

use Drupal\Core\Action\Attribute\Action;
use Drupal\Core\StringTranslation\TranslatableMarkup;

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
   * {@inheritdoc}
   *
   * One bulk run is one invoice, so the number goes up once.
   */
  public function executeMultiple(array $entities): void {
    parent::executeMultiple($entities);
    $config = \Drupal::configFactory()->getEditable('liquid_timesheets.settings');
    $config->set('next_invoice_number', (int) $config->get('next_invoice_number') + 1)->save();
  }

}
