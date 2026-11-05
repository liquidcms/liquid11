<?php

/**
 * @file
 * Rebuilds the D7 Evolve views (Views do not migrate from D7).
 *
 * View and display ids match D7 so block ids and CSS hooks line up.
 * Re-runnable: each view is deleted and recreated.
 * Run: ddev drush php:script scripts/evolve-create-views.php
 */

use Drupal\views\Entity\View;

/**
 * Node filters: published + bundle.
 */
function evolve_node_filters(array $bundles): array {
  return [
    'status' => [
      'id' => 'status', 'table' => 'node_field_data', 'field' => 'status',
      'entity_type' => 'node', 'entity_field' => 'status', 'plugin_id' => 'boolean',
      'value' => '1', 'group' => 1,
    ],
    'type' => [
      'id' => 'type', 'table' => 'node_field_data', 'field' => 'type',
      'entity_type' => 'node', 'entity_field' => 'type', 'plugin_id' => 'bundle',
      'value' => array_combine($bundles, $bundles),
    ],
  ];
}

/**
 * Relationship to an entityqueue (replaces D7 nodequeue_rel).
 */
function evolve_queue_relationship(string $queue): array {
  return [
    'entityqueue_relationship' => [
      'id' => 'entityqueue_relationship', 'table' => 'node_field_data', 'field' => 'entityqueue_relationship',
      'plugin_id' => 'entity_queue', 'required' => TRUE, 'limit_queue' => [$queue],
    ],
  ];
}

function evolve_queue_sort(): array {
  return [
    'entityqueue_relationship' => [
      'id' => 'entityqueue_relationship', 'table' => 'node_field_data', 'field' => 'entityqueue_relationship',
      'relationship' => 'entityqueue_relationship', 'plugin_id' => 'entity_queue_position', 'order' => 'ASC',
    ],
  ];
}

function evolve_sort(string $field, string $order = 'DESC'): array {
  if ($field === 'random') {
    return ['random' => ['id' => 'random', 'table' => 'views', 'field' => 'random', 'plugin_id' => 'random']];
  }
  return [$field => [
    'id' => $field, 'table' => 'node_field_data', 'field' => $field, 'order' => $order,
    'entity_type' => 'node', 'entity_field' => $field, 'plugin_id' => 'date',
  ]];
}

function evolve_bxslider(array $o): array {
  return ['type' => 'dexp_bxslider', 'options' => $o + ['grouping' => [], 'row_class' => '', 'default_row_class' => FALSE]];
}

function evolve_node_row(string $view_mode = 'teaser'): array {
  return ['type' => 'entity:node', 'options' => ['view_mode' => $view_mode]];
}

function evolve_pager(string $type, int $items = 10): array {
  return ['type' => $type, 'options' => ['items_per_page' => $items, 'offset' => 0]];
}

function evolve_html_area(string $id, string $html): array {
  return [$id => [
    'id' => $id, 'table' => 'views', 'field' => 'area', 'plugin_id' => 'text',
    'empty' => FALSE, 'content' => ['value' => $html, 'format' => 'full_html'],
  ]];
}

/**
 * Display options for the 3-item title + date lists in the blog sidebar tabs.
 */
function evolve_recent_list(string $css_class, string $description): array {
  return [
    'css_class' => $css_class,
    'block_description' => $description,
    'pager' => evolve_pager('some', 3),
    'style' => ['type' => 'html_list', 'options' => ['type' => 'ul', 'grouping' => [], 'row_class' => '', 'default_row_class' => FALSE, 'wrapper_class' => 'item-list', 'class' => '']],
    'row' => ['type' => 'fields', 'options' => ['default_field_elements' => TRUE]],
    'fields' => [
      'title' => [
        'id' => 'title', 'table' => 'node_field_data', 'field' => 'title', 'plugin_id' => 'field',
        'entity_type' => 'node', 'entity_field' => 'title', 'label' => '', 'type' => 'string', 'settings' => ['link_to_entity' => TRUE],
      ],
      'created' => [
        'id' => 'created', 'table' => 'node_field_data', 'field' => 'created', 'plugin_id' => 'field',
        'entity_type' => 'node', 'entity_field' => 'created', 'label' => '', 'type' => 'timestamp',
        'element_wrapper_class' => 'clearfix', 'element_default_classes' => FALSE,
        'settings' => ['date_format' => 'custom', 'custom_date_format' => 'd M Y', 'timezone' => ''],
      ],
    ],
  ];
}

