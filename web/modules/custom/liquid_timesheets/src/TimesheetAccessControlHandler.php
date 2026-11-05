<?php

declare(strict_types=1);

namespace Drupal\liquid_timesheets;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Own/any permissions for timesheets, keyed on the owner (staff) field.
 */
class TimesheetAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account): AccessResultInterface {
    $verb = match ($operation) {
      'view' => 'view',
      'update' => 'edit',
      'delete' => 'delete',
      default => NULL,
    };
    if (!$verb) {
      return AccessResult::neutral();
    }
    $any = AccessResult::allowedIfHasPermission($account, "$verb any timesheets");
    if ($any->isAllowed()) {
      return $any;
    }
    /** @var \Drupal\user\EntityOwnerInterface $entity */
    $is_owner = (int) $entity->getOwnerId() === (int) $account->id();
    return AccessResult::allowedIf($is_owner && $account->hasPermission("$verb own timesheets"))
      ->cachePerPermissions()
      ->cachePerUser()
      ->addCacheableDependency($entity);
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL): AccessResultInterface {
    return AccessResult::allowedIfHasPermission($account, 'create timesheets');
  }

}
