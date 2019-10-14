<?php
/***** for custom post type blog *****/
register_post_type('blog', array(
'label' => 'Blog',
'description' => '',
'public' => true,
'show_ui' => true,
'show_in_menu' => true,
'capability_type' => 'post',
'map_meta_cap' => true,
'hierarchical' => false,
'rewrite' => array('slug' => 'blog', 'with_front' => true),
'query_var' => true,
'supports' => array('title','editor','excerpt','custom-fields','comments','revisions','thumbnail','author','page-attributes','post-formats'),
'taxonomies' => array('blog-category', 'blog-tag' ),
'labels' => array (
  'name' => 'Blog Post',
  'singular_name' => 'Blog Post',
  'menu_name' => 'Blog',
  'add_new' => 'Add Blog Post',
  'add_new_item' => 'Add New Blog Post',
  'edit' => 'Edit',
  'edit_item' => 'Edit Blog Post',
  'new_item' => 'New Blog Post',
  'view' => 'View Blog Post',
  'view_item' => 'View Blog Post',
  'search_items' => 'Search Blog Post',
  'not_found' => 'No Blog Post Found',
  'not_found_in_trash' => 'No Blog Post Found in Trash',
  'parent' => 'Parent Blog',
)
) );

/*add_filter( 'manage_edit-blog_columns', 'my_edit_blog_columns' ) ;
function my_edit_blog_columns( $columns ) {
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __( 'Blog' ),
		'ecerpt' => __( 'Desciption' ),
		'featured_image' => __( 'Thumbnail' ),
		'date' => __( 'Date' )
	);
	return $columns;
}

add_filter( 'manage_edit-blog_sortable_columns', 'my_blog_sortable_columns' );
function my_blog_sortable_columns( $columns ) {
	$columns['title'] = 'Blog';
	return $columns;
}

function my_custom_featured_image_column_image( $image ) {
    if ( !has_post_thumbnail() )
        return trailingslashit( get_stylesheet_directory_uri() ) . 'images/no-featured-image';
    else
    the_thumbnail();
}
add_filter( 'featured_image_column_default_image', 'my_custom_featured_image_column_image' );*/

function add_thumbnail_to_post_list_data($column,$post_id)
{
    switch ($column)
    {
        case 'post_thumbnail':
            echo '<a href="' . get_edit_post_link() . '">'.the_post_thumbnail( 'thumbnail' ).'</a>';
            break;
    }
}
function add_thumbnail_to_post_list( $columns )
{
    $columns['post_thumbnail'] = 'Thumbnail';
    return $columns;
}

if (function_exists('add_theme_support'))
{
    // Add To Posts
    add_filter( 'manage_posts_columns' , 'add_thumbnail_to_post_list' );
    add_action( 'manage_posts_custom_column' , 'add_thumbnail_to_post_list_data', 10, 2 );
}


register_taxonomy('blog-category', 'blog', array(
// Hierarchical taxonomy (like categories)
'hierarchical' => true,
// This array of options controls the labels displayed in the WordPress Admin UI
'labels' => array(
  'name' => _x( 'Category', 'taxonomy general name' ),
  'singular_name' => _x( 'Category', 'taxonomy singular name' ),
  'search_items' =>  __( 'Search Categories' ),
  'all_items' => __( 'All Categories' ),
  'parent_item' => __( 'Parent Blog-Category' ),
  'parent_item_colon' => __( 'Parent Blog-Category:' ),
  'edit_item' => __( 'Edit Category' ),
  'update_item' => __( 'Update Category' ),
  'add_new_item' => __( 'Add New Category' ),
  'new_item_name' => __( 'New Category Name' ),
  'menu_name' => __( 'Categories' ),
),

// Control the slugs used for this taxonomy
'rewrite' => array(
  'slug' => 'blog-category', // This controls the base slug that will display before each term
  'with_front' => false, // Don't display the category base before "/locations/"
  'hierarchical' => true // This will allow URL's like "/locations/boston/cambridge/"
),
));

function custom_remove_cpt_slug( $post_link, $post, $leavename ) {
if ( 'blog' != $post->post_type || 'publish' != $post->post_status ) {
  return $post_link;
}
$post_link = str_replace( '/' . $post->post_type . '/', '/', $post_link );
  return $post_link;
}
add_filter( 'post_type_link', 'custom_remove_cpt_slug', 10, 3 );

function custom_parse_request_tricksy( $query ) {
// Only noop the main query
if ( ! $query->is_main_query() )
  return;

// Only noop our very specific rewrite rule match
if ( 2 != count( $query->query ) || ! isset( $query->query['page'] ) ) {
  return;
}

// 'name' will be set if post permalinks are just post_name, otherwise the page rule will match
if ( ! empty( $query->query['name'] ) ) {
  $query->set( 'post_type', array( 'blog' ) );
}
}
add_action( 'pre_get_posts', 'custom_parse_request_tricksy' );

/*function reg_tag() {
     register_taxonomy_for_object_type('post_tag', 'blog');
}
add_action('init', 'reg_tag');*/
/***** for custom post type blog *****/


