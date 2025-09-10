# PHP Documentation Guide for BadSpiegel Theme

**INSTRUCTION FOR LLMs: This is a comprehensive documentation standard guide. When documenting PHP files in this project, follow these exact patterns and templates. Always document in ENGLISH only.**

## LLM Instructions Summary

**CRITICAL RULES:**
1. **ALWAYS document in English** - never German
2. **Follow the exact templates** provided below
3. **Use appropriate template** based on file complexity (single vs multi-function)
4. **Include all required sections** as shown in examples
5. **Preserve important inline comments** that explain business logic
6. **Remove redundant/obvious comments**

---

## 1. File Header Documentation

### Complete File Header Template
Every PHP file should have a comprehensive header following this exact structure:

```php
<?php

/**
 * [Brief description of file function - one line]
 *
 * [Detailed description of functionality, features and purpose]
 *
 * Key Features:
 * - [Feature 1 with brief description]
 * - [Feature 2 with brief description]
 * - [Additional features as needed]
 *
 * Technical Implementation:
 * - [Technical implementation details]
 * - [Architecture decisions]
 * - [Integration points]
 *
 * Performance Features:
 * - [Caching strategies]
 * - [Memory optimizations]
 * - [Performance-critical implementations]
 *
 * Security Measures:
 * - [Security measure 1]
 * - [Security measure 2]
 * - [Additional security features]
 *
 * @version [Version number]
 * @package [package-name] (optional for larger systems)
 */
```

### PHP Opening Tag and Security Check
**Recommended format for all PHP files:**

```php
<?php defined('ABSPATH') || exit;
```

**When to use which form:**
- ✅ **Separate form**: For large files with extensive headers
- ✅ **Compact form**: For small, single-purpose files
- ✅ **Consistency**: Stay consistent within a project

**Advantages of separate form:**
- ✅ **Clarity**: Explicit security check
- ✅ **Debugging**: Easier error handling
- ✅ **Standards**: Matches setup.php practices

---

## 2. Code Structure and Sections

### Single-Function Files (IMPORTANT!)
**For files with only one function:**
- ✅ **Single DocBlock** as file header AND function documentation
- ❌ **NO** separate section separation
- ❌ **NO** duplicate documentation (file + function)

```php
<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [File title and function description combined]
 *
 * [Combined description of file AND the single function]
 *
 * @version [Version number]
 * @param [Type] $param [Description]
 * @return [Return-Type] [Description]
 */

function single_function() {
    // Direct code without additional DocBlock
}
```

### Multi-Function Files
**For complex files with multiple functions:**

#### Extended Section Structure (based on setup.php)
```php
// =============================================================================
// SECTION-NAME (UPPERCASE with meaningful description)
// =============================================================================
```

**Recommended Section Names:**
- `INTELLIGENT [FUNCTION-TYPE] FUNCTIONS` - For advanced/intelligent functions
- `CORE [SYSTEM-NAME] FUNCTIONS` - For core functionalities
- `[SYSTEM] INTEGRATION HOOKS` - For WordPress/WooCommerce hooks
- `UTILITY & HELPER FUNCTIONS` - For helper functions
- `ADVANCED [FEATURE] PROCESSING` - For complex feature sets

#### Structured Inline Documentation (New!)
**For complex functions with multiple steps:**

```php
function complex_processing_function($data) {
    // =================================================================
    // STEP 1: Initial data validation and sanitization
    // =================================================================

    // Business logic comment explaining WHY, not WHAT

    // =================================================================
    // STEP 2: Core processing with caching optimization
    // =================================================================

    // Technical decision comment

    // =================================================================
    // STEP 3: Final result preparation and return
    // =================================================================
}
```

#### Advanced Inline Block Documentation (From render.php)
**For major processing sections within functions:**

```php
function render_product_configurator() {
    // ======== AUTO-LOAD CONFIGURATION PROCESSING ========
    /**
     * Auto-Load Configuration System
     *
     * Processes URL parameters to automatically load saved configurations.
     * Supports the format: ?load_config={6-character-alphanumeric-code}
     *
     * Security measures:
     * - Validates code format with regex pattern
     * - Sanitizes input using WordPress functions
     * - Uses prepared statements for database queries
     * - Validates JSON integrity before processing
     *
     * Database integration:
     * - Queries wp_product_config_codes table
     * - Matches both config code and product ID
     * - Handles database errors gracefully
     * - Decodes and validates configuration data
     */
    $auto_load_config = null;
    // ... processing code ...
    // ======== END AUTO-LOAD CONFIGURATION PROCESSING ========

    // ======== OPTION GROUPING AND FILTERING ========
    /**
     * Option Grouping and Validation System
     *
     * Groups product options by their assigned categories and validates
     * that all groups exist in the global option groups configuration.
     * [Additional details...]
     */
    $product_options_grouped = [];
    // ... processing code ...
    // ======== END OPTION GROUPING AND FILTERING ========
}
```---

