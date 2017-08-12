<?php

// Say goodnight.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

$option_name = 'gm_contact_address';

if (!is_multisite())
{
    delete_option($option_name);
}
else {
    delete_site_option($option_name);
}
