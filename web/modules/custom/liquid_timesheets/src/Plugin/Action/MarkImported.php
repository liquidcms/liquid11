<?php

declare(strict_types=1);

namespace Drupal\liquid_timesheets\Plugin\Action;

use Drupal\Core\Action\Attribute\Action;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Mark imported.
 */
#[Action(
  id: 'timesheet_mark_imported',
  label: new TranslatableMarkup('Mark imported'),
  type: 'timesheet',
)]
class MarkImported extends InvoicedFlagActionBase {

  protected const FIELD = 'imported';

  protected const VALUE = TRUE;

}
