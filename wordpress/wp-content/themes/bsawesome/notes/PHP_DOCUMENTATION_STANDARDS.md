# BSAwesome Theme - PHP Documentation Standards

This document defines the coding and documentation standards for the BSAwesome WordPress theme to ensure consistency, maintainability, and professional quality.

## File Header Standards

Every PHP file must start with the security check and a comprehensive file header:

```php
<?php defined('ABSPATH') || exit;

/**
 * [File Purpose Description]
 *
 * [Detailed description of what this file does, its role in the theme,
 * and any important implementation details or dependencies.]
 *
 * @package BSAwesome
 * @subpackage [ComponentName]
 * @since 1.0.0
 * @author BS Awesome Team
 * @version 1.0.0
 * 
 * @link [relevant documentation link if applicable]
 */
```

### Subpackage Names
Use these standardized subpackage names:

- `Functions` - Main functions.php file
- `Setup` - Theme setup and configuration
- `WooCommerce` - WooCommerce integration
- `Shortcodes` - Custom shortcode handlers
- `ProductLoop` - Product loop customizations
- `Templates` - Template files (header, footer, index, etc.)
- `LayoutComponents` - Layout component files
- `CategoryComponents` - Category-specific components
- `ProductComponents` - Product-specific components
- `Assets` - Asset management
- `Security` - Security-related functionality
- `Performance` - Performance optimization
- `Internationalization` - i18n/l10n functionality

## Function Documentation Standards

Every function must have comprehensive documentation:

```php
/**
 * [Brief description of what the function does]
 *
 * [Longer description explaining the function's purpose, when to use it,
 * any side effects, and how it fits into the broader system.]
 *
 * @since 1.0.0
 * @param string $param1 Description of parameter
 * @param int    $param2 Optional. Description of optional parameter. Default 0.
 * @param array  $param3 {
 *     Optional. Array of arguments.
 *     
 *     @type string $key1 Description of array key.
 *     @type int    $key2 Description of array key.
 * }
 * @return string|false Returns description on success, false on failure.
 */
function theme_function_name($param1, $param2 = 0, $param3 = array()) {
    // Function implementation
}
```

## Class Documentation Standards

```php
/**
 * [Class Purpose Description]
 *
 * [Detailed description of the class, its responsibilities,
 * and usage examples.]
 *
 * @since 1.0.0
 * @package BSAwesome
 * @subpackage [ComponentName]
 */
class BSAwesome_Class_Name {
    
    /**
     * [Property description]
     *
     * @since 1.0.0
     * @var string
     */
    public $property_name;
    
    /**
     * Constructor
     *
     * [Description of what the constructor does]
     *
     * @since 1.0.0
     * @param array $args Constructor arguments
     */
    public function __construct($args = array()) {
        // Implementation
    }
}
```

## Inline Comment Standards

### Section Comments
Use these for major sections within files:

```php
/**
 * SECTION NAME
 * 
 * Description of what this section contains
 */
```

### Block Comments
For explaining complex logic:

```php
/**
 * Detailed explanation of the following code block.
 * Multiple lines are okay for complex explanations.
 */
```

### Single Line Comments
For brief explanations:

```php
// Brief explanation of the following line or short block
$variable = some_function();
```

### TODO Comments
For future improvements:

```php
// TODO: Implement caching for this expensive operation
// @author [Developer Name]
// @date [Date]
```

## WordPress-Specific Documentation

### Hook Documentation
```php
/**
 * Hook Name: action_name or filter_name
 *
 * [Description of when this hook fires and what it's used for]
 *
 * @since 1.0.0
 * @param string $param1 Description
 * @param array  $param2 Description
 */
add_action('action_name', 'function_name', 10, 2);
```

### Template Documentation
Template files should include template hierarchy information:

```php
/**
 * [Template Name]
 *
 * [Description of when this template is used and what it displays]
 *
 * @package BSAwesome
 * @subpackage Templates
 * @since 1.0.0
 * @author BS Awesome Team
 * @version 1.0.0
 * 
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 */
```

## Security Documentation

Document security measures:

```php
/**
 * Sanitize user input for database storage
 *
 * @since 1.0.0
 * @param string $input Raw user input
 * @return string Sanitized input safe for database storage
 */
function sanitize_user_input($input) {
    // Sanitize and validate input
    return sanitize_text_field($input);
}
```

## Performance Documentation

Document performance considerations:

```php
/**
 * Get cached product data
 *
 * Retrieves product data from cache if available, otherwise queries
 * the database and caches the result for 1 hour.
 *
 * @since 1.0.0
 * @param int $product_id Product ID
 * @return array|false Product data on success, false on failure
 */
function get_cached_product_data($product_id) {
    // Implementation with caching logic
}
```

## Internationalization Documentation

Document translatable strings:

