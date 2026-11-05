/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

jQuery(document).ready(function(){
    // Set guide line for using Accordion 
    jQuery("#accordion_code").html('[accordions class="STYLE"]<br/>[accordion title="TITLE_ITEM_1"]Content Item 1 here[/accordion]<br/>[accordion title="TITLE_ITEM_2"]Content Item 2 here[/accordion]<br/>[accordion title="TITLE_ITEM_3"]Content Item 3 here[/accordion]<br/>[accordion title="TITLE_ITEM_4"]Content Item 4 here[/accordion]<br/>[/accordions]<br/>NOTE: STYLE can be question (Style 01) or default (style 02) or arrow (style 03) or question_answer (style 04).<br/> Missing class attribute on accordions tag it will be default').wrap( "<pre></pre>");
    
    // Set guide line for using Tab
    jQuery("#tab_code").html('[tabs class="STYLE"]<br/>[tab title="TITLE_ITEM_1" icon="ICON_NAME_ITEM_1"]Content Item 1 here[/tab]<br/>[tab title="TITLE_ITEM_2" icon="ICON_NAME_ITEM_2"]Content Item 2 here[/tab]<br/>[tab title="TITLE_ITEM_3" icon="ICON_NAME_ITEM_3"]Content Item 3 here[/tab]<br/>[/tabs]<br/>NOTE: STYLE can be or default (vertical) (style 01 and 02) or horizontal (style 03 and 04).').wrap( "<pre></pre>" );
    
    // Set guide line for using Button
    jQuery("#button-code").html('[button size="SIZE" bg="BACKGROUND_COLOR" type="TYPE" color="TEXT_COLOR" url="LINK_TO"][/button]<br/>NOTE: SIZE can be large, small, mini or missing will be default').wrap( "<pre></pre>" );
    
    // Set guide line for using Icon
    jQuery("#icon-code").html('[icon name="NAME" link="URL"]TEXT_HERE[/icon]<br/> NAME is a valid class name in font owesome').wrap( "<pre></pre>" );
    
    // Set guide line for using Testimonial
    jQuery("#testimonial_code").html('[testimonials class="STYLE"]<br/>[testimonial author="AUTHOR_NAME_1" position="AUTHOR_POSITION_1" avatar="PATH_TO_AVATAR_1"] Your Testimonial 1 here [/testimonial]<br/>[testimonial author="AUTHOR_NAME_2" position="AUTHOR_POSITION_2" avatar="PATH_TO_AVATAR_2"] Your Testimonial 2 here [/testimonial]<br/>[/testimonials]<br/>NOTE: STYLE can be style01, style02, style03, style04, style05, style06, style07, style08, style09, style10, style11, style12<br/>Style01 will be default if property <b>class</b> missing or invalid value').wrap( "<pre></pre>" );
    
    // Set guide line for using Progress bar
    jQuery("#progress_code").html('[progress percent="PERCENT"]TITLE_CONTENT_HERE[/progress]<br/>[progress percent="PERCENT"]TITLE_CONTENT_HERE[/progress]<br/>[progress percent="PERCENT"]TITLE_CONTENT_HERE[/progress]').wrap( "<pre></pre>");
    
    // Set guide line for using column
    jQuery("#column_code").html('[column cols="I" begin="0-OR-1" end="0-OR-1"]CONTENT HERE [/column]<br/>[column cols="I" begin="0-OR-1" end="0-OR-1"]CONTENT HERE [/column]<br/>NOTE: Property begin = "1" indicates that it is the first column layout, property end = "1" indicates that it is the last column layout<br/>Example:<br/> - Shortcode of 2 columns (width = 1/2): <br/>[column cols="6" begin="1"]CONTENT HERE [/column][column cols="6" end="1"]CONTENT HERE [/column]<br/>- Shortcode of 3 columns (width = 1/3): <br/>[column cols="4" begin="1"]CONTENT HERE [/column][column cols="4"]CONTENT HERE [/column][column cols="4" end="1"]CONTENT HERE [/column]<br/>- Shortcode of 4 columns (width = 1/4): <br/>[column cols="3" begin="1"]CONTENT HERE [/column][column cols="3"]CONTENT HERE [/column][column cols="3"]CONTENT HERE [/column][column cols="3" end="1"]CONTENT HERE [/column]<br/> - Shortcode of 2 columns (width = 1/4 and 3/4): <br/>[column cols="3" begin="1"]CONTENT HERE [/column][column cols="9" end="1"]CONTENT HERE [/column]').wrap( "<pre></pre>");
    
     // Set guide line for using Carousel
    jQuery("#carousel_code").html('[carousels][carousel path="PATH_TO_IMAGE"]CAPTION_HERE[/carousel][carousel path="PATH_TO_IMAGE"]CAPTION_HERE[/carousel][carousel path="PATH_TO_IMAGE"]CAPTION_HERE[/carousel][/carousels]').wrap( "<pre></pre>");
});

