<?php

declare(strict_types=1);

namespace Drupal\liquid_timesheets;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Admin list of projects, active first.
 *
 * Set $clientId to list only one client's projects (the client's Projects tab).
 */
class TimesheetProjectListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  protected $limit = 100;

  /**
   * Client node ID to limit the list to, if any.
   */
  public ?int $clientId = NULL;

  /**
   * {@inheritdoc}
   */
  protected function getEntityIds(): array {
    $query = $this->getStorage()->getQuery()
      ->accessCheck(TRUE)
      ->sort('archived')
      ->sort('title');
    if ($this->clientId) {
      $query->condition('client', $this->clientId);
    }
    $query->pager($this->limit);
    return $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header['title'] = $this->t('Project');
    if (!$this->clientId) {
      $header['client'] = $this->t('Client');
    }
    $header['rate'] = $this->t('Billing rate');
    $header['archived'] = $this->t('Archived');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    $row['title'] = $entity->toLink();
    if (!$this->clientId) {
      $row['client'] = $entity->get('client')->entity?->label() ?? '';
    }
    $row['rate'] = $entity->get('billing_rate')->entity?->label() ?? '';
    $row['archived'] = $entity->get('archived')->value ? $this->t('Yes') : '';
    return $row + parent::buildRow($entity);
  }

}