/***** custom post template for blog *****/
add_filter('single_template', 'blog_post_cusom_template');
function blog_post_cusom_template($single) {
define(SINGLE_PATH, TEMPLATEPATH . '/');

  //if (has_category('Uncategorized')){
    if(file_exists(SINGLE_PATH . '/single-blog.php')){ return SINGLE_PATH . '/single-blog.php'; }
  //}

}
/***** custom post template for blog *****/

add_action( 'add_meta_boxes', 'blog_meta_box_add' );
function blog_meta_box_add() {
$args=array( 'public' => true, 'exclude_from_search' => false );
$output = 'names'; // names or objects, note names is the default
$operator = 'and'; // 'and' or 'or'
$post_types = get_post_types($args,$output,$operator);
foreach ($post_types  as $post_type ) {
  if($post_type == "blog"){
  add_meta_box( 'blog-meta-box-id', 'Custom Content', 'blog_meta_box_cb', $post_type, 'normal', 'high' );
  }else{}
}

}
function blog_meta_box_cb( $post ) {
    $values = get_post_custom( $post->ID );
    $pagetitle = isset( $values['blog_pagetitle'] ) ? esc_attr( $values['blog_pagetitle'][0] ) : '';
    $pagedesc = isset( $values['blog_metadesc'] ) ? esc_attr( $values['blog_metadesc'][0] ) : '';
    $content = get_post_meta($post->ID, 'custom_editor', true);
    $author_name = get_post_meta($post->ID, 'author_name', true);
    $saved = get_post_meta( $post->ID, 'myplugin_media_id', true );
    wp_nonce_field( 'blog_meta_box_nonce', 'meta_box_nonce' );


echo '<p><label for="custom_editor"><strong>Add Your Content Here</strong></label><br />'."\n";
//wp_editor ( $content , 'custom_editor',array ( 'media_buttons' => true ) );
wp_editor ( htmlspecialchars_decode($content), 'custom_editor', array("media_buttons" => false) );
echo '<span class="custom_editor">Add your content here</span></p>'."\n";

echo '<p><label for="custom_editor"><strong>Add Author Name</strong></label>'."\n";
echo '<input type="text" name="author_name" id="author_name" value="'. $author_name .'" placeholder="'. $author_name .'" style="width:100%;" /><br />'."\n";
echo '<span class="description">Add your content here</span></p>'."\n";

/*echo '<p><label for="blog_pagetitle"><strong>Page Title: </strong></label><br />'."\n";
echo '<input type="text" name="blog_pagetitle" id="blog_pagetitle" value="'. $pagetitle .'" placeholder="'. $pagetitle .'" style="width:100%;" />'."\n";
echo '<span class="description">Add browser title here</span></p>'."\n";
echo '<p><label for="blog_metadesc"><strong>Meta Description: </strong></label><br />'."\n";
echo '<textarea name="blog_metadesc" id="blog_metadesc" value="'. $pagedesc .'" placeholder="'. $pagedesc .'" style="width:100%;">'. $pagedesc .'</textarea>'."\n";
echo '<span class="description">Add meta description here</span></p>'."\n";
echo '<br /><span class="description">Allow or Disallow page in meta robots</span></p>'."\n";*/

/*$args = array('rating'=>3.5, 'type'=>'rating', 'number'=>1234,);
wp_star_rating($args);*/
}

add_action( 'save_post', 'blog_meta_box_save' );
function blog_meta_box_save( $post_id ) {
    //cheking for auto save
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    //verify for nonce field
    if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'blog_meta_box_nonce' ) ) return;
    //checcking current user privilege to editing
    if( !current_user_can( 'edit_post', $post_id ) ) return;

    //saving datas for custom_editor
    if( isset( $_POST['custom_editor'] ) )
        $data=htmlspecialchars($_POST['custom_editor']);
        update_post_meta( $post_id, 'custom_editor', $data );

    //saving datas for author_nameeditor
    if( isset( $_POST['author_name'] ) )
        update_post_meta( $post_id, 'author_name', sanitize_text_field( $_POST['author_name'] ) );

    // Make sure the file array isn't empty
    if(!empty($_FILES['wp_custom_attachment']['name'])) {

        // Setup the array of supported file types. In this case, it's just PDF.
        $supported_types = array('application/jpg');

        // Get the file type of the upload
        $arr_file_type = wp_check_filetype(basename($_FILES['wp_custom_attachment']['name']));
        $uploaded_type = $arr_file_type['type'];

        // Check if the type is supported. If not, throw an error.
        if(in_array($uploaded_type, $supported_types)) {

            // Use the WordPress API to upload the file
            $upload = wp_upload_bits($_FILES['wp_custom_attachment']['name'], null, file_get_contents($_FILES['wp_custom_attachment']['tmp_name']));

            if(isset($upload['error']) && $upload['error'] != 0) {
                wp_die('There was an error uploading your file. The error is: ' . $upload['error']);
            } else {
                add_post_meta($id, 'wp_custom_attachment', $upload);
                update_post_meta($id, 'wp_custom_attachment', $upload);
            } // end if/else

        } else {
            wp_die("The file type that you've uploaded is not a PDF.");
        } // end if/else

    } // end if

    //saving datas for pagetitle
    /*if( isset( $_POST['blog_pagetitle'] ) )
        update_post_meta( $post_id, 'blog_pagetitle', sanitize_text_field( $_POST['blog_pagetitle'] ) );*/

    //saving datas for meta description
    /*if( isset( $_POST['blog_metadesc'] ) )
        update_post_meta( $post_id, 'blog_metadesc', sanitize_text_field( $_POST['seo_metadesc'] ) );*/
}

