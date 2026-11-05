<?php

declare(strict_types=1);

namespace Drupal\liquid_timesheets\Plugin\Action;

use Drupal\Core\Action\Attribute\Action;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Mark client invoiced and imported in one go (bumps the invoice number).
 */
#[Action(
  id: 'timesheet_mark_client_invoiced_imported',
  label: new TranslatableMarkup('Mark client invoiced + imported'),
  type: 'timesheet',
)]
class MarkClientInvoicedImported extends MarkClientInvoiced {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL): void {
    $entity?->set('client_invoiced', TRUE)->set('imported', TRUE)->save();
  }

}
