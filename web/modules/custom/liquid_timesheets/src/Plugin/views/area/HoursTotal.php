<?php

declare(strict_types=1);

namespace Drupal\liquid_timesheets\Plugin\views\area;

use Drupal\views\Attribute\ViewsArea;
use Drupal\views\Plugin\views\area\AreaPluginBase;

/**
 * Total hours of the rows shown (use with an unpaged display).
 */
#[ViewsArea('liquid_timesheets_hours_total')]
class HoursTotal extends AreaPluginBase {

  /**
   * {@inheritdoc}
   */
  public function render($empty = FALSE): array {
    if ($empty && empty($this->options['empty'])) {
      return [];
    }
    $total = 0.0;
    foreach ($this->view->result as $row) {
      $total += (float) $row->_entity->get('hours')->value;
    }
    return [
      '#markup' => $this->t('<strong>Total hours: @total</strong> (@count entries)', [
        '@total' => number_format($total, 2),
        '@count' => count($this->view->result),
      ]),
    ];
  }

}
