<?php defined('ABSPATH') || exit;

// Falls über `get_template_part()` mit `$args` gearbeitet wird:
$text_domain = ! empty( $args['text_domain'] ) ? $args['text_domain'] : 'my-product-configurator';

?>

<abbr class="required text-danger" title="<?php echo esc_attr__( 'erforderlich', $text_domain ); ?>">*</abbr>
