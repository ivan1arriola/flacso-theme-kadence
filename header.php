<?php
/**
 * Header for child theme with Bootstrap support
 *
 * @package kadence-child
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<!doctype html>
<html <?php language_attributes(); ?> class="no-js">
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">

    <meta name="theme-color" content="#1d3a72">

    <?php if ( is_singular() ) : ?>
        <meta name="page-id" content="<?php echo esc_attr( (string) get_the_ID() ); ?>">
        <meta name="page-slug" content="<?php echo esc_attr( basename( get_permalink() ) ); ?>">
        <meta name="page-type" content="<?php echo esc_attr( (string) get_post_type() ); ?>">
        <meta name="page-title" content="<?php echo esc_attr( get_the_title() ); ?>">
    <?php endif; ?>

    <?php
    /**
     * Hook para insertar CSS y JS
     * wp_head() cargará Bootstrap, jQuery UI y los estilos del child theme
     */
    wp_head();
    ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#inner-wrap"><?php esc_html_e( 'Saltar al contenido principal', 'kadence-child-flacso' ); ?></a>

<?php
// Kadence before wrapper hook
do_action( 'kadence_before_wrapper' );
?>
<div id="wrapper" class="site wp-site-blocks">
    <?php
    // Kadence antes del header
    do_action( 'kadence_before_header' );

    // Header principal
    do_action( 'kadence_header' );

    // Kadence after header
    do_action( 'kadence_after_header' );
    ?>

    <div id="inner-wrap" class="wrap kt-clear">
        <?php
        do_action( 'kadence_before_content' );
        ?>
