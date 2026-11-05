<?php

declare(strict_types=1);

namespace Drupal\liquid_timesheets\Plugin\Action;

use Drupal\Core\Action\Attribute\Action;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Unmark imported.
 */
#[Action(
  id: 'timesheet_unmark_imported',
  label: new TranslatableMarkup('Unmark imported'),
  type: 'timesheet',
)]
class UnmarkImported extends InvoicedFlagActionBase {

  protected const FIELD = 'imported';

  protected const VALUE = FALSE;

}
