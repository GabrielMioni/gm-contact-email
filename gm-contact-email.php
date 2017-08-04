<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
/*
Plugin Name: Gabriel's Contact Email
Plugin URI: http://gabrielmioni.com
Description: This plugin sends a contact email
Author: Gabriel Mioni
Author URI: http://gabrielmioni.com
*/

session_start();

// Plugin version, bump it up if you update the plugin
define( 'GM_CONTACT_VERSION', '1.0' );

// Enqueue the script, in the footer
add_action( 'template_redirect', 'gm_add_contact_js');
function gm_add_contact_js() {

    // Enqueue the script
    wp_enqueue_script( 'gm_contact',
        plugin_dir_url( __FILE__ ).'js/contact.js',
        array('jquery'), GM_CONTACT_VERSION, true
    );

    // Get current page protocol.
    $protocol = isset( $_SERVER["HTTPS"]) ? 'https://' : 'http://';

    // Localize ajaxurl with protocol
    $params = array(
        'ajaxurl' => admin_url( 'admin-ajax.php', $protocol )
    );
    wp_localize_script( 'gm_contact', 'gm_contact', $params );
}

// Shortcode for the contact form.
add_shortcode('gm-email-form', 'gm_email_form');
function gm_email_form() {
    require_once('gm_contact_form.php');

    $auto_p_flag = false;

    // If wpautop filter is set, temporarily disable it.
    if ( has_filter( 'the_content', 'wpautop' ) )
    {
        $auto_p_flag = true;
        remove_filter( 'the_content', 'wpautop' );
        remove_filter( 'the_excerpt', 'wpautop' );
    }

    $build_html = new gm_contact_form();
    echo $build_html->return_html();

    // If wpautop had been set previously, re-enable it.
    if ($auto_p_flag === true)
    {
        add_filter( 'the_content', 'wpautop' );
        add_filter( 'the_excerpt', 'wpautop' );
    }
}

// Ajax handler
add_action('wp_ajax_nopriv_gm_contact_ajax', 'gm_contact_ajax');
add_action('wp_ajax_gm_contact_ajax', 'gm_contact_ajax');
function gm_contact_ajax() {

    require_once('gm-contact-email-send.php');

    $data = $_REQUEST['form_data'];

    parse_str($data, $get_array);

    $_POST['is_ajax'] = 1;
    $_POST['name']    = $get_array['name'];
    $_POST['email']   = $get_array['email'];
    $_POST['company'] = $get_array['company'];
    $_POST['message'] = $get_array['message'];

    $send_email = new gm_contact_email_send();
    $send_email_response = $send_email->return_ajax_msg();

    echo $send_email_response;

    die();
}