/*******************************/
//register taxonomy for custom post tags
register_taxonomy(
'blog-tag', //taxonomy
'blog', //post-type
array(
    'hierarchical'  => false,
    'label'         => __( 'My Custom Tags','taxonomy general name'),
    'singular_name' => __( 'Tag', 'taxonomy general name' ),
    'rewrite'       => true,
    'query_var'     => true
));

add_action('init', '_question_register_post_type');
function _question_register_post_type(){
    //register taxonomy for custom post tags
    register_taxonomy(
    'blog-tag', //taxonomy
    'blog', //post-type
    array(
        'hierarchical'  => false,
        'label'         => __( 'Tags','taxonomy general name'),
        'singular_name' => __( 'Tag', 'taxonomy general name' ),
        'rewrite'       => true,
        'query_var'     => true
    ));
}

/*******************************************************************/
add_shortcode( 'wpshout_frontend_post', 'wpshout_frontend_post' );
function wpshout_frontend_post() {
?>
<div id="postbox">
<form id="new_post" name="new_post" method="post">

<p><label for="title">Title</label><br />
<input type="text" id="title" value="" tabindex="1" size="20" name="title" style="width:50%;" /></p>

<p><label for="content">Post Content</label><br />
<textarea id="content" tabindex="3" name="content" cols="50" rows="6" style="width:50%;"></textarea></p>

<p><label for="content">Category</label><br />
<?php wp_dropdown_categories( 'show_option_none=Category&tab_index=1&taxonomy=blog-category&selected=21&show_option_none=Select Category' ); ?></p>

<p><label for="post_tags">Tags</label><br />
<input type="text" value="" tabindex="5" size="16" name="post_tags" id="post_tags" style="width:20%;" /></p>

<p><label for="post_tags">Image</label><br />
<input type="file" id="wp_custom_attachment" name="wp_custom_attachment" value="" size="25" /></p>

<?php //wp_editor ( htmlspecialchars_decode($content1), 'wp_custom_attachment', array("media_buttons" => true) ); ?>

<?php wp_nonce_field( 'wps-frontend-post' ); ?>

<input type="hidden" name="action" value="saveconfiguration">
<p align="right"><input type="submit" class="button button-primary" value="Publish" tabindex="6" id="submit" name="submit" /></p>

</form>
</div>
<?php
//add_action( 'save_post', 'wpshout_save_post_if_submitted' );
function wpshout_save_post_if_submitted() {
    // Stop running function if form wasn't submitted
    if ( !isset($_POST['title']) ) {
        return;
    }

    // Check that the nonce was set and valid
    if( !wp_verify_nonce($_POST['_wpnonce'], 'wps-frontend-post') ) {
        echo 'Did not save because your form seemed to be invalid. Sorry';
        return;
    }

    // Do some minor form validation to make sure there is content
    if (strlen($_POST['title']) < 3) {
        echo 'Please enter a title. Titles must be at least three characters long.';
        return;
    }
    if (strlen($_POST['content']) < 100) {
        echo 'Please enter content more than 100 characters in length';
        return;
    }

    // Add the content of the form to $post as an array
    $post = array(
        'post_title'    => wp_strip_all_tags($_POST['title']),
        'post_content'  => $_POST['content'],
        'post_category' => array($_POST['cat']),
        'tags_input'    => array($_POST['post_tags']),
        'tax_input'     => array( 'blog-category'=>$_POST['cat'], 'blog-tag'=>$_POST['post_tags'] ),
        'post_status'   => 'draft',
        'post_type' 	=> 'blog',
    );
    $postid = wp_insert_post($post);

    echo 'Saved your post successfully! :)';
}
wpshout_save_post_if_submitted();
}

/******************************************************************/


/***** Call to actions *****/
function call_to_action_main( $atts ) {
	$atts = shortcode_atts( array(
		'city_name' => 'Temecula'
	), $atts, 'bartag' );

	return '
<div class="city-list">
<h3 style="color:#FB8103;"> Don\'t Waste Time; Call On Time Today!</h3>
<p><strong>Call <a href="tel:951-474-0636">(951) 474-0636</a> or schedule an in-home consultation appointment online.</strong></p>
</div>
';
}
add_shortcode( 'cta_main', 'call_to_action_main' );
/*** use do_shortcode() method to print ***/
/***** Call to actions *****/

/****************************************************************/
/********************** single-blog.php *************************/
/****************************************************************/
$content = get_post_meta($post->ID, 'custom_editor', true);
$author_name = get_post_meta($post->ID, 'author_name', true);

$content = htmlspecialchars_decode($content);
$content = wpautop( $content );

echo '<div>'.$content.'</div>';
echo '<p>'.$author_name.'</p>';

echo '<p>';
  $category = get_the_terms( $post->ID, 'blog-category' );
  if (is_array($category) || is_object($category))
  {
    foreach ( $category as $cat){
      $cat_slur = esc_url( get_term_link( $cat->slug, 'blog-category' ) );
      echo 'Category: <a href="'.$cat_slur.'">'.$cat->name. '</a>';
    }
  }