## 3. Function Documentation

### Standard DocBlock Format (Extended!)

```php
/**
 * [Brief, concise function description]
 *
 * [Detailed description of functionality, purpose and implementation.
 * Explain complex logic and technical decisions.]
 *
 * [Context-specific sections:]
 *
 * Interface Features: (for UI/rendering functions)
 * - [UI component 1 with description]
 * - [UI component 2 with description]
 * - [User interaction features]
 *
 * Data Processing: (for data manipulation functions)
 * - [Data validation steps]
 * - [Processing pipeline stages]
 * - [Output formatting requirements]
 *
 * Rendering Logic: (for template/output functions)
 * - [Template selection criteria]
 * - [Conditional rendering rules]
 * - [Output buffer management]
 *
 * Technical Implementation: (for complex algorithms)
 * - [Algorithm/logic details]
 * - [Performance optimizations]
 * - [Caching strategies]
 *
 * Processing Pipeline: (for multi-step functions)
 * 1. [Step 1 with description]
 * 2. [Step 2 with description]
 * 3. [Step 3 with description]
 *
 * Security Features: (for security-critical functions)
 * - [Security feature 1]
 * - [Security feature 2]
 *
 * Validation Pipeline: (for validation functions)
 * 1. [Check 1 with description]
 * 2. [Check 2 with description]
 *
 * Caching Strategy: (for performance-critical functions)
 * - [Caching mechanism]
 * - [Cache invalidation]
 * - [Performance metrics]
 *
 * Performance Optimizations: (for optimization-focused functions)
 * - [Optimization technique 1]
 * - [Optimization technique 2]
 * - [Memory/speed improvements]
 *
 * Dependencies: [function1(), function2()] (if present)
 *
 * @global type $global_var Description of global variable usage
 * @param string|int|array $param_name Parameter description with multiple types
 * @param bool $optional_param Optional parameter with default behavior (default: value)
 * @return type|WP_Error|null Return description with all possible types
 * @since 2.0.0 (only if updating existing function)
 *
 * @see function_name() For related functionality
 * @see Class::method() For related class methods
 *
 * @example
 * $result = function_name('example_input');
 * if (is_wp_error($result)) {
 *     handle_error($result);
 * }
 * // Expected output: 'processed_result'
 */
```### Extended Parameter Documentation
```php
/**
 * @global WC_Product $product Current WooCommerce product object
 * @global wpdb $wpdb WordPress database abstraction object
 * @param string|WC_Product $product Product ID or WC_Product object
 * @param array|null $post_data Optional POST data (default: global $_POST)
 * @param string|null $config_code Optional config code for loading saved configuration
 * @param int $option_order_start Starting order number for sequential numbering (default: 1)
 * @return array Array with 'base_price', 'additional_price', 'total_price', 'config_data'
 * @since 2.0.0 (only when updating existing functions)
 */
```

### Return Documentation with Error Handling
```php
/**
 * @return string|null Full file path if found, null if no match found
 * @return array|WP_Error Processing result on success, WP_Error on failure
 * @return void Outputs JSON response and exits (no return value)
 * @return int Final option order number for continued sequential numbering
 */
```

### Cross-Reference Documentation
```php
/**
 * @see get_product_options() For retrieving filtered product options
 * @see get_all_product_option_groups() For option group definitions
 * @see render_options_group() For individual option group rendering
 * @see Class::method() For related class methods
 * @see https://example.com/docs For external documentation
 */
```

---

## 4. Performance Documentation (NEW!)

