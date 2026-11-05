<?php

namespace Drupal\evolve_views\Plugin\views\style;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\Attribute\ViewsStyle;
use Drupal\views\Plugin\views\style\StylePluginBase;

/**
 * Port of the D7 dexp_grid2 Views style (Bootstrap mode only).
 */
#[ViewsStyle(
  id: 'dexp_grid2',
  title: new TranslatableMarkup('Dexp Grid'),
  help: new TranslatableMarkup('Rows in a Bootstrap grid (Evolve theme).'),
  theme: 'views_view_dexp_grid',
  display_types: ['normal'],
)]
class DexpGrid extends StylePluginBase {

  protected $usesRowPlugin = TRUE;

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    foreach (['lg' => 4, 'md' => 4, 'sm' => 2, 'xs' => 1] as $size => $default) {
      $options["grid_cols_$size"] = ['default' => $default];
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    foreach (['lg', 'md', 'sm', 'xs'] as $size) {
      $form["grid_cols_$size"] = [
        '#type' => 'select',
        '#title' => $this->t('Columns (@size)', ['@size' => $size]),
        '#options' => [1 => 1, 2 => 2, 3 => 3, 4 => 4, 6 => 6, 12 => 12],
        '#default_value' => $this->options["grid_cols_$size"],
      ];
    }
  }

}