/**
 * Creates a view. $displays: [id => [plugin, title, options]].
 */
function evolve_create_view(string $id, string $label, string $base, array $default, array $displays): void {
  if ($existing = View::load($id)) {
    $existing->delete();
  }
  $all = ['default' => ['id' => 'default', 'display_plugin' => 'default', 'display_title' => 'Default', 'position' => 0, 'display_options' => $default + [
    'access' => ['type' => 'perm', 'options' => ['perm' => 'access content']],
    'cache' => ['type' => 'tag', 'options' => []],
    'query' => ['type' => 'views_query', 'options' => []],
    'exposed_form' => ['type' => 'basic', 'options' => []],
  ]]];
  $position = 1;
  foreach ($displays as $display_id => [$plugin, $title, $options]) {
    // Any option a display sets overrides the default display's value.
    foreach (array_keys($options) as $key) {
      if (!in_array($key, ['defaults', 'path', 'block_description', 'display_description'], TRUE)) {
        $options['defaults'][$key] = FALSE;
      }
    }
    $all[$display_id] = ['id' => $display_id, 'display_plugin' => $plugin, 'display_title' => $title, 'position' => $position++, 'display_options' => $options];
  }
  View::create(['id' => $id, 'label' => $label, 'base_table' => $base, 'base_field' => $base === 'node_field_data' ? 'nid' : 'tid', 'display' => $all])->save();
  echo "Created view $id\n";
}

// Cool Projects carousel (home) and Related Projects (portfolio pages).
evolve_create_view('latest_projects', 'Featured Projects', 'node_field_data', [
  'title' => 'Related Projects',
  'pager' => evolve_pager('some', 10),
  'style' => evolve_bxslider(['speed' => 500, 'auto' => FALSE, 'infiniteloop' => TRUE, 'slidemargin' => 20, 'controls' => TRUE, 'maxslides' => 6, 'slidewidth' => 200]),
  'row' => evolve_node_row('dexp_portfolio'),
  'filters' => evolve_node_filters(['dexp_portfolio']),
  'sorts' => evolve_sort('created'),
], [
  'block' => ['block', 'Featured Projects Home', [
    'css_class' => 'home-featured',
    'block_description' => 'Featured Projects',
    'defaults' => ['style' => FALSE, 'row' => FALSE, 'relationships' => FALSE, 'sorts' => FALSE],
    'style' => evolve_bxslider(['speed' => 500, 'auto' => FALSE, 'infiniteloop' => TRUE, 'slidemargin' => 20, 'controls' => TRUE, 'maxslides' => 4, 'slidewidth' => 200]),
    'row' => evolve_node_row('dexp_portfolio'),
    'relationships' => evolve_queue_relationship('cool_projects'),
    'sorts' => evolve_queue_sort(),
  ]],
  'block_1' => ['block', 'Related Projects', [
    'block_description' => 'Related Projects',
    'defaults' => ['style' => FALSE, 'arguments' => FALSE, 'sorts' => FALSE],
    // D7 had no sort here, so MySQL returned rows by nid.
    'sorts' => ['nid' => ['id' => 'nid', 'table' => 'node_field_data', 'field' => 'nid', 'plugin_id' => 'standard', 'order' => 'ASC', 'entity_type' => 'node', 'entity_field' => 'nid']],
    'style' => evolve_bxslider(['speed' => 500, 'auto' => FALSE, 'infiniteloop' => TRUE, 'slidemargin' => 20, 'controls' => FALSE, 'maxslides' => 4, 'slidewidth' => 250]),
    'arguments' => [
      'term_node_tid_depth' => [
        'id' => 'term_node_tid_depth', 'table' => 'node_field_data', 'field' => 'term_node_tid_depth', 'plugin_id' => 'taxonomy_index_tid_depth',
        'default_action' => 'default', 'default_argument_type' => 'taxonomy_tid',
        'default_argument_options' => ['term_page' => FALSE, 'node' => TRUE, 'limit' => TRUE, 'vids' => ['portfolio_category' => 'portfolio_category'], 'anyall' => ','],
        'depth' => 0, 'break_phrase' => TRUE,
      ],
      'nid' => [
        'id' => 'nid', 'table' => 'node_field_data', 'field' => 'nid', 'plugin_id' => 'node_nid',
        'entity_type' => 'node', 'entity_field' => 'nid',
        'default_action' => 'default', 'default_argument_type' => 'node', 'not' => TRUE,
      ],
    ],
  ]],
]);

