<?php

/**
 * @package     GM-Contact-Email
 * @author      Gabriel Mioni <gabriel@gabrielmioni.com>
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

// https://media.giphy.com/media/qUUyTJkYe0I1O/giphy.gif

$option_name = 'gm_contact_address';

if (!is_multisite())
{
    delete_option($option_name);
}
else {
    delete_site_option($option_name);
}
