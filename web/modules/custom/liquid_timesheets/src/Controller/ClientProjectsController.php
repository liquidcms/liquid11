<?php

declare(strict_types=1);

namespace Drupal\liquid_timesheets\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;

/**
 * The "Projects" tab on client nodes.
 */
class ClientProjectsController extends ControllerBase {

  /**
   * Lists the client's projects with an add link prefilled for the client.
   */
  public function list(NodeInterface $node): array {
    /** @var \Drupal\liquid_timesheets\TimesheetProjectListBuilder $list */
    $list = $this->entityTypeManager()->getListBuilder('timesheet_project');
    $list->clientId = (int) $node->id();
    return [
      'add' => [
        '#type' => 'link',
        '#title' => $this->t('Add project'),
        '#url' => Url::fromRoute('entity.timesheet_project.add_form', [], [
          'query' => ['client' => $node->id(), 'destination' => Url::fromRoute('<current>')->toString()],
        ]),
        '#attributes' => ['class' => ['button', 'button--primary', 'button--small']],
      ],
      'list' => $list->render(),
    ];
  }

  /**
   * Only client nodes get the tab, and only for timesheet admins.
   */
  public function access(NodeInterface $node, AccountInterface $account): AccessResultInterface {
    return AccessResult::allowedIf($node->bundle() === 'clients')
      ->andIf(AccessResult::allowedIfHasPermission($account, 'administer timesheets'))
      ->addCacheableDependency($node);
  }

}