echo '</p>';

echo '<p>';
  $tags = get_the_terms( $post->ID, 'blog-tag' );
  if (is_array($tags) || is_object($tags))
  {
    foreach ( $tags as $tag){
      $tag_slur = esc_url( get_term_link( $tag->slug, 'blog-tag' ) );
      echo 'Category: <a href="'.$tag_slur.'">'.$tag->name. '</a>';
    }
  }
echo '</p>';
/****************************************************************/
/********************** single-blog.php *************************/
/****************************************************************/
?>

<?php
/***** for custom post type blog with category & tags *****/
register_post_type('blog', array(
'label' => 'Blog',
'description' => '',
'public' => true,
'show_ui' => true,
'show_in_menu' => true,
'capability_type' => 'post',
'map_meta_cap' => true,
'hierarchical' => false,
'rewrite' => array('slug' => 'blog', 'with_front' => true),
'query_var' => true,
'supports' => array('title','editor','excerpt','custom-fields','comments','revisions','thumbnail','author','page-attributes','post-formats'),
'taxonomies' => array('blog-category', 'blog-tag' ),
'labels' => array (
  'name' => 'Blog Post',
  'singular_name' => 'Blog Post',
  'menu_name' => 'Blog',
  'add_new' => 'Add Blog Post',
  'add_new_item' => 'Add New Blog Post',
  'edit' => 'Edit',
  'edit_item' => 'Edit Blog Post',
  'new_item' => 'New Blog Post',
  'view' => 'View Blog Post',
  'view_item' => 'View Blog Post',
  'search_items' => 'Search Blog Post',
  'not_found' => 'No Blog Post Found',
  'not_found_in_trash' => 'No Blog Post Found in Trash',
  'parent' => 'Parent Blog',
)
) );

register_taxonomy('blog-category', 'blog', array(
// Hierarchical taxonomy (like categories)
'hierarchical' => true,
// This array of options controls the labels displayed in the WordPress Admin UI
'labels' => array(
  'name' => _x( 'Category', 'taxonomy general name' ),
  'singular_name' => _x( 'Category', 'taxonomy singular name' ),
  'search_items' =>  __( 'Search Categories' ),
  'all_items' => __( 'All Categories' ),
  'parent_item' => __( 'Parent Blog-Category' ),
  'parent_item_colon' => __( 'Parent Blog-Category:' ),
  'edit_item' => __( 'Edit Category' ),
  'update_item' => __( 'Update Category' ),
  'add_new_item' => __( 'Add New Category' ),
  'new_item_name' => __( 'New Category Name' ),
  'menu_name' => __( 'Categories' ),
),

// Control the slugs used for this taxonomy
'rewrite' => array(
  'slug' => 'blog-category', // This controls the base slug that will display before each term
  'with_front' => false, // Don't display the category base before "/locations/"
  'hierarchical' => true // This will allow URL's like "/locations/boston/cambridge/"
),
));

register_taxonomy(
'blog-tag', //taxonomy
'blog', //post-type
array(
    'hierarchical'  => false,
    'label'         => __( 'Tags','taxonomy general name'),
    'singular_name' => __( 'Tag', 'taxonomy general name' ),
    'rewrite'       => true,
    'query_var'     => true
));

/***** removing /blog/ to custom blog post type ******/
function custom_remove_cpt_slug( $post_link, $post, $leavename ) {
if ( 'blog' != $post->post_type || 'publish' != $post->post_status ) {
  return $post_link;
}else{
$post_link = str_replace( '/' . $post->post_type . '/', '/', $post_link );
}
  return $post_link;
}
add_filter( 'post_type_link', 'custom_remove_cpt_slug', 10, 3 );

function custom_parse_request_tricksy( $query ) {
// Only noop the main query
if ( ! $query->is_main_query() )
  return;

// Only noop our very specific rewrite rule match
if ( 2 != count( $query->query ) || ! isset( $query->query['page'] ) ) {
  return;
}

// 'name' will be set if post permalinks are just post_name, otherwise the page rule will match
if ( ! empty( $query->query['name'] ) ) {
  $query->set( 'post_type', array( 'blog' ) );
}
}
add_action( 'pre_get_posts', 'custom_parse_request_tricksy' );
/***** removing /blog/ to custom blog post type ******/

/*add_filter( 'manage_edit-blog_columns', 'my_edit_blog_columns' ) ;
function my_edit_blog_columns( $columns ) {
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __( 'Blog' ),
		'ecerpt' => __( 'Desciption' ),
		'featured_image' => __( 'Thumbnail' ),
		'date' => __( 'Date' )
	);
	return $columns;
}

add_filter( 'manage_edit-blog_sortable_columns', 'my_blog_sortable_columns' );
function my_blog_sortable_columns( $columns ) {
	$columns['title'] = 'Blog';
	return $columns;
}

function my_custom_featured_image_column_image( $image ) {
    if ( !has_post_thumbnail() )
        return trailingslashit( get_stylesheet_directory_uri() ) . 'images/no-featured-image';
    else
    the_thumbnail();
}
add_filter( 'featured_image_column_default_image', 'my_custom_featured_image_column_image' );*/

