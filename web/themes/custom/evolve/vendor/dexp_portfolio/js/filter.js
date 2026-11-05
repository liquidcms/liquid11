(function($){
  // Portfolio category filter (from D7 dexp_portfolio), initialised once per
  // grid: D11 re-attaches behaviors, which used to bind the filter repeatedly.
  Drupal.behaviors.dexp_portfolio = {
    attach: function(context,settings) {
      $.each(settings.dexp_portfolio || {}, function(filter, view){
        $(once('dexp-portfolio', '#'+view, context)).each(function(){
          var $grid = $(this), $filter = $('#'+filter);
          $grid.shuffle({
            itemSelector: '.dexp-portfolio-item',
            speed: 500
          });
          $filter.find('a').click(function(){
            var $this = $(this), filter = $this.data('filter');
            if(filter == '*'){
              $grid.shuffle('shuffle', 'all');
            }else{
              $grid.shuffle('shuffle', function($el, shuffle) {
                // Only search elements in the current group
                if (shuffle.group !== 'all' && $.inArray(shuffle.group, $el.data('groups')) === -1) {
                  return false;
                }
                return $el.hasClass(filter);
              });
            }
            $(this).parents('.dexp-portfolio-filter').find('a').removeClass('active');
            $(this).addClass('active');
            return false;
          });
          $grid.shuffle('shuffle', 'all');
          $(window).on('load', function(){
            $grid.shuffle('update');
          });
        });
      });
    }
  }
})(jQuery);
