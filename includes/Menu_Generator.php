<?php
namespace WP_Recipe;

class Menu_Generator {
    public static function init() {
        add_action('init', [__CLASS__, 'register_menu_shortcode']);
    }

    public static function register_menu_shortcode() {
        add_shortcode('recipe_menu', [__CLASS__, 'render_menu']);
    }

    public static function render_menu($atts = []) {
        return '<p>' . __('This is a menu generator placeholder.', 'wp-recipe') . '</p>';
    }
}