/*****/
/*function add_thumbnail_to_post_list_data($column,$post_id)
{
    switch ($column)
    {
        case 'post_thumbnail':
            echo '<a href="' . get_edit_post_link() . '">'.the_post_thumbnail( 'thumbnail' ).'</a>';
            break;
    }
}
function add_thumbnail_to_post_list( $columns )
{
    $columns['post_thumbnail'] = 'Thumbnail';
    return $columns;
}

if (function_exists('add_theme_support'))
{
    // Add To Posts
    add_filter( 'manage_posts_columns' , 'add_thumbnail_to_post_list' );
    add_action( 'manage_posts_custom_column' , 'add_thumbnail_to_post_list_data', 10, 2 );
}*/
/*****/

add_filter( 'manage_blog_posts_columns', function ( $columns ) {
$my_columns = [ 'id' => 'ID', 'thumb' => 'Thumbnail', 'star_ratings' => 'Ratings', ];
return array_slice( $columns, 0, 1, 3 ) + $my_columns + $columns;
}, 10 );

add_action( 'manage_blog_posts_custom_column', function ( $column_name ) {
if ( $column_name === 'id' ) { the_ID(); }

if ( $column_name === 'star_ratings' ) {
$star_ratings = get_post_meta(get_the_ID(), 'star_ratings', true);
if(empty($star_ratings)){ $star_ratings = 3; }
$args = array( 'rating' => $star_ratings, 'type' => 'rating', 'number' => 1234, ); wp_star_rating( $args );
}

if ( $column_name === 'thumb' && has_post_thumbnail() ) { ?>
<a href="<?php echo get_edit_post_link(); ?>"><?php the_post_thumbnail( array(150,150) ); ?></a>
<?php }
}, 10,2 );

add_action( 'admin_print_footer_scripts-edit.php', function () {
?>
<style>
.fixed .column-thumb, .fixed .column-star_ratings{ width:100px; }
.column-id { width:50px; }
.column-thumb img { max-width:100%; height:auto; }
</style>
<?php
} );

/*function reg_tag() {
     register_taxonomy_for_object_type('post_tag', 'blog');
}
add_action('init', 'reg_tag');*/
/***** for custom post type blog *****/


/***** custom post template for blog *****/
add_filter('single_template', 'blog_post_cusom_template');
function blog_post_cusom_template($single) {
define(SINGLE_PATH, TEMPLATEPATH . '/');

  //if (has_category('Uncategorized')){
    if(file_exists(SINGLE_PATH . '/single-blog.php')){ return SINGLE_PATH . '/single-blog.php'; }
  //}

}
/***** custom post template for blog *****/

add_action( 'add_meta_boxes', 'blog_meta_box_add' );
function blog_meta_box_add() {
$args=array( 'public' => true, 'exclude_from_search' => false );
$output = 'names'; // names or objects, note names is the default
$operator = 'and'; // 'and' or 'or'
$post_types = get_post_types($args,$output,$operator);
foreach ($post_types  as $post_type ) {
  if($post_type == "blog"){
  add_meta_box( 'blog-meta-box-id', 'Custom Content', 'blog_meta_box_cb', $post_type, 'normal', 'high' );
  }else{}
}

}
function blog_meta_box_cb( $post ) {
    $values = get_post_custom( $post->ID );
    $pagetitle = isset( $values['blog_pagetitle'] ) ? esc_attr( $values['blog_pagetitle'][0] ) : '';
    $pagedesc = isset( $values['blog_metadesc'] ) ? esc_attr( $values['blog_metadesc'][0] ) : '';
    $content = get_post_meta($post->ID, 'custom_editor', true);
    $author_name = get_post_meta($post->ID, 'author_name', true);
    $star_ratings = get_post_meta($post->ID, 'star_ratings', true);
    $wp_custom_attachment = get_post_meta($post->ID, 'wp_custom_attachment', true);
    $saved = get_post_meta( $post->ID, 'myplugin_media_id', true );
    wp_nonce_field( 'blog_meta_box_nonce', 'meta_box_nonce' );

echo '<p><label for="custom_editor"><strong>Add Your Content Here</strong></label><br />'."\n";
//wp_editor ( $content , 'custom_editor',array ( 'media_buttons' => true ) );
wp_editor ( htmlspecialchars_decode($content), 'custom_editor', array("media_buttons" => false) );
echo '<span class="custom_editor">Add your content here</span></p>'."\n";

echo '<p><label for="author_name"><strong>Add Author Name</strong></label>'."\n";
echo '<input type="text" name="author_name" id="author_name" value="'. $author_name .'" placeholder="'. $author_name .'" style="width:100%;" /><br />'."\n";
echo '<span class="description">Add your content here</span></p>'."\n";

echo '<p><label for="wp_custom_attachment"><strong>Add Image</strong></label>'."\n";
echo '<input type="file" name="wp_custom_attachment" id="wp_custom_attachment" style="width:100%;" /><br />'."\n";
echo '<span class="description">Add your image here</span></p>'."\n";

echo '<p><label for="star_ratings"><strong>Ratings for post</strong></label>'."\n".'<br />';
echo '<select name="star_ratings" id="star_ratings">';
for($i=1; $i<=5; $i++){
if($i == $star_ratings){ $selected = ' selected="selected"'; }elseif($i == 3){ $selected = ' selected="selected"'; }else{ $selected = ''; }
echo '<option value="'.$i.'" '.$selected.'>'.$i.'</option>'."\n";
}
echo '</select><br />'."\n";
//echo '<input type="text" name="star_ratings" id="star_ratings" value="'. $star_ratings .'" placeholder="'. $star_ratings .'" style="width:100%;" /><br />'."\n";
echo '<span class="description">Add custom ratings for post</span></p>'."\n";

/*echo '<p><label for="blog_pagetitle"><strong>Page Title: </strong></label><br />'."\n";
echo '<input type="text" name="blog_pagetitle" id="blog_pagetitle" value="'. $pagetitle .'" placeholder="'. $pagetitle .'" style="width:100%;" />'."\n";
echo '<span class="description">Add browser title here</span></p>'."\n";
echo '<p><label for="blog_metadesc"><strong>Meta Description: </strong></label><br />'."\n";
echo '<textarea name="blog_metadesc" id="blog_metadesc" value="'. $pagedesc .'" placeholder="'. $pagedesc .'" style="width:100%;">'. $pagedesc .'</textarea>'."\n";
echo '<span class="description">Add meta description here</span></p>'."\n";
echo '<br /><span class="description">Allow or Disallow page in meta robots</span></p>'."\n";*/

/*$args = array('rating'=>3.5, 'type'=>'rating', 'number'=>1234,);
wp_star_rating($args);*/
}

