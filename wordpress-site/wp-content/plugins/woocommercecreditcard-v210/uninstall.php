<?php

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

// Delete options.
delete_option('woocommerce_' . get_file_data(__DIR__ . '/index.php', array('Text Domain' => 'Text Domain'), false)['Text Domain'] . '_settings');

// Clear any cached data that has been removed.
wp_cache_flush();
