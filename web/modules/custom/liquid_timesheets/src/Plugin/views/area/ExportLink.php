<?php

declare(strict_types=1);

namespace Drupal\liquid_timesheets\Plugin\views\area;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\views\Attribute\ViewsArea;
use Drupal\views\Plugin\views\area\AreaPluginBase;

/**
 * A button to another display of this view, keeping the current filters.
 *
 * Used for the Xero CSV export, whose own feed icon is easy to miss.
 */
#[ViewsArea('liquid_timesheets_export_link')]
class ExportLink extends AreaPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions(): array {
    $options = parent::defineOptions();
    $options['display_id'] = ['default' => ''];
    $options['label'] = ['default' => 'Download CSV'];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state): void {
    parent::buildOptionsForm($form, $form_state);
    $form['display_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Display ID to link to'),
      '#default_value' => $this->options['display_id'],
    ];
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Button text'),
      '#default_value' => $this->options['label'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function render($empty = FALSE): array {
    if ($empty && empty($this->options['empty'])) {
      return [];
    }
    $route = 'view.' . $this->view->id() . '.' . $this->options['display_id'];
    $query = $this->view->getExposedInput();
    unset($query['page']);
    return [
      '#type' => 'link',
      '#title' => $this->options['label'],
      '#url' => Url::fromRoute($route, [], ['query' => $query]),
      '#attributes' => ['class' => ['button', 'button--small']],
      '#cache' => ['contexts' => ['url.query_args']],
    ];
  }

}
