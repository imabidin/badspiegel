# Modal Category System - Performance & Readability Improvements

## Problem

The original modal system had several readability and performance issues:

```php
// Old approach - hard to read and inefficient
<?php if (modal_has_product_category('spiegelschraenke-mit-faechern') || modal_has_product_category('badspiegel-mit-rahmen-aus-holz-und-ablage')): ?>
```

**Issues:**
- Long, unreadable conditionals
- Multiple function calls for the same data
- Hard-coded category slugs throughout templates
- Performance overhead from repeated category lookups

## Solution: Pre-computed Category Flags

### Backend Implementation

The system now pre-computes boolean flags for all known categories during modal context setup:

```php
// In setup_modal_product_context()
$modal_context['category_flags'] = compute_modal_category_flags($product_categories);
```

**Key Functions:**
- `compute_modal_category_flags($categories)` - Computes all category flags once
- `get_modal_category_flags()` - Returns computed flags
- `modal_category_flag($flag_name)` - Convenience function for single flag checks
- `modal_has_any_category($category_slugs)` - OR logic for multiple categories
- `modal_has_all_categories($category_slugs)` - AND logic for multiple categories  
- `modal_category_condition($expression)` - Boolean expression evaluator

### Template Usage

#### Approach 1: Individual Flag Variables (Recommended)
```php
<?php
// Get flags once at the top of template
$flags = get_modal_category_flags();

// Extract to readable variables
$is_badspiegel = $flags['is_badspiegel'] ?? false;
$is_spiegelschrank = $flags['is_spiegelschraenke'] ?? false;
$is_badspiegel_mit_rahmen = $flags['is_badspiegel_mit_rahmen_aus_holz_und_ablage'] ?? false;

// Compute business logic with semantic names
$show_spiegelkante_info = $is_badspiegel || !$is_badspiegel_mit_rahmen;
$show_korpuskante_info = $is_spiegelschrank || $is_badspiegel_mit_rahmen;
?>

<!-- Clean, readable template conditions -->
<?php if ($show_spiegelkante_info): ?>
<li>Maße sind von einer Spiegelkante bis zur nächsten Kante</li>
<?php endif; ?>
```

#### Approach 2: Direct Flag Access
```php
<?php if (modal_category_flag('is_badspiegel')): ?>
<li>Specific content for Badspiegel</li>
<?php endif; ?>
```

#### Approach 3: Multi-Category Helpers
```php
<?php if (modal_has_any_category(['badspiegel', 'spiegelschraenke'])): ?>
<li>Content for either category</li>
<?php endif; ?>

<?php if (modal_has_all_categories(['badspiegel', 'badspiegel-mit-beleuchtung'])): ?>
<li>Content for products with both categories</li>
<?php endif; ?>
```

#### Approach 4: Boolean Expression Evaluator
```php
<?php if (modal_category_condition('badspiegel OR spiegelschraenke')): ?>
<li>Content using boolean logic</li>
<?php endif; ?>

<?php if (modal_category_condition('(spiegelschraenke-mit-faechern OR badspiegel-mit-rahmen-aus-holz-und-ablage) AND NOT badspiegel')): ?>
<li>Complex boolean logic</li>
<?php endif; ?>
```

#### Approach 5: Complex Logic Variables
```php
<?php
$flags = get_modal_category_flags();
$show_complex_info = ($flags['is_spiegelschraenke_mit_faechern'] ?? false) || 
                     ($flags['is_badspiegel_mit_rahmen_aus_holz_und_ablage'] ?? false);
?>

<?php if ($show_complex_info): ?>
<li>Complex conditional content</li>
<?php endif; ?>
```

## Performance Benefits

### Before (Multiple Function Calls)
```php
// Each modal_has_product_category() loops through all categories
if (modal_has_product_category('cat1') || modal_has_product_category('cat2')) // 2 loops
if (modal_has_product_category('cat3') || modal_has_product_category('cat4')) // 2 more loops  
// Total: 4+ category array iterations per template
```

### After (Single Computation)
```php
// One-time computation during context setup
$flags = compute_modal_category_flags($categories); // 1 loop total
// All subsequent checks are simple array lookups: O(1)
```

**Performance Improvements:**
- **Reduced Complexity:** O(n*m) → O(n) where n=categories, m=template checks
- **Single Loop:** Category array traversed only once during setup
- **Cached Results:** Boolean flags cached in modal context
- **Memory Efficient:** Small boolean array vs repeated function calls

## Maintenance

### Adding New Categories

1. **Update `compute_modal_category_flags()`:**
```php
$known_categories = array(
    'badspiegel',
    'spiegelschraenke',
    'your-new-category', // Add here
);
```

2. **Flag Name Convention:**
- Category slug: `badspiegel-mit-beleuchtung`  
- Flag name: `is_badspiegel_mit_beleuchtung`
- Rule: `is_` + `str_replace('-', '_', $category_slug)`

3. **Template Usage:**
```php
$is_your_new_category = $flags['is_your_new_category'] ?? false;
```

### Backwards Compatibility

The original `modal_has_product_category()` function remains available for backwards compatibility, but new templates should use the flag system.

## File Structure

```
inc/modal.php
├── compute_modal_category_flags()     # Core computation function
├── get_modal_category_flags()         # Context accessor
├── modal_category_flag()              # Convenience helper
└── setup_modal_product_context()     # Integration point

templates/
├── breite.html                        # Example implementation
└── other-templates.html               # Should migrate to new system
```

## Best Practices

### ✅ Do
- Extract flags to semantic variable names
- Group related business logic
- Use meaningful variable names that explain the business intent
- Compute complex conditions once and store in variables

### ❌ Don't  
- Mix old and new approaches in the same template
- Use flag names directly in complex conditionals
- Forget to handle missing flags with `?? false`
- Add categories without updating the known_categories array

## Migration Guide

### Step 1: Identify Templates with Category Checks
```bash
grep -r "modal_has_product_category" html/
```

### Step 2: Replace with Flag System
```php
// Old
<?php if (modal_has_product_category('cat1') || modal_has_product_category('cat2')): ?>

// New  
<?php
$flags = get_modal_category_flags();
$show_content = ($flags['is_cat1'] ?? false) || ($flags['is_cat2'] ?? false);
?>
<?php if ($show_content): ?>
```

### Step 3: Test Performance
- Monitor AJAX response times
- Check for proper flag computation
- Verify category detection still works correctly

## Examples

See `html/configurator/masse/breite.html` for a complete implementation example.
