<?php

declare(strict_types=1);

namespace Drupal\liquid_timesheets\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Attribute\ViewsField;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Today's date plus an offset, e.g. InvoiceDate / DueDate on invoice exports.
 */
#[ViewsField('liquid_timesheets_relative_date')]
class RelativeDate extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query(): void {}

  /**
   * {@inheritdoc}
   */
  protected function defineOptions(): array {
    $options = parent::defineOptions();
    $options['offset'] = ['default' => 'now'];
    $options['format'] = ['default' => 'd-m-Y'];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state): void {
    parent::buildOptionsForm($form, $form_state);
    $form['offset'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Date'),
      '#description' => $this->t('A strtotime() string, e.g. "now" or "+15 days".'),
      '#default_value' => $this->options['offset'],
    ];
    $form['format'] = [
      '#type' => 'textfield',
      '#title' => $this->t('PHP date format'),
      '#default_value' => $this->options['format'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values): string {
    $timezone = date_default_timezone_get();
    return (new \DateTime($this->options['offset'], new \DateTimeZone($timezone)))->format($this->options['format']);
  }

}
