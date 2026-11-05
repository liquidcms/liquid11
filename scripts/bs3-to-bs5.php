<?php

/**
 * @file
 * Converts Bootstrap 3 markup to Bootstrap 5.
 *
 * Breakpoints are shifted so layouts switch at the same widths as before:
 * BS3 xs/sm/md/lg (<768, 768, 992, 1200) = BS5 base/md/lg/xl.
 *
 * Used as a library (evolve_bs3_to_bs5()) by evolve-import-block-html.php,
 * or from the command line to rewrite files in place:
 *   ddev exec php scripts/bs3-to-bs5.php path/to/file.html.twig ...
 */

function evolve_bs3_to_bs5(string $html): string {
  // Class attributes: grid, visibility, helpers.
  $html = preg_replace_callback('/\bclass=(["\'])(.*?)\1/s', function ($m) {
    $classes = preg_split('/\s+/', trim($m[2]));
    $out = [];
    $hidden = [];
    $map = ['xs' => '', 'sm' => 'md', 'md' => 'lg', 'lg' => 'xl'];
    foreach ($classes as $class) {
      if (preg_match('/^col-(xs|sm|md|lg)-offset-(\d+)$/', $class, $c)) {
        $out[] = 'offset' . ($map[$c[1]] ? '-' . $map[$c[1]] : '') . '-' . $c[2];
      }
      elseif (preg_match('/^col-(xs|sm|md|lg)-(push|pull)-\d+$/', $class)) {
        // No BS5 equivalent; ordering is handled by flex order if ever needed.
        continue;
      }
      elseif (preg_match('/^col-(xs|sm|md|lg)-(\d+)$/', $class, $c)) {
        $out[] = 'col' . ($map[$c[1]] ? '-' . $map[$c[1]] : '') . '-' . $c[2];
      }
      elseif (preg_match('/^hidden-(xs|sm|md|lg)$/', $class, $c)) {
        $hidden[$c[1]] = TRUE;
      }
      else {
        $out[] = match ($class) {
          'pull-right' => 'float-end',
          'pull-left' => 'float-start',
          'img-responsive' => 'img-fluid',
          'center-block' => 'mx-auto d-block',
          'text-left' => 'text-start',
          'text-right' => 'text-end',
          'btn-default' => 'btn-light',
          'btn-xs' => 'btn-sm',
          default => $class,
        };
      }
    }
    if ($hidden) {
      // Express "hidden at these BS3 sizes" as BS5 display utilities.
      $sizes = ['xs' => '', 'sm' => 'md', 'md' => 'lg', 'lg' => 'xl'];
      $prev = NULL;
      foreach ($sizes as $bs3 => $bs5) {
        $state = empty($hidden[$bs3]) ? 'block' : 'none';
        if ($state !== $prev) {
          $out[] = 'd' . ($bs5 ? "-$bs5" : '') . "-$state";
          $prev = $state;
        }
      }
      // A leading d-block is the default; drop it.
      $out = array_values(array_diff($out, ['d-block']));
    }
    return 'class=' . $m[1] . implode(' ', array_unique($out)) . $m[1];
  }, $html);

  // Carousel parts.
  $html = preg_replace('/\bclass=(["\'])left carousel-control\1/', 'class=$1carousel-control-prev carousel-control left$1', $html);
  $html = preg_replace('/\bclass=(["\'])right carousel-control\1/', 'class=$1carousel-control-next carousel-control right$1', $html);
  $html = preg_replace('/\bclass=(["\'])item( |\1)/', 'class=$1carousel-item$2', $html);

  // Tabs: BS5 styles and activates .nav-link, not li > a.
  $html = preg_replace_callback('#<li( class="active")?>\s*<a ([^>]*data-(?:bs-)?toggle="tab"[^>]*)>#', function ($m) {
    $active = $m[1] ? ' active' : '';
    return '<li class="nav-item' . $active . '"><a class="nav-link' . $active . '" ' . $m[2] . '>';
  }, $html);

  // JS data attributes are namespaced in BS5.
  $html = preg_replace('/\bdata-(toggle|target|placement|ride|slide|slide-to|dismiss|parent|interval|content|trigger|container)=/', 'data-bs-$1=', $html);
  return $html;
}

if (PHP_SAPI === 'cli' && realpath($argv[0] ?? '') === __FILE__) {
  foreach (array_slice($argv, 1) as $file) {
    $before = file_get_contents($file);
    $after = evolve_bs3_to_bs5($before);
    if ($after !== $before) {
      file_put_contents($file, $after);
      echo "converted $file\n";
    }
  }
}
