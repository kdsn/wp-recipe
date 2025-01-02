<?php
/**
* Plugin Name: WP Recipe
* Plugin URI: https://github.com/kdsn/wp-recipe
* Description: A WordPress plugin for creating dynamic recipe cards and menus with Elementor support.
* Version: 1.0.0
* Author: Keld W. Sorensen
* Author URI: https://kdsn.dk
* License: MIT
* Text Domain: wp-recipe
*/

// Prevent direct access to the file.
if (!defined('ABSPATH')) {
    exit;
}

// Define constants.
define('WP_RECIPE_VERSION', '1.0.0');
define('WP_RECIPE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_RECIPE_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoload classes with namespaces.
spl_autoload_register(function ($class) {
    $prefix = 'WP_Recipe\\';
    $base_dir = WP_RECIPE_PLUGIN_DIR . 'includes/';
    $len = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    error_log("Autoloader trying to load class: $class");
    error_log("File path resolved to: $file");

    if (file_exists($file)) {
        require $file;
        error_log("File loaded successfully: $file");
    } else {
        error_log("File not found: $file");
    }
});

// Initialize the plugin.
function wp_recipe_init() {
    \WP_Recipe\Recipe::init();
    \WP_Recipe\Menu_Generator::init();
    #\WP_Recipe\Shortcodes::init();
}
add_action('plugins_loaded', 'wp_recipe_init');