### Caching Strategies (Based on setup.php)
```php
/**
 * Advanced Caching Implementation
 *
 * Multi-level caching strategy for optimal performance:
 *
 * Caching Strategy:
 * - Static cache for function-level results per request
 * - WordPress transients for persistent file-based data (1 hour TTL)
 * - Cache invalidation based on file modification timestamps
 * - Memory management with intelligent cache pruning (every 1000 operations)
 *
 * Performance Metrics:
 * - Cache hit ratio: ~85% for repeated calls
 * - Memory overhead: <2MB for typical operations
 * - File system calls reduced by 70%
 *
 * Cache Keys Structure:
 * - Static: {function_name}_{primary_key}
 * - Transient: {prefix}_{file_hash}_{mtime}
 * - Memory: Automatic pruning after 1000 cache operations
 */
```

### Memory Management
```php
/**
 * Memory Management Features:
 * - Static cache arrays for performance optimization
 * - Intelligent cache pruning instead of complete clearing
 * - Cache hit counters for memory leak prevention
 * - Fallback mechanisms for memory-constrained environments
 */
```

### Database Optimization
```php
/**
 * Database Optimization:
 * - Single query with JOIN optimization instead of multiple queries
 * - Prepared statements for security and performance
 * - Result caching to avoid repeated database hits
 * - Query result pagination for large datasets
 */
```

---

## 5. Security Documentation

### Security Measures Section
Document all security measures:

```php
/**
 * Security Measures:
 * - WordPress nonce verification (wp_verify_nonce)
 * - Input sanitization using sanitize_text_field()
 * - File extension whitelist validation
 * - Path traversal prevention with realpath() checks
 * - Rate limiting (session-based per IP tracking)
 * - File existence and readability verification
 * - SQL injection prevention with prepared statements
 */
```

### Robust Validation Pipeline
```php
/**
 * Multi-layer Validation Pipeline:
 * 1. Initial input type and format validation
 * 2. WordPress nonce verification for CSRF protection
 * 3. User capability and permission checks
 * 4. File/path security validation with whitelist approach
 * 5. Business logic validation (ranges, formats, etc.)
 * 6. Final sanitization before processing
 */
```

### Security-Critical Functions
- **Input Sanitization** with WordPress functions documented
- **Access Control** explicitly described
- **Rate Limiting** specified
- **Error Handling** without sensitive information

---

## 6. Extended Example Documentation

### Practical Examples with Context
```php
/**
 * @example Basic usage with error handling
 * $file_path = validate_file_path('contact_de');
 * if (is_wp_error($file_path)) {
 *     return send_modal_error($file_path);
 * }
 * // Success: $file_path = '/path/to/theme/html/contact_de.html'
 *
 * @example Advanced usage with caching
 * $options = get_product_options($product);
 * // Cache hit: Returns cached result from static array
 * // Cache miss: Processes full option filtering pipeline
 *
 * @example Complex configuration processing
 * $price_result = calculate_configured_product_price($product, 'ABC123');
 * // Returns: ['base_price' => 299.00, 'additional_price' => 50.00, 'total_price' => 349.00]
 */
```

### Use Cases with Business Context
```php
/**
 * Use Cases:
 * - Product configurator: Dynamic option filtering based on categories/attributes
 * - Cart integration: Price calculation for configured products
 * - Admin interface: Bulk management of price matrix assignments
 * - Performance optimization: Multi-level caching for repeated operations
 * - Error recovery: Graceful handling of missing or corrupted data files
 */
```

### API Integration Examples
```php
/**
 * Integration Examples:
 *
 * WooCommerce Cart:
 * add_filter('woocommerce_add_cart_item_data', 'product_configurator_add_cart_item_data', 10, 2);
 *
 * WordPress AJAX:
 * wp_ajax_load_modal_file -> load_modal_file_handler()
 *
 * Custom Hooks:
 * do_action('bsawesome_before_option_processing', $product, $options);
 */
```

---

## 7. Error Handling and Robustness

### WP_Error Integration (Extended)
```php
/**
 * Comprehensive Error Handling Strategy
 *
 * Error Response Pipeline:
 * 1. Input validation errors (WP_Error with validation context)
 * 2. File system errors (with fallback mechanisms)
 * 3. Database errors (with retry logic)
 * 4. Integration errors (with graceful degradation)
 *
 * @param string|WP_Error $error Error message or WP_Error object
 * @param int $code HTTP status code (default: 400)
 * @param array $context Additional error context for debugging
 * @return void Outputs JSON error response with proper headers and exits
 *
 * @example Error handling with context
 * try {
 *     $result = risky_operation($data);
 * } catch (Exception $e) {
 *     return new WP_Error('operation_failed', $e->getMessage(), ['context' => $data]);
 * }
 */
```

