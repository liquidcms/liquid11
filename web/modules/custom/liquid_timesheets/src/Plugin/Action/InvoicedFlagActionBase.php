<?php

declare(strict_types=1);

namespace Drupal\liquid_timesheets\Plugin\Action;

use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Sets one of the timesheet "invoiced" checkboxes; used by Manage billings.
 */
abstract class InvoicedFlagActionBase extends ActionBase {

  /**
   * The boolean field to set.
   */
  protected const FIELD = '';

  /**
   * The value to set it to.
   */
  protected const VALUE = TRUE;

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL): void {
    if ($entity && (bool) $entity->get(static::FIELD)->value !== static::VALUE) {
      $entity->set(static::FIELD, static::VALUE)->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, ?AccountInterface $account = NULL, $return_as_object = FALSE): bool|AccessResultInterface {
    /** @var \Drupal\liquid_timesheets\Entity\Timesheet $object */
    return $object->access('update', $account, $return_as_object);
  }

}