add_action('post_edit_form_tag', 'update_edit_form');
function update_edit_form() { echo ' enctype="multipart/form-data"'; }

add_action( 'save_post', 'blog_meta_box_save' );
function blog_meta_box_save( $post_id ) {
    //cheking for auto save
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    //verify for nonce field
    if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'blog_meta_box_nonce' ) ) return;
    //checcking current user privilege to editing
    if( !current_user_can( 'edit_post', $post_id ) ) return;

    //saving datas for custom_editor
    if( isset( $_POST['custom_editor'] ) )
        $data=htmlspecialchars($_POST['custom_editor']);
        update_post_meta( $post_id, 'custom_editor', $data );

    //saving datas for author_nameeditor
    if( isset( $_POST['author_name'] ) )
        update_post_meta( $post_id, 'author_name', sanitize_text_field( $_POST['author_name'] ) );

    //saving datas for star_ratings
    if( isset( $_POST['star_ratings'] ) )
        update_post_meta( $post_id, 'star_ratings', sanitize_text_field( $_POST['star_ratings'] ) );

    //saving datas for wp_custom_attachment
    if( !empty( $_FILES['wp_custom_attachment'] ) || !empty( $_POST['wp_custom_attachment'] ) ){

      $filename = $_FILES['wp_custom_attachment'];
      $filesize = $filename['size'];
      $arr_file_type = wp_check_filetype(basename($_FILES['wp_custom_attachment']['name']));
      $uploaded_file_type = $arr_file_type['type'];
      $allowed_file_types = array('image/jpg','image/jpeg','image/gif','image/png');
      $size_in_kb = $filesize / 1024;
      $file_size_limit = 2048;

      if($filesize === 0){ return; // checking if no file selected
      }else{

      if(in_array($uploaded_file_type,  $allowed_file_types)){ // checking for image file
        if( $size_in_kb > $file_size_limit ){ // checking for image size
            $upload_error .= 'Image files must be smaller than '.$file_size_limit.'KB'; return;
        }else{
          $wp_upload_dir = wp_upload_dir();
          $upload_overrides = array( 'test_form' => false );
          $movefile = wp_handle_upload( $filename, $upload_overrides );
          $attachment = array(
  				 'post_mime_type' => $uploaded_file_type,
  				 'post_title' => preg_replace('/\.[^.]+$/', '', basename($_FILES['wp_custom_attachment']['name'])),
  				 'post_content' => '',
  				 'post_status' => 'inherit'
                 );
          $attach_id = wp_insert_attachment( $attachment, $movefile['file'], $post_id );
          $attach_data = wp_generate_attachment_metadata( $attach_id, $movefile['file'] );
          wp_update_attachment_metadata( $attach_id,  $attach_data );
          set_post_thumbnail( $post_id, $attach_id );
          update_post_meta( $post_id, 'wp_custom_attachment', sanitize_text_field( $_POST['wp_custom_attachment'] ) );
        }
      }else{ $upload_error .= 'Invalid File type'; return; }
      }
      if(isset($upload_error)){ echo $upload_error; return; }
    }

    //saving datas for pagetitle
    /*if( isset( $_POST['blog_pagetitle'] ) )
        update_post_meta( $post_id, 'blog_pagetitle', sanitize_text_field( $_POST['blog_pagetitle'] ) );*/

    //saving datas for meta description
    /*if( isset( $_POST['blog_metadesc'] ) )
        update_post_meta( $post_id, 'blog_metadesc', sanitize_text_field( $_POST['seo_metadesc'] ) );*/
}

/*******************************/

