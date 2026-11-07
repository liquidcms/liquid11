<?php

declare(strict_types=1);

namespace Drupal\liquid_timesheets\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\liquid_timesheets\InvoiceNumber;

/**
 * Timesheet settings (Configuration › System).
 */
class TimesheetSettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'liquid_timesheets_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['next_invoice_number'] = [
      '#type' => 'number',
      '#title' => $this->t('Next invoice number'),
      '#description' => $this->t('Printed as InvoiceNumber in the Xero export; "Mark client invoiced" adds one, except for clients with "Don\'t advance invoice number" ticked. Stored in State, so config imports leave it alone.'),
      '#min' => 1,
      '#required' => TRUE,
      '#default_value' => InvoiceNumber::get(),
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save configuration'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    InvoiceNumber::set((int) $form_state->getValue('next_invoice_number'));
    $this->messenger()->addStatus($this->t('The configuration options have been saved.'));
  }

}