### Graceful Degradation
```php
/**
 * Fallback Strategies:
 * - Primary: Load from cache/database
 * - Secondary: Load from file system with error handling
 * - Tertiary: Use default values with user notification
 * - Final: Graceful system degradation with logging
 */
```

### Error Response Structures
```php
/**
 * Standardized Error Response Format:
 * {
 *   "success": false,
 *   "error": "human_readable_message",
 *   "error_code": "SYSTEM_ERROR_CODE",
 *   "context": {...additional_debug_info},
 *   "timestamp": "2025-01-01T12:00:00Z"
 * }
 */
```

---

## 8. AJAX Handler and System Integration

### AJAX Request Processing Pipeline
```php
/**
 * Advanced AJAX Request Processing Pipeline
 *
 * Request Lifecycle:
 * 1. Security validation (rate limiting + nonce verification)
 * 2. Input sanitization and type validation
 * 3. Business logic validation with fallback strategies
 * 4. Core processing with caching optimization
 * 5. Response formatting with error handling
 * 6. Output with proper HTTP headers and exit
 *
 * Security Layers:
 * - WordPress nonce verification
 * - User capability checks (if applicable)
 * - Rate limiting with IP tracking
 * - Input sanitization pipeline
 * - Output escaping for XSS prevention
 *
 * Expected POST Parameters:
 * - action: 'handler_action_name'
 * - nonce: WordPress nonce for security
 * - [specific_params]: Validated and sanitized input data
 */
```

### WordPress Integration Standards
```php
/**
 * WordPress Hook Registration and Integration
 *
 * Hook Registration Strategy:
 * - Authenticated users: wp_ajax_{action}
 * - Non-authenticated: wp_ajax_nopriv_{action}
 * - Priority considerations for hook conflicts
 * - Conditional registration based on context
 *
 * Handler Registration:
 * add_action('wp_ajax_load_modal_file', 'load_modal_file_handler');
 * add_action('wp_ajax_nopriv_load_modal_file', 'load_modal_file_handler');
 *
 * Capability Requirements:
 * - Public handlers: No capability required
 * - Admin handlers: 'manage_options' or specific capability
 * - Content handlers: 'edit_posts' or content-specific capability
 */
```

### System Integration Points
```php
/**
 * Integration Architecture:
 * - WooCommerce: Product data, cart hooks, checkout integration
 * - WordPress: User management, capability system, nonce handling
 * - Custom Systems: Configuration codes, price matrices, caching
 * - Frontend: AJAX endpoints, modal systems, form processing
 */
```

---

## 9. WordPress Integration

### Hook Documentation (Extended)
```php
/**
 * WordPress Hook Integration System
 *
 * Registers comprehensive integration points for modal content and product configurator:
 *
 * AJAX Handlers:
 * - wp_ajax_load_modal_file: Authenticated modal content loading
 * - wp_ajax_nopriv_load_modal_file: Public modal content access
 * - wp_ajax_save_config: Configuration saving (authenticated)
 *
 * WooCommerce Integration:
 * - woocommerce_get_price_html: Price prefix modification ("ab" for configurable products)
 * - woocommerce_add_cart_item_data: Configuration data storage in cart
 * - woocommerce_before_calculate_totals: Dynamic price calculation
 * - woocommerce_checkout_create_order_line_item: Order metadata storage
 *
 * Filter Priority Strategy:
 * - High priority (5): Core functionality that must run early
 * - Standard priority (10): Most integrations and modifications
 * - Low priority (15+): Final adjustments and cleanup operations
 */
```

### WordPress Standards Compliance
```php
/**
 * WordPress Coding Standards Compliance:
 * - Nonce verification for all form submissions
 * - Sanitization: sanitize_text_field(), wp_kses(), esc_html()
 * - Database: $wpdb->prepare() for all custom queries
 * - Capabilities: current_user_can() for permission checks
 * - Hooks: Proper add_action()/add_filter() usage with priorities
 * - Internationalization: __(), _e(), esc_html__() for all user-facing text
 */
```

### Performance Integration
```php
/**
 * WordPress Performance Integration:
 * - Object Cache: wp_cache_set/get for session data
 * - Transient API: set/get_transient for persistent caching
 * - Database optimization: Minimal queries with proper indexing
 * - Asset loading: wp_enqueue_script/style with dependencies
 * - Conditional loading: Load scripts only when needed
 */
```

