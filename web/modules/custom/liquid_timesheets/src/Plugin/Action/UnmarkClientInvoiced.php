<?php

declare(strict_types=1);

namespace Drupal\liquid_timesheets\Plugin\Action;

use Drupal\Core\Action\Attribute\Action;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Unmark client invoiced.
 */
#[Action(
  id: 'timesheet_unmark_client_invoiced',
  label: new TranslatableMarkup('Unmark client invoiced'),
  type: 'timesheet',
)]
class UnmarkClientInvoiced extends InvoicedFlagActionBase {

  protected const FIELD = 'client_invoiced';

  protected const VALUE = FALSE;

}
