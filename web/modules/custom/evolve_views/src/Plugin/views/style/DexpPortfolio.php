<?php

namespace Drupal\evolve_views\Plugin\views\style;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\Attribute\ViewsStyle;
use Drupal\views\Plugin\views\style\StylePluginBase;

/**
 * Port of the D7 dexp_portfolio Views style: filterable Bootstrap grid.
 */
#[ViewsStyle(
  id: 'dexp_portfolio',
  title: new TranslatableMarkup('Dexp Portfolio'),
  help: new TranslatableMarkup('Bootstrap grid with optional category filter (Evolve theme).'),
  theme: 'views_view_dexp_portfolio',
  display_types: ['normal'],
)]
class DexpPortfolio extends StylePluginBase {

  protected $usesRowPlugin = TRUE;

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['filter'] = ['default' => FALSE];
    $options['filter_vocabulary'] = ['default' => ''];
    $options['filter_field'] = ['default' => ''];
    $options['col_lg'] = ['default' => 3];
    $options['col_md'] = ['default' => 3];
    $options['col_sm'] = ['default' => 2];
    $options['col_xs'] = ['default' => 1];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $cols = [1 => 1, 2 => 2, 3 => 3, 4 => 4, 6 => 6, 12 => 12];
    foreach (['lg' => 'Large desktop', 'md' => 'Desktop', 'sm' => 'Tablet', 'xs' => 'Phone'] as $size => $label) {
      $form["col_$size"] = [
        '#type' => 'select',
        '#title' => $this->t('@label columns', ['@label' => $label]),
        '#options' => $cols,
        '#default_value' => $this->options["col_$size"],
      ];
    }
    $form['filter'] = ['#type' => 'checkbox', '#title' => $this->t('Use filter'), '#default_value' => $this->options['filter']];
    $form['filter_vocabulary'] = ['#type' => 'textfield', '#title' => $this->t('Filter vocabulary id'), '#default_value' => $this->options['filter_vocabulary']];
    $form['filter_field'] = ['#type' => 'textfield', '#title' => $this->t('Term reference field on the node'), '#default_value' => $this->options['filter_field']];
  }

  /**
   * Same class munging as D7 _dexp_portfolio_safe_class().
   */
  public static function safeClass(string $name): string {
    return preg_replace('/\W+/', '', strtolower(strip_tags($name)));
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $o = $this->options;
    $col_classes = sprintf('col-xl-%d col-lg-%d col-md-%d col-%d', 12 / $o['col_lg'], 12 / $o['col_md'], 12 / $o['col_sm'], 12 / $o['col_xs']);
    $view_id = Html::getClass($this->view->id() . '-' . $this->view->current_display);
    $filter_id = Html::getUniqueId('dexp_portfolio_filter');

    $rows = [];
    foreach ($this->view->result as $index => $row) {
      $render = $this->view->rowPlugin->render($row);
      $classes = explode(' ', $col_classes);
      if ($o['filter_field'] && $row->_entity && $row->_entity->hasField($o['filter_field'])) {
        foreach ($row->_entity->get($o['filter_field'])->referencedEntities() as $term) {
          $classes[] = static::safeClass($term->label());
        }
      }
      // Printed on a wrapper div by views-view-dexp-portfolio.html.twig.
      $render['#evolve_node_classes'] = $classes;
      $rows[$index] = $render;
    }

    $categories = [];
    if ($o['filter'] && $o['filter_vocabulary']) {
      $tree = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($o['filter_vocabulary'], 0, 1);
      foreach ($tree as $term) {
        $categories[static::safeClass($term->name)] = $term->name;
      }
    }

    $build = [
      '#theme' => $this->themeFunctions(),
      '#view' => $this->view,
      '#options' => $o,
      '#rows' => $rows,
      '#view_id' => $view_id,
      '#filter_id' => $filter_id,
      '#categories' => $categories,
    ];
    $build['#attached']['library'][] = 'evolve/global';
    if ($categories) {
      $build['#attached']['library'][] = 'evolve/portfolio';
      $build['#attached']['drupalSettings']['dexp_portfolio'][$filter_id] = $view_id;
    }
    return $build;
  }

}