---

## 10. Debugging and Development

### Debug Mode Features (Extended)
```php
if (defined('WP_DEBUG') && WP_DEBUG) {
    /**
     * Development-Only Functionality and Advanced Debugging
     *
     * Debug Features:
     * - Request/Response logging with timestamp and context
     * - Performance monitoring with execution time tracking
     * - Memory usage tracking and leak detection
     * - Cache hit/miss ratio reporting
     * - Error tracking with stack traces
     * - SQL query logging and optimization hints
     *
     * Development Tools:
     * - Function call tracing for complex operations
     * - Input/Output data dumps for troubleshooting
     * - Configuration validation and syntax checking
     * - System status reporting (file permissions, PHP settings)
     *
     * Security Note:
     * All debug features are automatically disabled in production when WP_DEBUG is false.
     * No sensitive information is logged in production environments.
     */
}
```

### Production vs Development
```php
/**
 * Environment-Specific Behavior:
 *
 * Development Mode (WP_DEBUG = true):
 * - Verbose error reporting with stack traces
 * - Performance profiling and memory tracking
 * - Cache debugging with hit/miss statistics
 * - Input validation with detailed feedback
 *
 * Production Mode (WP_DEBUG = false):
 * - Silent error handling with user-friendly messages
 * - Optimized performance with minimal logging
 * - Security-focused error responses
 * - Graceful degradation for system failures
 */
```

### Debugging Integration
```php
/**
 * Debug Integration Points:
 * - error_log() for server-side debugging
 * - wp_die() for development error display
 * - console.log() integration for frontend debugging
 * - WP_Error objects for structured error handling
 * - Custom debug constants for feature-specific debugging
 */
```

---

## 11. Checklist for New PHP Files

