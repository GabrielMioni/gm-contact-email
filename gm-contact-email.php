<?php
/*
Plugin Name: GM-Contact-Email
Plugin URI: http://gabrielmioni.com
Description: Simple contact form functionality. Example at gabrielmioni.com/contact
Author: Gabriel Mioni
Author URI: http://gabrielmioni.com

Copyright 2017 Gabriel Mioni <email : gabriel@gabrielmioni.com>

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * @package     GM-Contact-Form
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 *
 * This file does the following:
 * 1. Registers CSS and JS files used for GM-Contact
 * 2. Registers a shortcode that lets the user set the HTML contact form on WordPress pages
 * 3. Creates an options page on the WordPress Admin Panel at Settings > GM Contact
 * 4. Handles Ajax requests when the contact form is submitted.
 * 5. Handles non-Ajax requests if JS is disabled.
 */

if (!isset($_SESSION))
{
    session_start();
}

// Plugin version, bump it up if you update the plugin
define( 'GM_CONTACT_VERSION', '1.0' );

// Register CSS
add_action( 'wp_enqueue_scripts', 'gm_register_css' );
function gm_register_css()
{
    wp_register_style( 'gm-contact-css', plugins_url( '/css/gm-contact.css', __FILE__ ), array(), GM_CONTACT_VERSION, 'all' );
}

// Register Google Fonts
add_action('wp_enqueue_scripts', 'gm_register_oswald');
function gm_register_oswald()
{
    wp_enqueue_style( 'gm-google-oswald', 'https://fonts.googleapis.com/css?family=Fjalla+One|Oswald', false );
}

// Enqueue the script, in the footer
add_action( 'template_redirect', 'gm_add_contact_js');
function gm_add_contact_js() {

    // Enqueue the script
    wp_enqueue_script( 'gm_contact',
        plugin_dir_url( __FILE__ ).'js/gm-contact.js',
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

/* *******************************
 * - Contact Form Shortcode
 * *******************************/

add_shortcode('gm-email-form', 'gm_email_form');
function gm_email_form() {
    require_once('gm-contact-form.php');

    // Add the gm-contact.js file
//    wp_enqueue_script('gm-contact-js');

    // Add the gm-contact.css file
    wp_enqueue_style('gm-contact-css');

    // Add Google Oswlad font
    wp_enqueue_style('gm-google-oswald');

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

/* *******************************
 * - Settings Page
 * *******************************/

// Add the GM Contact setting option/page
add_action('admin_menu', 'gm_contact_add_page');
function gm_contact_add_page()
{
    add_options_page('GM Contact', 'GM Contact', 'manage_options', 'gm_contact', 'gm_contact_option_page');
}

// Display GM Contact Settings
function gm_contact_option_page()
{
    ?>
    <form action="options.php" method="post">
        <?php   settings_fields('gm_contact_address');  ?>
        <?php   do_settings_sections('gm_contact');     ?>
        <input name="Submit" type="submit" class="button button-primary" value="Save Address">
    </form>

    <?php
}

// Register Settings
add_action('admin_init', 'gm_contact_admin_init');
function gm_contact_admin_init()
{
    register_setting('gm_contact_address', 'gm_contact_address', 'gm_contact_validate_address');
    add_settings_section('gm_contact_main', 'GM Contact Settings', 'gm_contact_section_text', 'gm_contact');
    add_settings_field('gm_contact_text_string', 'Address:', 'gm_contact_address_input', 'gm_contact', 'gm_contact_main');
    add_settings_field('gm_contact_name_string', 'Name:', 'gm_contact_name_input', 'gm_contact', 'gm_contact_main');
}

// Basic instructions for the setting page.
function gm_contact_section_text()
{
    echo '<p>Enter your name and email address below. This will set the recipient\'s name and email address for the Contact Form!</p>';
}

// Email Address input
function gm_contact_address_input()
{
    $option  = get_option('gm_contact_address');

    $address = '';

    if (is_array($option))
    {
        $address = isset($option['address']) ? $option['address'] : '';
    }

    $input =  "<input id='address' name='gm_contact_address[address]' type='text' value='$address'>";

    echo $input;
}

// Name input
function gm_contact_name_input()
{
    $option  = get_option('gm_contact_address');

    $name = '';

    if (is_array($option))
    {
        $name    = isset($option['name']) ? $option['name'] : '';
    }

    $input = "<input id='name' name='gm_contact_address[name]' type='text' value='$name'>";

    echo $input;
}

// Setting validation
function gm_contact_validate_settings($input )
{
    $current = get_option('gm_contact_address');

    $check['address'] = strip_tags($input['address']);
    $check['name']    = strip_tags($input['name']);

    $reason = '';

    if (filter_var($check['address'], FILTER_VALIDATE_EMAIL) === false)
    {
        $bad_input = $check['address'];
        $reason = "You submitted '$bad_input.' That value is not in valid format.<br>";
    }

    if(trim($check['address']) === '')
    {
        $reason = 'Address cannot be empty!<br>';
    }

    if(trim($check['name'] === ''))
    {
        $reason .= 'The name field cannot be blank';
    }

    if($reason !== '')
    {
        add_settings_error(
            'gm_contact_text_string',
            'gm_contact_texterror',
            $reason,
            'error'
        );
        return $current;
    }

    return $check;
}


/* *******************************
 * - Ajax Handler
 * *******************************/
add_action('wp_ajax_nopriv_gm_contact_ajax', 'gm_contact_ajax');
add_action('wp_ajax_gm_contact_ajax', 'gm_contact_ajax');
function gm_contact_ajax() {

    require_once('gm-contact-send.php');

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

/* *******************************
 * - API Handler for non-Ajax
 * *******************************/
add_action('init', 'gm_contact_check_api');
function gm_contact_check_api()
{
    $api_set = isset($_GET['gm_contact']) ? true : false;

    if ($api_set === true)
    {
        require_once(dirname(__FILE__) . '/gm-contact-send.php');

        new gm_contact_email_send();
    }
}