// Portfolio grid page with category filter.
evolve_create_view('portfolio', 'Portfolio', 'node_field_data', [
  'title' => 'Portfolio',
  'pager' => ['type' => 'none', 'options' => ['offset' => 0]],
  'style' => ['type' => 'dexp_portfolio', 'options' => ['filter' => TRUE, 'filter_vocabulary' => 'portfolio_category', 'filter_field' => 'field_portfolio_categories', 'col_lg' => 3, 'col_md' => 3, 'col_sm' => 3, 'col_xs' => 1]],
  'row' => evolve_node_row('dexp_portfolio'),
  'filters' => evolve_node_filters(['dexp_portfolio']),
  'sorts' => evolve_sort('changed'),
], [
  'page' => ['page', '3 Columns Portfolio', ['path' => 'portfolio']],
]);

// Testimonials slider (home).
evolve_create_view('testimonial', 'Testimonial', 'node_field_data', [
  'title' => 'Testimonial',
  'pager' => evolve_pager('some', 5),
  'style' => evolve_bxslider(['speed' => 500, 'auto' => TRUE, 'pause' => 10000, 'infiniteloop' => TRUE, 'randomstart' => TRUE, 'controls' => FALSE, 'maxslides' => 1, 'slidewidth' => 1000]),
  'row' => evolve_node_row('teaser'),
  'filters' => evolve_node_filters(['testimonial']),
  'sorts' => evolve_sort('random'),
], [
  'block' => ['block', 'Home Page Testimonial', ['block_description' => 'Testimonial']],
]);

// Client logos carousel (home).
evolve_create_view('clients', 'Clients', 'node_field_data', [
  'title' => 'Clients',
  'pager' => evolve_pager('some', 10),
  'style' => evolve_bxslider(['speed' => 500, 'auto' => TRUE, 'pause' => 4000, 'infiniteloop' => TRUE, 'slidemargin' => 30, 'controls' => FALSE, 'maxslides' => 5, 'slidewidth' => 250]),
  'row' => ['type' => 'fields', 'options' => ['default_field_elements' => FALSE]],
  'fields' => ['field_image' => [
    'id' => 'field_image', 'table' => 'node__field_image', 'field' => 'field_image', 'plugin_id' => 'field',
    'label' => '', 'element_default_classes' => FALSE, 'type' => 'image', 'settings' => ['image_style' => 'logo', 'image_link' => ''],
  ]],
  'filters' => evolve_node_filters(['clients']),
  'sorts' => evolve_sort('created'),
], [
  'block' => ['block', 'Block', ['block_description' => 'Clients']],
]);

