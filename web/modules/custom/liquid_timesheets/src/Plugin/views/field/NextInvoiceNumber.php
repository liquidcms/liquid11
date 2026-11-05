<?php

declare(strict_types=1);

namespace Drupal\liquid_timesheets\Plugin\views\field;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\views\Attribute\ViewsField;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * The "Next invoice number" setting, for invoice exports.
 */
#[ViewsField('liquid_timesheets_next_invoice_number')]
class NextInvoiceNumber extends FieldPluginBase implements CacheableDependencyInterface {

  /**
   * {@inheritdoc}
   */
  public function query(): void {}

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values): string {
    return (string) \Drupal::config('liquid_timesheets.settings')->get('next_invoice_number');
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags(): array {
    return ['config:liquid_timesheets.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge(): int {
    return -1;
  }

}
