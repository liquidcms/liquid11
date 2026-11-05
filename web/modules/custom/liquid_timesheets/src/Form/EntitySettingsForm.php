<?php

declare(strict_types=1);

namespace Drupal\liquid_timesheets\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Placeholder page that Field UI attaches its tabs to.
 *
 * The invoice number setting lives under Configuration › System.
 */
class EntitySettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'liquid_timesheets_entity_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['info'] = [
      '#markup' => $this->t('Use the tabs to manage fields and displays.'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {}

}
