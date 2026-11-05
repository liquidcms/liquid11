<?php

namespace Drupal\evolve_views\Plugin\views\style;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Attribute\ViewsStyle;
use Drupal\views\Plugin\views\style\StylePluginBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Port of the D7 dexp_bxslider Views style: rows in a bxSlider carousel.
 */
#[ViewsStyle(
  id: 'dexp_bxslider',
  title: new TranslatableMarkup('Dexp bxSlider'),
  help: new TranslatableMarkup('Displays rows in a bxSlider carousel (Evolve theme).'),
  theme: 'views_view_dexp_bxslider',
  display_types: ['normal'],
)]
class DexpBxslider extends StylePluginBase {

  protected $usesRowPlugin = TRUE;

  protected $usesRowClass = TRUE;

  /**
   * bxSlider options, with the D7 defaults.
   */
  protected const SLIDER_OPTIONS = [
    'mode' => 'horizontal',
    'speed' => 500,
    'auto' => FALSE,
    'pause' => 4000,
    'infiniteloop' => TRUE,
    'slidemargin' => 0,
    'startslide' => 0,
    'randomstart' => FALSE,
    'pager' => FALSE,
    'controls' => TRUE,
    'minslides' => 1,
    'maxslides' => 1,
    'moveslides' => 1,
    'slidewidth' => 0,
    'prevtext' => '<i class="fa fa-angle-left"></i>',
    'nexttext' => '<i class="fa fa-angle-right"></i>',
    'rows' => 1,
  ];

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    foreach (static::SLIDER_OPTIONS as $key => $default) {
      $options[$key] = ['default' => $default];
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $form['mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Mode'),
      '#options' => ['horizontal' => 'horizontal', 'vertical' => 'vertical', 'fade' => 'fade'],
      '#default_value' => $this->options['mode'],
    ];
    foreach (['auto', 'infiniteloop', 'randomstart', 'pager', 'controls'] as $key) {
      $form[$key] = ['#type' => 'checkbox', '#title' => $key, '#default_value' => $this->options[$key]];
    }
    foreach (['speed', 'pause', 'slidemargin', 'startslide', 'minslides', 'maxslides', 'moveslides', 'slidewidth', 'rows'] as $key) {
      $form[$key] = ['#type' => 'number', '#title' => $key, '#default_value' => $this->options[$key]];
    }
    foreach (['prevtext', 'nexttext'] as $key) {
      $form[$key] = ['#type' => 'textfield', '#title' => $key, '#default_value' => $this->options[$key]];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $o = $this->options;
    $id = Html::getUniqueId($this->view->id() . '-' . $this->view->current_display);
    foreach ($build as &$group) {
      $group['#slider_id'] = $id;
    }
    unset($group);
    // Same settings shape as D7 so dexp_bxslider.js works unchanged.
    $build['#attached']['library'][] = 'evolve/global';
    $build['#attached']['drupalSettings']['dexpbxsliders'][$id] = [
      'mode' => $o['mode'],
      'speed' => (int) $o['speed'],
      'auto' => (bool) $o['auto'],
      'pause' => (int) $o['pause'],
      'infiniteLoop' => (bool) $o['infiniteloop'],
      'slideMargin' => (int) $o['slidemargin'],
      'startSlide' => (int) $o['startslide'],
      'randomStart' => (bool) $o['randomstart'],
      'pager' => (bool) $o['pager'],
      'controls' => (bool) $o['controls'],
      'minSlides' => (int) $o['minslides'],
      'maxSlides' => (int) $o['maxslides'],
      'moveSlides' => (int) $o['moveslides'],
      'slideWidth' => (int) $o['slidewidth'],
      'prevText' => $o['prevtext'],
      'nextText' => $o['nexttext'],
    ];
    return $build;
  }

}
