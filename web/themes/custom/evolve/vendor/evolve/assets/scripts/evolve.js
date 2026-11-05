jQuery(document).ready(function($){
  $(".dtooltip").tooltip();
  $('span.menu-toggle').click(function(){
    $('body').toggleClass('menu-open');
  });
  $('.region-navigation').click(function(e){
     if($(e.target).hasClass('region-navigation')){
       $('body').removeClass('menu-open');
     }
  });
  $('.region-top').hide();
  $('.search-toggle').click(function(){
    $('.region-top').show().find('input[type=text]').focus();
    return false;
  });
  $('.search-close').click(function(){
    $('.region-top').hide();
    return false;
  });
  $('.dexp-fading').each(function(){
    var $this = $(this);
    var $slides = [];
    $this.find('> *').each(function(index){
      $(this).wrap('<div>');
      $slides[index] = $(this).parent().html();
      $(this).remove();
    });
    var counter = 1;
    $this.html($slides[0]).removeClass('fadeout');
    setInterval(function(){
      $this.addClass('fadeout');
      setTimeout(function(){
        $this.html($slides[counter=(counter+1)%$slides.length]).removeClass('fadeout');
      },500);
    },5000);
  });
});