// Home page gallery slider.
evolve_create_view('gallery', 'Gallery', 'node_field_data', [
  'title' => 'Gallery',
  'pager' => evolve_pager('some', 5),
  'style' => evolve_bxslider(['mode' => 'fade', 'speed' => 500, 'auto' => TRUE, 'pause' => 5000, 'infiniteloop' => TRUE, 'randomstart' => TRUE, 'pager' => TRUE, 'controls' => TRUE, 'maxslides' => 1, 'slidewidth' => 500]),
  'row' => ['type' => 'fields', 'options' => ['default_field_elements' => FALSE]],
  'fields' => ['field_gallery_images' => [
    'id' => 'field_gallery_images', 'table' => 'node__field_gallery_images', 'field' => 'field_gallery_images', 'plugin_id' => 'field',
    'label' => '', 'element_default_classes' => FALSE, 'type' => 'image', 'settings' => ['image_style' => 'gallery', 'image_link' => ''],
    'group_rows' => FALSE, 'delta_limit' => 5, 'delta_offset' => 0,
  ]],
  'filters' => evolve_node_filters(['gallery']),
  'sorts' => evolve_sort('random'),
], [
  'block' => ['block', 'Block', ['block_description' => 'Home Page Gallery']],
]);

// Blog: /blog listing, home slider, footer "fresh off the blog".
evolve_create_view('blog', 'Blog', 'node_field_data', [
  'title' => 'Blog',
  'pager' => ['type' => 'full', 'options' => ['items_per_page' => 4, 'offset' => 0, 'quantity' => 9]],
  'style' => ['type' => 'default', 'options' => ['grouping' => [], 'row_class' => '', 'default_row_class' => FALSE]],
  'row' => evolve_node_row('teaser'),
  'filters' => evolve_node_filters(['article']),
  'sorts' => evolve_sort('created'),
], [
  'page' => ['page', 'Blog Post Medium', ['path' => 'blog']],
  'block_5' => ['block', 'Blog Slider Front', [
    'css_class' => 'slider-post',
    'block_description' => 'Blog Slider Front',
    'defaults' => ['pager' => FALSE, 'style' => FALSE, 'row' => FALSE, 'footer' => FALSE, 'filters' => FALSE, 'filter_groups' => FALSE],
    'pager' => evolve_pager('some', 10),
    'style' => evolve_bxslider(['speed' => 500, 'auto' => FALSE, 'infiniteloop' => TRUE, 'slidemargin' => 20, 'controls' => TRUE, 'maxslides' => 2, 'moveslides' => 2, 'slidewidth' => 250]),
    'row' => evolve_node_row('slider'),
    'footer' => evolve_html_area('area', '<div style="text-align:right;padding:20px 0 40px 0;"><a role="button" class="dexp-shortcodes-button btn button line-color nobg btn-link" href="/blog">View All Blog Posts</a></div>'),
    'filters' => evolve_node_filters(['article']) + [
      'field_categories_target_id' => [
        'id' => 'field_categories_target_id', 'table' => 'node__field_categories', 'field' => 'field_categories_target_id',
        'plugin_id' => 'taxonomy_index_tid', 'operator' => 'or', 'value' => [28 => 28, 36 => 36],
        'type' => 'select', 'vid' => 'blog_categories',
      ],
    ],
  ]],
  'block_3' => ['block', 'From The Blog', [
    'title' => 'FRESH OFF THE BLOG',
    'css_class' => 'refresh-blog',
    'block_description' => 'From The Blog',
    'defaults' => ['pager' => FALSE, 'style' => FALSE, 'row' => FALSE, 'fields' => FALSE, 'title' => FALSE],
    'pager' => evolve_pager('some', 2),
    'style' => ['type' => 'html_list', 'options' => ['type' => 'ul', 'grouping' => [], 'row_class' => '', 'default_row_class' => FALSE, 'wrapper_class' => 'item-list', 'class' => '']],
    'row' => ['type' => 'fields', 'options' => ['default_field_elements' => TRUE]],
    'fields' => [
      'field_resource' => [
        'id' => 'field_resource', 'table' => 'node__field_resource', 'field' => 'field_resource', 'plugin_id' => 'field',
        'label' => '', 'type' => 'file_url_plain', 'alter' => ['alter_text' => TRUE, 'text' => '<img src="{{ field_resource }}" width="60" height="60" alt="" />'],
      ],
      'title' => [
        'id' => 'title', 'table' => 'node_field_data', 'field' => 'title', 'plugin_id' => 'field',
        'entity_type' => 'node', 'entity_field' => 'title', 'label' => '', 'type' => 'string', 'settings' => ['link_to_entity' => TRUE],
      ],
      'created' => [
        'id' => 'created', 'table' => 'node_field_data', 'field' => 'created', 'plugin_id' => 'field',
        'entity_type' => 'node', 'entity_field' => 'created', 'label' => '', 'type' => 'timestamp',
        'settings' => ['date_format' => 'custom', 'custom_date_format' => 'd M Y', 'timezone' => ''],
      ],
    ],
  ]],
  // Blog sidebar "Recent" / "Popular" tabs (embedded by the theme in block 14).
  'block_1' => ['block', 'Recent Post', evolve_recent_list('recent-post', 'Recent Post')],
  // D7 sorted Popular by statistics hit count, but the statistics module was
  // not enabled there, so it fell back to the same newest-first list.
  'block_2' => ['block', 'Popular Post', evolve_recent_list('popular-post', 'Popular Post')],
]);

