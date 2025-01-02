<?php
namespace WP_Recipe;

class Recipe {
    public static function init() {
        add_action('init', [__CLASS__, 'register_post_type']);
        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
        add_action('save_post', [__CLASS__, 'save_recipe_metadata']);
    }

    public static function register_post_type() {
        register_post_type('recipe', [
            'labels' => [
                'name' => __('Recipes', 'wp-recipe'),
                'singular_name' => __('Recipe', 'wp-recipe'),
            ],
            'public' => true,
            'has_archive' => true,
            'rewrite' => ['slug' => 'recipes'],
            'supports' => ['title', 'editor', 'thumbnail'],
        ]);
    }

    public static function add_meta_boxes() {
        add_meta_box(
            'recipe_details',
            __('Recipe Details', 'wp-recipe'),
            [__CLASS__, 'render_meta_boxes'],
            'recipe',
            'normal',
            'default'
        );
    }

    public static function render_meta_boxes($post) {
        // Get existing metadata
        $description = get_post_meta($post->ID, '_recipe_description', true);
        $ingredients = get_post_meta($post->ID, '_recipe_ingredients', true);
        $instructions = get_post_meta($post->ID, '_recipe_instructions', true);
        $tips = get_post_meta($post->ID, '_recipe_tips', true);
        $time = get_post_meta($post->ID, '_recipe_time', true);
        $price = get_post_meta($post->ID, '_recipe_price', true);
        $servings = get_post_meta($post->ID, '_recipe_servings', true);

        // Safety key
        wp_nonce_field('save_recipe_metadata', 'recipe_nonce');

        ?>
        <p>
            <label for="recipe_description"><?php _e('Description', 'wp-recipe'); ?></label>
            <textarea id="recipe_description" name="recipe_description" rows="4" style="width:100%;"><?php echo esc_textarea($description); ?></textarea>
        </p>
        <p>
            <label for="recipe_servings"><?php _e('Number of Servings', 'wp-recipe'); ?></label>
            <input type="number" id="recipe_servings" name="recipe_servings" value="<?php echo esc_attr($servings); ?>" style="width:100%;">
        </p>
        <p>
            <label><?php _e('Ingredients', 'wp-recipe'); ?></label>
        <div id="recipe-ingredients-container">
            <?php
            if (!empty($ingredients) && is_array($ingredients)) {
                foreach ($ingredients as $index => $ingredient) {
                    self::render_ingredient_row($index, $ingredient);
                }
            } else {
                self::render_ingredient_row(0, ['amount' => '', 'unit' => '', 'description' => '']);
            }
            ?>
        </div>
        <button type="button" id="add-ingredient-button" class="button"><?php _e('Add Ingredient', 'wp-recipe'); ?></button>
        </p>
        <p>
            <label for="recipe_instructions"><?php _e('Instructions', 'wp-recipe'); ?></label>
            <textarea id="recipe_instructions" name="recipe_instructions" rows="4" style="width:100%;"><?php echo esc_textarea($instructions); ?></textarea>
        </p>
        <p>
            <label for="recipe_tips"><?php _e('Tips & Tricks', 'wp-recipe'); ?></label>
            <textarea id="recipe_tips" name="recipe_tips" rows="4" style="width:100%;"><?php echo esc_textarea($tips); ?></textarea>
        </p>
        <p>
            <label for="recipe_time"><?php _e('Time (minutes)', 'wp-recipe'); ?></label>
            <input type="number" id="recipe_time" name="recipe_time" value="<?php echo esc_attr($time); ?>" style="width:100%;">
        </p>
        <p>
            <label for="recipe_price"><?php _e('Price Level', 'wp-recipe'); ?></label><br>
            <select id="recipe_price" name="recipe_price" style="width:100%;">
                <option value="1" <?php selected($price, '1'); ?>>$</option>
                <option value="2" <?php selected($price, '2'); ?>>$$</option>
                <option value="3" <?php selected($price, '3'); ?>>$$$</option>
                <option value="4" <?php selected($price, '4'); ?>>$$$$</option>
                <option value="5" <?php selected($price, '5'); ?>>$$$$$</option>
            </select>
        </p>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const container = document.getElementById('recipe-ingredients-container');
                const button = document.getElementById('add-ingredient-button');

                button.addEventListener('click', function () {
                    const index = container.children.length;
                    const template = `
                    <div class="ingredient-row">
                        <input type="number" name="recipe_ingredients[${index}][amount]" placeholder="Amount" style="width:20%;" />
                        <input type="text" name="recipe_ingredients[${index}][unit]" placeholder="Unit" style="width:20%;" />
                        <input type="text" name="recipe_ingredients[${index}][description]" placeholder="Description" style="width:55%;" />
                        <button type="button" class="remove-ingredient button" style="width:5%;">X</button>
                    </div>`;
                    container.insertAdjacentHTML('beforeend', template);

                    container.querySelectorAll('.remove-ingredient').forEach(button => {
                        button.addEventListener('click', function () {
                            this.parentNode.remove();
                        });
                    });
                });

                container.querySelectorAll('.remove-ingredient').forEach(button => {
                    button.addEventListener('click', function () {
                        this.parentNode.remove();
                    });
                });
            });
        </script>
        <style>
            .ingredient-row {
                display: flex;
                gap: 10px;
                margin-bottom: 10px;
            }
        </style>
        <?php
    }

    private static function render_ingredient_row($index, $ingredient) {
        ?>
        <div class="ingredient-row">
            <input type="number" name="recipe_ingredients[<?php echo $index; ?>][amount]" placeholder="Amount" value="<?php echo esc_attr($ingredient['amount']); ?>" style="width:20%;" />
            <input type="text" name="recipe_ingredients[<?php echo $index; ?>][unit]" placeholder="Unit" value="<?php echo esc_attr($ingredient['unit']); ?>" style="width:20%;" />
            <input type="text" name="recipe_ingredients[<?php echo $index; ?>][description]" placeholder="Description" value="<?php echo esc_attr($ingredient['description']); ?>" style="width:55%;" />
            <button type="button" class="remove-ingredient button" style="width:5%;">X</button>
        </div>
        <?php
    }


    public static function save_recipe_metadata($post_id) {
        if (!isset($_POST['recipe_nonce']) || !wp_verify_nonce($_POST['recipe_nonce'], 'save_recipe_metadata')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Save number of plates
        update_post_meta($post_id, '_recipe_servings', intval($_POST['recipe_servings'] ?? 0));

        // Save ingredients
        $ingredients = $_POST['recipe_ingredients'] ?? [];
        $sanitized_ingredients = [];
        foreach ($ingredients as $ingredient) {
            $sanitized_ingredients[] = [
                'amount' => sanitize_text_field($ingredient['amount']),
                'unit' => sanitize_text_field($ingredient['unit']),
                'description' => sanitize_text_field($ingredient['description']),
            ];
        }
        update_post_meta($post_id, '_recipe_ingredients', $sanitized_ingredients);

        // Save other fields
        update_post_meta($post_id, '_recipe_description', sanitize_textarea_field($_POST['recipe_description'] ?? ''));
        update_post_meta($post_id, '_recipe_instructions', sanitize_textarea_field($_POST['recipe_instructions'] ?? ''));
        update_post_meta($post_id, '_recipe_tips', sanitize_textarea_field($_POST['recipe_tips'] ?? ''));
        update_post_meta($post_id, '_recipe_time', intval($_POST['recipe_time'] ?? 0));
        update_post_meta($post_id, '_recipe_price', intval($_POST['recipe_price'] ?? 1));
    }

}
