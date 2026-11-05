<?php

declare(strict_types=1);

namespace Drupal\liquid_timesheets;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Projects are managed by admins; staff only need to see them to log time.
 */
class TimesheetProjectAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account): AccessResultInterface {
    if ($operation === 'view') {
      return AccessResult::allowedIfHasPermissions($account, ['create timesheets', 'administer timesheets'], 'OR');
    }
    // Everything else needs the entity type's admin permission.
    return parent::checkAccess($entity, $operation, $account);
  }

}