// Blog categories (blog sidebar) and tags (footer).
$term_name = ['name' => [
  'id' => 'name', 'table' => 'taxonomy_term_field_data', 'field' => 'name', 'plugin_id' => 'field',
  'entity_type' => 'taxonomy_term', 'entity_field' => 'name', 'label' => '', 'type' => 'string', 'settings' => ['link_to_entity' => TRUE],
]];
$vocab_filter = fn($vid) => ['vid' => [
  'id' => 'vid', 'table' => 'taxonomy_term_field_data', 'field' => 'vid', 'plugin_id' => 'bundle',
  'entity_type' => 'taxonomy_term', 'entity_field' => 'vid', 'value' => [$vid => $vid],
]];
evolve_create_view('blog_categories', 'Blog Categories', 'taxonomy_term_field_data', [
  'title' => 'Categories',
  'pager' => ['type' => 'none', 'options' => ['offset' => 0]],
  'style' => ['type' => 'html_list', 'options' => ['type' => 'ul', 'grouping' => [], 'row_class' => '', 'default_row_class' => FALSE, 'wrapper_class' => 'item-list', 'class' => '']],
  'row' => ['type' => 'fields', 'options' => ['default_field_elements' => FALSE]],
  'fields' => $term_name,
  'filters' => $vocab_filter('blog_categories'),
  'sorts' => ['weight' => ['id' => 'weight', 'table' => 'taxonomy_term_field_data', 'field' => 'weight', 'plugin_id' => 'standard', 'order' => 'ASC', 'entity_type' => 'taxonomy_term', 'entity_field' => 'weight']],
], [
  'block' => ['block', 'Categories', ['css_class' => 'categories', 'block_description' => 'Categories']],
  'block_1' => ['block', 'Tags', [
    'title' => 'Tags', 'css_class' => 'tags', 'block_description' => 'Tags',
    'defaults' => ['filters' => FALSE, 'filter_groups' => FALSE, 'title' => FALSE],
    'filters' => $vocab_filter('tags'),
  ]],
]);

// Team members (about page).
evolve_create_view('team', 'Team', 'node_field_data', [
  'title' => 'Team',
  'pager' => evolve_pager('some', 3),
  'style' => ['type' => 'dexp_grid2', 'options' => ['grid_cols_lg' => 3, 'grid_cols_md' => 3, 'grid_cols_sm' => 3, 'grid_cols_xs' => 1, 'grouping' => [], 'row_class' => '', 'default_row_class' => FALSE]],
  'row' => evolve_node_row('teaser'),
  'filters' => evolve_node_filters(['team']),
  'sorts' => evolve_sort('created'),
], [
  'block_2' => ['block', 'Our Team Members', [
    'block_description' => 'Our Team Members',
    'defaults' => ['relationships' => FALSE, 'sorts' => FALSE],
    'relationships' => evolve_queue_relationship('team'),
    'sorts' => evolve_queue_sort(),
  ]],
]);
