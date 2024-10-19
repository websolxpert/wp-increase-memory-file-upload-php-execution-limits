<?php
/**
 * Plugin Name: Increase WP Memory and Upload Limits
 * Description: A plugin to increase WP memory, upload file limits, and PHP execution time via backend settings.
 * Version: 1.8
 * Author: Web Sol Xpert
 * Author URI: https://websolxpert.com
 */

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

// Create admin menu
function iwmul_add_admin_menu() {
    add_options_page('Increase WP Limits', 'Increase WP Limits', 'manage_options', 'increase_wp_limits', 'iwmul_options_page');
}
add_action('admin_menu', 'iwmul_add_admin_menu');

// Register settings
function iwmul_settings_init() {
    register_setting('iwmul_options_group', 'iwmul_memory_limit', 'intval');
    register_setting('iwmul_options_group', 'iwmul_upload_limit', 'intval');
    register_setting('iwmul_options_group', 'iwmul_execution_time', 'intval');

    add_settings_section(
        'iwmul_settings_section',
        'Adjust WordPress Limits',
        'iwmul_settings_section_callback',
        'increase_wp_limits'
    );

    add_settings_field(
        'iwmul_memory_limit',
        'Increase WP Memory Limit (MB)',
        'iwmul_memory_limit_render',
        'increase_wp_limits',
        'iwmul_settings_section'
    );

    add_settings_field(
        'iwmul_upload_limit',
        'Increase WP File Upload Limit (MB)',
        'iwmul_upload_limit_render',
        'increase_wp_limits',
        'iwmul_settings_section'
    );

    add_settings_field(
        'iwmul_execution_time',
        'Increase PHP Execution Time (seconds)',
        'iwmul_execution_time_render',
        'increase_wp_limits',
        'iwmul_settings_section'
    );

}
add_action('admin_init', 'iwmul_settings_init');

// Section callback
function iwmul_settings_section_callback() {
    echo 'Set the memory, upload limits, and execution time for your WordPress installation.';
}

// Memory limit field
function iwmul_memory_limit_render() {
    $options = get_option('iwmul_memory_limit');
    $current_memory_limit = ini_get('memory_limit');
    
    echo '<input type="number" name="iwmul_memory_limit" value="' . esc_attr($options) . '" min="32" /> MB';
    echo '<p><strong>Current Memory Limit:</strong> ' . esc_html($current_memory_limit) . '</p>'; // Show current value
}

// Upload limit field
function iwmul_upload_limit_render() {
    $options = get_option('iwmul_upload_limit');
    $current_upload_limit = $options ? $options . ' MB' : 'Not set';
    echo '<input type="number" name="iwmul_upload_limit" value="' . esc_attr($options) . '" min="40" /> MB';
    echo '<p><strong>Current Upload Limit:</strong> ' . esc_html($current_upload_limit) . '</p>'; // Show saved value
}

// Execution time field
function iwmul_execution_time_render() {
    $options = get_option('iwmul_execution_time');
    $current_execution_time = ini_get('max_execution_time');
    
    echo '<input type="number" name="iwmul_execution_time" value="' . esc_attr($options) . '" min="60" /> seconds';
    echo '<p><strong>Current Execution Time:</strong> ' . esc_html($current_execution_time) . ' seconds</p>'; // Show current value
}

// Options page
function iwmul_options_page() {
    ?>
    <form action="options.php" method="post">
        <h2>Increase WP Memory and Upload Limits</h2>
        <?php
        settings_fields('iwmul_options_group');
        do_settings_sections('increase_wp_limits');
        submit_button();
        ?>
    </form>
    <h3>Info</h3>
    <p>This could be 128MB to 2GB on Shared Hosting and could vary on VPS and Dedicated Servers (up to 64GB).</p>
    <p>The upload limit will vary based on your needs; specify the maximum file size you want to upload.</p>
    <?php
}

// Apply settings
function iwmul_apply_settings() {
    $memory_limit = get_option('iwmul_memory_limit');
    $upload_limit = get_option('iwmul_upload_limit');
    $execution_time = get_option('iwmul_execution_time');

    // Apply memory limit only if it's a valid value
    if ($memory_limit && $memory_limit <= 2048) {
        @ini_set('memory_limit', $memory_limit . 'M');
        define('WP_MEMORY_LIMIT', $memory_limit . 'M'); // Frontend limit
        define('WP_MAX_MEMORY_LIMIT', $memory_limit . 'M'); // Backend limit
    }

    // Apply upload limit only if it's a valid value
    if ($upload_limit) {
        @ini_set('upload_max_filesize', $upload_limit . 'M');
        @ini_set('post_max_size', $upload_limit . 'M');
    }

    // Apply execution time only if it's a valid value
    if ($execution_time) {
        @ini_set('max_execution_time', $execution_time);
    }
}
add_action('init', 'iwmul_apply_settings');

// Filter to adjust upload size limit in WordPress
add_filter('upload_size_limit', 'iwmul_custom_upload_size');
function iwmul_custom_upload_size($size) {
    $upload_limit = get_option('iwmul_upload_limit');
    if ($upload_limit) {
        $new_limit = $upload_limit * 1024 * 1024; // Convert to bytes
        return $new_limit;
    }
    return $size; // Default size if not set
}
