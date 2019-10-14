<?php
function get_custom_cat_template($single_template) {
   global $post;
   if ( in_category( 'movies' )) {
      $single_template = dirname( __FILE__ ) . '/single-movies.php';
   }
   return $single_template;
}
add_filter( "single_template", "get_custom_cat_template" ) ;

============================================================

add_filter('single_template', 'blog_post_cusom_template');

function blog_post_cusom_template($single) {
global $wp_query, $post;
define(SINGLE_PATH, TEMPLATEPATH . '/single');

if (has_category('Blog')){
  if(file_exists(SINGLE_PATH . '/single-blog.php')){ return SINGLE_PATH . '/single-blog.php'; }
}

}
?>