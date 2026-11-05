<?php

declare(strict_types=1);

namespace Drupal\liquid_timesheets\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Timesheet settings (Configuration › System).
 */
class TimesheetSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'liquid_timesheets_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['liquid_timesheets.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['next_invoice_number'] = [
      '#type' => 'number',
      '#title' => $this->t('Next invoice number'),
      '#description' => $this->t('Printed as InvoiceNumber in the Xero export; "Mark client invoiced" adds one.'),
      '#min' => 1,
      '#required' => TRUE,
      '#config_target' => 'liquid_timesheets.settings:next_invoice_number',
    ];
    return parent::buildForm($form, $form_state);
  }

}
