<?php

declare(strict_types=1);

namespace Drupal\liquid_timesheets;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Plain admin list; the real reporting screens will be Views.
 */
class TimesheetListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  protected $limit = 100;

  /**
   * {@inheritdoc}
   */
  protected function getEntityIds(): array {
    $query = $this->getStorage()->getQuery()
      ->accessCheck(TRUE)
      ->sort('work_date', 'DESC')
      ->sort('id', 'DESC');
    $query->pager($this->limit);
    return $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    return [
      'date' => $this->t('Date'),
      'description' => $this->t('Description'),
      'project' => $this->t('Client » project'),
      'staff' => $this->t('Staff'),
      'hours' => $this->t('Hours'),
    ] + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    /** @var \Drupal\liquid_timesheets\Entity\Timesheet $entity */
    return [
      'date' => $entity->get('work_date')->value,
      'description' => $entity->toLink(Unicode::truncate(strip_tags((string) $entity->get('description')->value), 80, TRUE, TRUE) ?: $this->t('(no description)')),
      'project' => _liquid_timesheets_project_label($entity->get('project')->entity),
      'staff' => $entity->getOwner()?->getDisplayName() ?? '',
      'hours' => $entity->get('hours')->value,
    ] + parent::buildRow($entity);
  }

}