### Single-Function Files:
- [ ] **Separate Security Check**: `if (!defined('ABSPATH')) { exit; }`
- [ ] **Single combined DocBlock** (file + function)
- [ ] **No redundant section separation**
- [ ] **Structured inline comments** (business logic, technical decisions)
- [ ] **German comments translated to English**
- [ ] **Remove redundant comments** (obvious syntax)
- [ ] **@example only for complex functions**
- [ ] **Do NOT add @since** (but don't remove if present)
- [ ] **Performance considerations documented** (if relevant)

### Multi-Function Files:
- [ ] **Separate Security Check**: `if (!defined('ABSPATH')) { exit; }`
- [ ] **Complete file header** with Technical Implementation
- [ ] **Intelligent section separation** with meaningful names
- [ ] **Structured STEP comments** in complex functions
- [ ] **Every function has complete DocBlock** with extended types
- [ ] **Caching strategies documented** (if implemented)
- [ ] **Performance features described**
- [ ] **Security measures explicitly listed**
- [ ] **Error handling pipeline documented**
- [ ] **WordPress integration standards followed**
- [ ] **@example blocks for complex functions**
- [ ] **Dependencies and integration points documented**

### Performance-Critical Files:
- [ ] **Caching strategies detailed documented**
- [ ] **Memory management described**
- [ ] **Database optimization mentioned**
- [ ] **Cache invalidation documented**
- [ ] **Performance metrics mentioned** (if available)

### DRY-Compliance Check:
- [ ] **Information documented only once**?
- [ ] **No redundant feature lists**?
- [ ] **Dependencies only mentioned where relevant**?
- [ ] **@example only for actually complex functions**?
- [ ] **Important inline comments preserved and translated to English**?
- [ ] **Redundant comments removed**?
- [ ] **Single-Function = Single DocBlock**?
- [ ] **Multi-Function = Structured sections with clear separations**?

---

## 12. Inline Comments Within Functions (IMPORTANT!)

### What to KEEP:

**✅ Important inline comments that must be explained:**
- **Complex Logic**: Why certain decisions were made
- **Business Logic**: Specific rules or calculations
- **Security Checks**: Why certain validations are necessary
- **Performance Optimizations**: Cache strategies, database optimizations
- **WordPress-specific Implementations**: Hooks, filters, custom fields
- **Temporary Workarounds**: With explanation why they are necessary
- **Configuration Values**: Why certain values were chosen

```php
function woocommerce_template_loop_favourite_button() {
    global $product;

    $product_id = $product->get_id();
    $user_id = get_current_user_id();

    // Check if we're in a favourites context (different behavior)
    $is_favourites_context = apply_filters('bsawesome_favourites_context', false);

    $config_code = null;

    // Priority order for config code detection
    if ($is_favourites_context) {
        global $bsawesome_current_favourite_config;
        if (isset($bsawesome_current_favourite_config)) {
            $config_code = $bsawesome_current_favourite_config;
        }
    }

    // Fallback: Check URL parameters for configuration
    if (!$config_code) {
        if (isset($_GET['load_config']) && !empty($_GET['load_config'])) {
            $config_code = sanitize_text_field($_GET['load_config']);
        } elseif (isset($_GET['config_code']) && !empty($_GET['config_code'])) {
            $config_code = sanitize_text_field($_GET['config_code']);
        }
    }

    // Validate config code format (must be 6-character alphanumeric)
    if ($config_code && !preg_match('/^[A-Z0-9]{6}$/', $config_code)) {
        $config_code = null;
    }
}
```

### What to REMOVE:

**❌ Redundant comments that can be removed:**
- Repetition of function name
- Obvious PHP syntax explanations
- Redundant descriptions of standard WordPress functions
- Comments that only repeat the code in words

```php
// ❌ REMOVE - obvious
$product_id = $product->get_id(); // Get the product ID

// ❌ REMOVE - redundant
echo '<div class="card">'; // Output card div

// ❌ REMOVE - standard WordPress function
$user_id = get_current_user_id(); // Get current user ID using WordPress function
```

### Translation Rule for Existing Comments:

**German Comments → English Comments:**
```php
// ❌ GERMAN (convert)
// Prüfe, ob es sich um eine Seite handelt und ob der Seiten-Slug in der Liste steht

// ✅ ENGLISH (new version)
// Check if it's a page and if the page slug is in the removal list
```

### Comment Quality Check:

**Ask yourself for each comment:**
1. **Does the comment explain WHY, not WHAT?**
2. **Is the logic hard to understand without the comment?**
3. **Does the comment contain business logic or technical decisions?**
4. **Would a new developer understand this code without the comment?**

**If YES → Keep comment and translate to English**
**If NO → Remove comment**

---

## 15. HTML Template Documentation (NEW!)

### HTML Comment Structure (From render.php)
**For complex HTML sections with business logic:**

```php
<?php if ($total_steps > 1) { ?>
    <!-- MULTI-STEP CAROUSEL INTERFACE -->

    <!-- Configuration Header with Save/Load Controls -->
    <div id="productConfiguratorHeader" class="row g-2 align-items-center mb-1">
        <!-- Header content -->
    </div>

    <!-- Configuration Code Save/Load Interface -->
    <div id="productConfiguratorCode">
        <!-- Interface content -->
    </div>

    <!-- Progress Bar for Step Tracking -->
    <div id="productConfiguratorProgress" class="progress bg-secondary-subtle mb-1">
        <!-- Progress content -->
    </div>

<?php } else { ?>
    <!-- SINGLE-STEP INTERFACE for simple configurations -->
    <div id="productConfiguratorSingleStep">
        <!-- Single step content -->
    </div>
<?php } ?>
```

### HTML Documentation Best Practices
**Use descriptive HTML comments for:**
- ✅ **Major interface sections** - Carousel, forms, navigation
- ✅ **Conditional rendering blocks** - Multi-step vs single-step
- ✅ **Complex interactive elements** - Progress bars, indicators
- ✅ **Template integration points** - Where PHP logic affects HTML structure

**Avoid HTML comments for:**
- ❌ **Obvious structure** - Basic divs, simple containers
- ❌ **Styling-only elements** - Pure CSS layout helpers
- ❌ **Self-explanatory components** - Standard Bootstrap components

### Template Integration Documentation
```php
/**
 * Template Rendering with Output Buffering
 *
 * Uses output buffering for clean HTML generation and better performance.
 * Renders either multi-step carousel interface or single-step interface
 * based on the number of available option groups.
 *
 * Interface Selection Logic:
 * - Multi-step: When $total_steps > 1
 * - Single-step: When $total_steps = 1
 *
 * Output Buffer Benefits:
 * - Cleaner HTML generation
 * - Better error handling
 * - Performance optimization for large templates
 * - Memory management for complex rendering
 */

// Start output buffering for cleaner HTML rendering
ob_start();
?>
<!-- HTML template content -->
<?php
// Output the buffered content and clean buffer
echo ob_get_clean();
```

---

## 13. DRY Principle in Documentation (CRITICAL!)

### Avoid Common Redundancies

**❌ Typical repetitions:**
- Describing functionality in both file header and function DocBlock
- Listing dependencies multiple times (DocBlock + visible code)
- Same technical details in different sections
- @example blocks for self-explanatory, simple functions
- Inline comments that repeat the obvious

**✅ DRY Solutions:**

### Single-Function Files
```php
<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Zendesk Chat Integration for BadSpiegel Theme
 *
 * Floating chat button with German localization and intelligent page exclusions.
 *
 * Technical Implementation:
 * - Bootstrap dark button positioned bottom-right
 * - Excluded from checkout/cart to prevent purchase flow disruption
 * - Font Awesome message icon with accessibility attributes
 *
 * @return void Outputs HTML chat button and Zendesk script
 */

function zendesk_chat() {
    if (is_checkout() || is_cart()) {  // Code is self-explanatory
        return;
    }
    // ... rest of function
}
```

### Multi-Function Files
```php
<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Modal Content AJAX Handler - DRY Version
 *
 * Handles secure AJAX requests with comprehensive security measures.
 * All technical details, dependencies, and examples are documented once
 * in relevant function DocBlocks to avoid repetition.
 */

/**
 * Verify WordPress nonce for modal requests
 *
 * Dependencies: wp_verify_nonce(), sanitize_text_field()
 */
function verify_modal_nonce() { ... }
```

### DRY Checklist for Each File:
- [ ] Information documented only once?
- [ ] No redundant feature lists?
- [ ] Dependencies only mentioned where relevant?
- [ ] @example only for complex functions?
- [ ] **Important inline comments preserved and translated to English?**
- [ ] **Redundant comments removed?**
- [ ] Single-Function = Single DocBlock?

---

## 14. Best Practices (Revised)

### Do's (Extended)
✅ **Architecture & Structure:**
- **Use separate security check**: `if (!defined('ABSPATH')) { exit; }`
- **Intelligent section separation** with meaningful names
- **Structured STEP comments** for complex processing pipelines
- **Follow Single-Function = Single DocBlock** principle

✅ **Documentation Standards:**
- **Extended @param/@return** with alternative types (`string|WC_Product|null`)
- **Technical Implementation** section for complex systems
- **Caching Strategy** documented in detail
- **Performance Metrics** provided where available
- **Error Handling Pipeline** completely described

✅ **Code Quality:**
- **Preserve important inline comments** (business logic, technical decisions)
- **Translate German comments to English**
- **Strictly follow DRY principle** - document information only once
- **Implement graceful degradation** for critical systems

✅ **WordPress Integration:**
- **Set hook priorities strategically** (5/10/15+)
- **Comprehensive error handling** with WP_Error
- **Security-first approach** in all public endpoints
- **Performance-optimized WordPress API usage**

### Don'ts (Extended)
❌ **Architecture Errors:**
- **Don't** use compact PHP opening for complex files
- **Don't** create redundant documentation (file + function for single-function)
- **Don't** use vague section names
- **Don't** overuse STEP comments for simple functions

❌ **Documentation Errors:**
- **Don't** add @since to new functions
- **Don't** remove existing @since tags
- **Don't** list dependencies multiple times
- **Don't** ignore performance details for critical functions
- **Don't** leave caching strategies undocumented

❌ **Code Quality Errors:**
- **Don't** remove important inline comments (business logic, security, performance)
- **Don't** keep German comments - translate to English
- **Don't** over-comment self-explanatory code
- **Don't** implement error handling without fallback strategies

❌ **Integration Errors:**
- **Don't** register WordPress hooks without priority considerations
- **Don't** leave security measures undocumented
- **Don't** ignore performance implications of integrations
- **Don't** forget to mention WP_Error possibilities

---

## Conclusion

These documentation standards are based on excellent documentation from `modal.php`, optimized through proven practices from `setup.php` and extended with modern performance and caching strategies.

**Core Principles (Extended):**

- **Smart Architecture**: Separate security checks for complex systems, intelligent section separation
- **Advanced Documentation**: Technical Implementation, Caching Strategies, Performance Metrics
- **DRY-First**: Document information only once, but completely and with rich context
- **Context-Aware**: Strategically treat single-function vs multi-function files differently
- **Intelligent Inline Comments**: Preserve important business logic and technical decisions, remove redundant ones
- **English Documentation**: All comments in English (intelligently translate German)
- **Performance-First**: Document caching strategies, memory management, database optimization
- **WordPress Standards**: Established WordPress/WooCommerce practices with modern extensions
- **Robust Error Handling**: Comprehensive fallback strategies and graceful degradation

**New Insights from setup.php:**
- **Structured STEP comments** for complex processing pipelines
- **Multi-level caching documentation** with performance metrics
- **Advanced type hints** with alternative types (`string|WC_Product|null`)
- **Integration architecture** with hook priorities and system dependencies
- **Graceful degradation** for critical system components

**New Insights from render.php:**
- **HTML template documentation** with clear comment blocks
- **Early exit validation patterns** with user-friendly messages
- **Output buffering documentation** for clean rendering
- **@global usage documentation** for WordPress context
- **Graceful degradation strategies** for complex UI components

---

## 16. Advanced Validation and Early Exit Patterns (NEW!)

### Early Exit Documentation Pattern (From render.php)
**Document early exit strategies for better code flow:**

```php
/**
 * Main function to render the complete product configurator interface
 *
 * Rendering Logic:
 * - Early validation of product objects and option availability
 * - Sequential validation with user-friendly error messages
 * - Early exits prevent unnecessary processing and resource usage
 * - Graceful degradation for edge cases and missing data
 *
 * Validation Pipeline:
 * 1. Product object validation (instanceof WC_Product)
 * 2. Product options availability check
 * 3. Option groups configuration validation
 * 4. Auto-load configuration validation (if applicable)
 * 5. Final rendering decision based on available data
 *
 * Performance Benefits:
 * - Prevents unnecessary database queries
 * - Reduces memory usage for invalid states
 * - Improves user experience with immediate feedback
 * - Minimizes server load for edge cases
 */
function render_product_configurator()
{
    global $product;

    // Validate product object exists and is valid WooCommerce product
    if (!$product || !is_a($product, 'WC_Product')) {
        return; // Silent exit for invalid products
    }

    // Get current product ID for database queries and caching
    $product_id = $product->get_id();

    // Retrieve product-specific options using filtering system
    $product_options = get_product_options($product);

    // Exit early if no options are available for this product
    if (empty($product_options)) {
        return; // Silent exit when no configuration needed
    }

    // Retrieve global option group definitions
    $product_option_groups = get_all_product_option_groups();

    // Exit early if no option groups are defined globally
    if (empty($product_option_groups)) {
        return; // Silent exit for system configuration issues
    }

    // Continue with main processing...
}
```

### User-Friendly Error Messages
**Provide helpful feedback when rendering fails:**

```php
// Exit early with user-friendly message if no valid groups found
if (empty($used_groups)) {
    echo '<div class="alert alert-danger">No configuration options available.</div>';
    return;
}

// Alternative: More specific error messages
if (empty($product_options)) {
    echo '<div class="alert alert-info">This product does not require configuration.</div>';
    return;
}

if (!$product || !is_a($product, 'WC_Product')) {
    echo '<div class="alert alert-warning">Product information not available.</div>';
    return;
}
```

### Graceful Degradation Documentation
```php
/**
 * Graceful Degradation Strategy:
 * - Primary: Full multi-step interface with all features
 * - Secondary: Single-step interface for simple configurations
 * - Tertiary: Silent exit for products without configuration needs
 * - Final: User-friendly error messages for system issues
 *
 * Fallback Logic:
 * - Template missing: Use default rendering
 * - Function missing: Use function_exists() checks
 * - Data missing: Provide sensible defaults
 * - System error: Log error and degrade gracefully
 */
```

---

## Final LLM Usage Instructions

**This .docguide is optimized for LLM comprehension. When an LLM reads this guide:**

1. **Follow the exact templates** provided for each function type
2. **Use English only** for all documentation
3. **Include performance implications** for complex functions
4. **Document error handling** and graceful degradation
5. **Explain business logic** clearly for future developers
6. **Use structured comments** (STEP 1, STEP 2, etc.) for complex processes
7. **Document HTML templates** with clear comment blocks
8. **Include validation patterns** and early exit strategies

**This guide contains real-world examples from the BadSpiegel Theme codebase, specifically from setup.php and render.php, ensuring practical and tested documentation patterns.**

Consistent, extended documentation with intelligent performance optimization and robust error handling significantly improves maintainability, reduces maintenance effort and maximizes developer experience in complex WordPress/WooCommerce environments.