/*******************************************************************/
add_shortcode( 'wpshout_frontend_post', 'wpshout_frontend_post' );
function wpshout_frontend_post() {

if( is_user_logged_in() ){

if(getPublishedPostCount() < 5){

wp_enqueue_media();
?>
<div id="postbox">
<form id="new_post" name="new_post" method="post" enctype="multipart/form-data">

<p><label for="title">Title</label><br />
<input type="text" id="title" value="" tabindex="1" size="20" name="title" style="width:50%;" /></p>

<p><label for="page-content">Post Content</label><br />
<!--textarea id="page-content" tabindex="3" name="page-content" cols="50" rows="6" style="width:50%;"></textarea-->

<?php
$usp_editor_content = '';
$usp_rte_settings = array(
'wpautop'          => true,  // enable rich text editor
'media_buttons'    => false,  // enable add media button
'textarea_name'    => 'page-content', // name
'textarea_rows'    => '14',  // number of textarea rows
'teeny'            => false, // output minimal editor config
'dfw'              => false, // replace fullscreen with DFW
'tinymce'          => true,  // enable TinyMCE
'quicktags'        => false,  // disable quicktags, visual and text tab disabled
'drag_drop_upload' => false,  // disable drag-drop
);
wp_editor($usp_editor_content, 'page-content', $usp_rte_settings); ?>
</p>

<p><label for="category">Category</label><br />
<?php wp_dropdown_categories( 'show_option_none=Category&tab_index=1&taxonomy=blog-category&selected=21&show_option_none=Select Category' ); ?></p>

<p><label for="post_tags">Tags</label><br />
<input type="text" value="" tabindex="5" size="16" name="post_tags" id="post_tags" style="width:20%;" /></p>

<p><label for="post_image">Image</label><br />
<input type="file" id="wp_custom_attachment" name="wp_custom_attachment" /></p>

<?php //wp_editor ( htmlspecialchars_decode($content1), 'wp_custom_attachment', array("media_buttons" => true) ); ?>

<?php wp_nonce_field( 'wps-frontend-post' ); ?>

<input type="hidden" name="action" value="saveconfiguration">
<p align="right"><input type="submit" class="button button-primary" value="Publish" tabindex="6" id="submit" name="submit" /></p>

</form>
</div>

<?php
//add_action( 'save_post', 'wpshout_save_post_if_submitted' );
function wpshout_save_post_if_submitted() {

    // Stop running function if form wasn't submitted
    if ( !isset($_POST['title']) ) {
        return;
    }

    if ( !isset($_FILES['wp_custom_attachment']) ) {
      //var_dump($_FILES);
        return;
    }

    // Check that the nonce was set and valid
    if( !wp_verify_nonce($_POST['_wpnonce'], 'wps-frontend-post') ) {
        echo 'Did not save because your form seemed to be invalid. Sorry';
        return;
    }

    // Do some minor form validation to make sure there is content
    if (strlen($_POST['title']) < 3) {
        echo 'Please enter a title. Titles must be at least three characters long.';
        return;
    }
    if (strlen($_POST['page-content']) < 100) {
        echo 'Please enter content more than 100 characters in length';
        return;
    }
    if ( !isset( $_FILES['wp_custom_attachment'] )) {
        echo 'Please select image to upload';
        return;
    }

    if ( !function_exists( 'wp_handle_upload' ) ) {
      require_once(ABSPATH . "wp-admin" . '/includes/image.php');
      require_once(ABSPATH . "wp-admin" . '/includes/file.php');
      require_once(ABSPATH . "wp-admin" . '/includes/media.php');
    }

    // Add the content of the form to $post as an array
    $post = array(
        'post_title'    => wp_strip_all_tags($_POST['title']),
        'post_content'  => $_POST['page-content'],
        'post_category' => array($_POST['cat']),
        'tags_input'    => array($_POST['post_tags']),
        'tax_input'     => array( 'blog-category'=>$_POST['cat'], 'blog-tag'=>$_POST['post_tags'] ),
        'post_status'   => 'draft',
        'post_type' 	=> 'blog',
    );
    $postid = wp_insert_post($post);

    if( !empty( $_FILES['wp_custom_attachment'] ) || !empty( $_POST['wp_custom_attachment'] ) ){

      $filename = $_FILES['wp_custom_attachment'];
      $filesize = $filename['size'];
      $arr_file_type = wp_check_filetype(basename($_FILES['wp_custom_attachment']['name']));
      $uploaded_file_type = $arr_file_type['type'];
      $allowed_file_types = array('image/jpg','image/jpeg','image/gif','image/png');
      $size_in_kb = $filesize / 1024;
      $file_size_limit = 2048;

      if($filesize === 0){ return; // checking if no file selected
      }else{

      if(in_array($uploaded_file_type,  $allowed_file_types)){ // checking for image file
        if( $size_in_kb > $file_size_limit ){ // checking for image size
            $upload_error .= 'Image files must be smaller than '.$file_size_limit.'KB'; return;
        }else{
          $wp_upload_dir = wp_upload_dir();
          $upload_overrides = array( 'test_form' => false );
          $movefile = wp_handle_upload( $filename, $upload_overrides );
          $attachment = array(
  				 'post_mime_type' => $uploaded_file_type,
  				 'post_title' => preg_replace('/\.[^.]+$/', '', basename($_FILES['wp_custom_attachment']['name'])),
  				 'post_content' => '',
  				 'post_status' => 'inherit'
                 );
          $attach_id = wp_insert_attachment( $attachment, $movefile['file'], $postid );
          $attach_data = wp_generate_attachment_metadata( $attach_id, $movefile['file'] );
          wp_update_attachment_metadata( $attach_id,  $attach_data );
          set_post_thumbnail( $postid, $attach_id );
          update_post_meta( $postid, 'wp_custom_attachment', sanitize_text_field( $_POST['wp_custom_attachment'] ) );
        }
      }else{ $upload_error .= 'Invalid File type'; return; }
      }
      if(isset($upload_error)){ echo $upload_error; return; }
    }

    echo 'Saved your post successfully! :)';
}
wpshout_save_post_if_submitted();

}else{
echo '<div class="error notice notice-error"><p style="font-size:2em;">Publication limit has been reached - you cannot create a new '.$typenow.' at this time.</p></div>
        <style>#poststuff{ display:none; visibility:hidden;}</style>';
}

}else{ echo "<span style='font-size:18px; color:red; font-weight:700;'>You don't have permission to view this page !!! </span>"; }

}

