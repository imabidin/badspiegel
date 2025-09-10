<?php defined('ABSPATH') || exit;

/**
 * Site Legal Notice and Pricing Information Display
 *
 * Displays legal disclaimer about pricing, VAT inclusion, and crossed-out
 * price explanations with link to homepage for legal compliance.
 *
 * @version 2.5.0
 *
 * Features:
 * - Legal pricing disclaimer with VAT information
 * - Crossed-out price explanation for transparency
 * - Homepage link integration with accessibility labels
 * - Internationalized text content with sprintf formatting
 * - WordPress KSES post filtering for safe HTML output
 * - Dark theme styling with responsive typography
 * - Center-aligned layout for professional appearance
 *
 * Security Measures:
 * - ABSPATH protection against direct access
 * - wp_kses_post() filtering for safe HTML content
 * - esc_html__() escaping for all translatable strings
 * - sprintf() for safe string interpolation
 * - ARIA label compliance for accessibility
 *
 * Performance Features:
 * - Minimal HTML structure with efficient content rendering
 * - No external dependencies or JavaScript requirements
 * - Clean semantic markup for screen reader optimization
 * - Responsive text sizing with Bootstrap utilities
 *
 * Dependencies:
 * - WordPress internationalization functions
 * - WordPress KSES filtering system
 * - Bootstrap 5 for responsive typography and layout
 * - sprintf() for string formatting with variables
 *
 * @todo Content is final - no additional modifications planned
 */

/**
 * Display legal pricing disclaimer and site information
 *
 * Renders standardized legal text about pricing, VAT inclusion, and
 * crossed-out price explanations with embedded homepage link for
 * e-commerce legal compliance requirements.
 *
 * Legal Elements:
 * - VAT inclusion statement for German e-commerce law
 * - Crossed-out price explanation for price comparison clarity
 * - Homepage link with proper accessibility labeling
 * - Responsive text formatting for mobile readability
 *
 * Text Structure:
 * - Internationalized base text with placeholder for link
 * - sprintf() integration for safe variable interpolation
 * - KSES post filtering for secure HTML content
 * - ARIA labels for screen reader accessibility
 *
 * @return void Outputs complete legal notice HTML
 */
function site_note()
{
    // Define accessibility-compliant homepage link components
    $link_aria = esc_html__('Link zur Startseite', 'imabi');
    $link_text = esc_html__('Badspiegel.de', 'imabi');

    // Construct safe homepage link with accessibility features
    $link = sprintf(
        '<a href="/" aria-label="%s">%s</a>',
        $link_aria,
        $link_text
    );

    // Define legal disclaimer text with link placeholder
    $text = __(
        'Alle Preise inkl. der gesetzlichen MwSt. und die durchgestrichenen Preise entsprechen dem bisherigen Preis bei %s.',
        'imabi'
    );

    // Generate safe HTML output with embedded link
    $output = wp_kses_post(sprintf($text, $link));
?>
    <!-- Legal pricing disclaimer and site information -->
    <div class="text-bg-dark text-md-center small">
        <div class="container-md py">
            <p class="mb-0"><?php echo $output; ?></p>
        </div>
    </div>
<?php
}