```php
/**
 * Get localized error message
 *
 * @since 1.0.0
 * @param string $error_code Error code identifier
 * @return string Translated error message
 */
function get_error_message($error_code) {
    $messages = array(
        'invalid_input' => __('Invalid input provided.', 'bsawesome'),
        'access_denied' => __('Access denied.', 'bsawesome'),
    );
    
    return isset($messages[$error_code]) ? $messages[$error_code] : __('Unknown error.', 'bsawesome');
}
```

## Code Organization Standards

### File Organization
```php
<?php defined('ABSPATH') || exit;

/**
 * File header documentation
 */

// 1. Constants (if any)
const THEME_CONSTANT = 'value';

// 2. Global variables (avoid if possible)

// 3. Main functions
function main_function() {
    // Implementation
}

// 4. Helper functions
function helper_function() {
    // Implementation
}

// 5. Hook assignments
add_action('wp_enqueue_scripts', 'enqueue_theme_assets');
add_filter('body_class', 'add_custom_body_classes');

// 6. Class definitions (if any)
class Theme_Class {
    // Implementation
}
```

### Function Naming
- Use `bsawesome_` prefix for all theme functions
- Use descriptive names: `bsawesome_get_product_hover_image()`
- Use verbs for actions: `bsawesome_enqueue_assets()`
- Use nouns for getters: `bsawesome_cart_contents()`

### Variable Naming
```php
// Good examples
$product_id = get_the_ID();
$user_input = sanitize_text_field($_POST['input']);
$cache_key = 'bsawesome_products_' . $category_id;

// Avoid
$pid = get_the_ID();
$input = $_POST['input'];
$key = 'products_' . $category_id;
```

## Error Handling Documentation

```php
/**
 * Process form submission with error handling
 *
 * @since 1.0.0
 * @param array $form_data Form submission data
 * @return array {
 *     Processing result.
 *     
 *     @type bool   $success Whether processing succeeded
 *     @type string $message Success or error message
 *     @type array  $errors  Array of validation errors
 * }
 */
function process_form_submission($form_data) {
    // Implementation with error handling
}
```

## Translation Standards

### Text Domain Usage
Always use the theme text domain `'bsawesome'`:

```php
// Correct
__('Text to translate', 'bsawesome')
_e('Text to translate', 'bsawesome')
esc_html__('Text to translate', 'bsawesome')
esc_attr__('Text to translate', 'bsawesome')

// Incorrect
__('Text to translate', 'woocommerce')
__('Text to translate', 'textdomain')
```

### Translator Comments
Provide context for translators:

```php
/* translators: %s: Product name */
sprintf(__('Added %s to cart', 'bsawesome'), $product_name);

/* translators: Used in countdown timer */
__('Days', 'bsawesome');
```

## Common Patterns

### Safe Output Escaping
```php
// HTML content
echo esc_html($variable);

// Attributes
echo '<div class="' . esc_attr($css_class) . '">';

// URLs
echo '<a href="' . esc_url($link) . '">';

// Already escaped content (rare cases)
echo wp_kses_post($html_content);
```

### Safe Input Handling
```php
// Text fields
$clean_input = sanitize_text_field($_POST['field']);

// Email
$email = sanitize_email($_POST['email']);

// URLs
$url = esc_url_raw($_POST['url']);

// Numbers
$number = absint($_POST['number']);
```

## Documentation Tools

### PHPDoc Tags
Use these standard PHPDoc tags:

- `@since` - Version when added
- `@param` - Function parameters
- `@return` - Return value
- `@throws` - Exceptions thrown
- `@see` - Related functions/classes
- `@link` - External documentation
- `@todo` - Future improvements
- `@deprecated` - Deprecated functionality
- `@author` - Author information
- `@version` - Current version

### Code Examples in Documentation
```php
/**
 * Example function with usage
 *
 * @since 1.0.0
 * 
 * Example usage:
 * ```php
 * $result = bsawesome_example_function('param1', array('key' => 'value'));
 * if ($result['success']) {
 *     echo $result['message'];
 * }
 * ```
 * 
 * @param string $param1 Description
 * @param array  $param2 Description
 * @return array Result array
 */
```

---

## Validation Checklist

Before considering a file "production ready", ensure:

- [ ] File has proper header documentation
- [ ] All functions have complete PHPDoc blocks
- [ ] All classes have documentation
- [ ] Complex logic has explanatory comments
- [ ] All user inputs are sanitized
- [ ] All outputs are properly escaped
- [ ] Text domain is consistent (`'bsawesome'`)
- [ ] Function names use proper prefix
- [ ] No PHP syntax errors
- [ ] No WordPress coding standard violations
- [ ] Security best practices followed
- [ ] Performance considerations documented

---
*Last Updated: August 1, 2025*
*Document Version: 1.0*