/******************************************************************/

function getPublishedPostCount(){
global $wpdb;
$userId = get_current_user_id();
$query = "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_type='blog' AND post_author=$userId AND post_status='publish'";

//$query .= "post_type='$postType' ";
//$query .= "post_type='blog' ";
//$query .= "AND post_date>'".$startDateTime->format('c')."' ";
//if(!$all){
//$query .= "AND post_author=$userId ";
//}
//if($publishAction == "Publish"){
//$query .= "AND post_status='publish'";
//}
//elseif($publishAction == "Submit For Review"){
//$query .= "AND post_status='pending'";  //or pending
//}
$count = $wpdb->get_var($query);
return $count;
}

add_action('admin_notices', 'applyPostLimtRules');
function applyPostLimtRules(){
global $pagenow;
if ($pagenow == 'post-new.php'){  //submit for review and publish
	global $typenow;

	$publishAction = 'Publish';
	if(!current_user_can('publish_posts')){
		$publishAction = 'Submit For Review';
	}

	if(getPublishedPostCount() >= 5){
		//disable publish and display message
		//wp_enqueue_style('cb_disable_publish', CB_LIMIT_POSTS_PLUGIN_URL . '/css/disablepublish.css');
		echo '<div class="error notice notice-error"><p style="font-size:2em;">Publication limit has been reached - you cannot create a new '.$typenow.' at this time.</p></div>
        <style>#poststuff{ display:none; visibility:hidden;}</style>';
	}
}
}

/***** Call to actions *****/
function call_to_action_main( $atts ) {
	$atts = shortcode_atts( array(
		'city_name' => 'Temecula'
	), $atts, 'bartag' );

	return '
<div class="city-list">
<h3 style="color:#FB8103;"> Don\'t Waste Time; Call On Time Today!</h3>
<p><strong>Call <a href="tel:951-474-0636">(951) 474-0636</a> or schedule an in-home consultation appointment online.</strong></p>
</div>
';
}
add_shortcode( 'cta_main', 'call_to_action_main' );
/*** use do_shortcode() method to print ***/
/***** Call to actions *****/

/***** Remove all CSS ans JS *****/
/*
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'start_post_rel_link');
remove_action('wp_head', 'index_rel_link');
remove_action('wp_head', 'adjacent_posts_rel_link');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'start_post_rel_link');
remove_action('wp_head', 'index_rel_link');
remove_action('wp_head', 'adjacent_posts_rel_link');

add_action( 'wp_enqueue_scripts', 'clear_styles_and_scripts', 100 );
function clear_styles_and_scripts() {

global $wp_scripts;
global $wp_styles;

foreach( $wp_scripts->queue as $handle ) :
  wp_dequeue_script( $handle );
  wp_deregister_script( $handle );
endforeach;

foreach( $wp_styles ->queue as $handle ) :
  wp_dequeue_style( $handle );
  wp_deregister_style( $handle );
endforeach;

}
*/
/***** Remove all CSS ans JS *****/

/*add_action("load-post-new.php","limit_user_by_post_count");
function limit_user_by_post_count(){
$user = get_current_user_id();
if (!current_user_can( ‘manage_options’)) {
//not an admin – so impose the limit
$user_post_count = count_user_posts($user);
if($user_post_count>=10)
header("Location: http://localhost/test/newwp/wp-admin/edit.php");
}
}*/

// Could be better adds the function to the 'init' hook and check later if it's an admin page
add_action( 'init', 'my_custom_dashboard_access_handler');

function my_custom_dashboard_access_handler() {

   // Check if the current page is an admin page
   // && and ensure that this is not an ajax call
   if ( is_admin() && !( defined( 'DOING_AJAX' ) && DOING_AJAX ) ){

      //Get all capabilities of the current user
      $user = get_userdata( get_current_user_id() );
      $caps = ( is_object( $user) ) ? array_keys($user->allcaps) : array();

      //All capabilities/roles listed here are not able to see the dashboard
      $block_access_to = array('subscriber', 'contributor', 'my-custom-role', 'my-custom-capability');

      if(array_intersect($block_access_to, $caps)) {
         wp_redirect( home_url() );
         exit;
      }
   }
}
?>