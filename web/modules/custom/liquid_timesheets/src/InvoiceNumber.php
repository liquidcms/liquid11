<?php

declare(strict_types=1);

namespace Drupal\liquid_timesheets;

use Drupal\Core\Cache\Cache;

/**
 * The next invoice number, kept in State.
 *
 * It changes on the live site every time an invoice goes out, so it is not
 * config: a "drush cim" would put it back to whatever was last exported.
 */
final class InvoiceNumber {

  public const STATE_KEY = 'liquid_timesheets.next_invoice_number';

  /**
   * Cache tag for anything that prints the number (the Xero export).
   *
   * Named after the config object that used to hold it, so existing view
   * cache metadata keeps working.
   */
  public const CACHE_TAG = 'config:liquid_timesheets.settings';

  /**
   * Returns the next invoice number, or NULL if it has never been set.
   */
  public static function get(): ?int {
    $value = \Drupal::state()->get(self::STATE_KEY);
    return $value === NULL ? NULL : (int) $value;
  }

  /**
   * Sets the next invoice number.
   */
  public static function set(int $number): void {
    \Drupal::state()->set(self::STATE_KEY, $number);
    Cache::invalidateTags([self::CACHE_TAG]);
  }

  /**
   * Moves on to the next invoice number.
   */
  public static function increment(): void {
    self::set((self::get() ?? 0) + 1);
  }

}
