<?php

declare(strict_types=1);

namespace Drupal\liquid_timesheets\Plugin\views\field;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\liquid_timesheets\InvoiceNumber;
use Drupal\views\Attribute\ViewsField;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * The next invoice number, for invoice exports.
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
    return (string) InvoiceNumber::get();
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
    return [InvoiceNumber::CACHE_TAG];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge(): int {
    return -1;
  }

}
