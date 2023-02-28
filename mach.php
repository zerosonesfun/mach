<?php
/*
Plugin Name: Mach
Plugin URI: https://wilcosky.com/mach
Description: Create a new post from the front-end; QUICKLY.
Version: 1.0
Author: Billy Wilcosky
Author URI: https://wilcosky.com
*/

// Enqueue the CSS file
function mach_enqueue_styles() {
  wp_enqueue_style( 'mach-styles', plugin_dir_url( __FILE__ ) . 'mach.css', array(), '1.0.0', 'all' );
}
add_action( 'wp_enqueue_scripts', 'mach_enqueue_styles' );
  
// Enqueue the JavaScript file
function mach_enqueue_scripts() {
  wp_enqueue_script('mach', plugins_url('mach.js', __FILE__), array('jquery'), '1.0.0', true);
  wp_localize_script('mach', 'machData', array(
    'ajaxurl' => admin_url('/admin-ajax.php'),
    'nonce' => wp_create_nonce('mach'),
    'defaultCategory' => get_option('default_category')
  ));
}
add_action('wp_enqueue_scripts', 'mach_enqueue_scripts');

// Create the shortcode
function mach_shortcode($atts, $content = null) {
  $html = '<form id="mach-form" method="post">';
  $html .= '<textarea id="mach-content" name="mach_content" placeholder="Enter your content here"></textarea>';
  $html .= '<input type="submit" value="Submit" />';
  $html .= '</form>';
  return $html;
}
add_shortcode('mach-form', 'mach_shortcode');

// Handle the AJAX request
function mach_submit() {
  // Verify the nonce
  if (!wp_verify_nonce($_POST['nonce'], 'mach')) {
    wp_send_json_error('Invalid nonce');
  }

  // Check if the user is logged in
  if (!is_user_logged_in()) {
    wp_send_json_error('Hello guest! Have a great day!');
  }

  // Get the content, title, tags, and category from the AJAX request
  $content = sanitize_textarea_field($_POST['content']);
  $title = sanitize_textarea_field($_POST['title']);
  $tags = array_map('sanitize_textarea_field', $_POST['tags']);
  $category = sanitize_textarea_field($_POST['category']);

  // Create the post
  $post_id = wp_insert_post(array(
    'post_title' => $title,
    'post_content' => $content,
    'post_status' => 'publish',
    'post_category' => array($category),
    'tags_input' => $tags
  ));

  // Check if the post was created successfully
  if ($post_id) {
    wp_send_json_success();
  } else {
    wp_send_json_error('An error occurred while creating the post. Please try again later.');
  }
}
add_action('wp_ajax_mach_submit', 'mach_submit');
add_action('wp_ajax_nopriv_mach_submit', 'mach_submit');