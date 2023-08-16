<?php
/**
 * Template part for displaying Previous/Next Post section.
 *
 * @package     Ds8
 * @author      Ds8 Team <hello@ds8wp.com>
 * @since       1.0.0
 */

// Do not show if post is password protected.
if ( post_password_required() ) {
	return;
}

$ds8_next_post = get_next_post();
$ds8_prev_post = get_previous_post();

// Return if there are no other posts.
if ( empty( $ds8_next_post ) && empty( $ds8_prev_post ) ) {
	return;
}
?>

<?php do_action( 'ds8_entry_before_prev_next_posts' ); ?>
<div data-test="container" class="container rowCompartir">
        
        <a class="btn-default btn Ripple-parent btn-sm btn-color-primary" href="http://glratings.con/leyesnormativas">Leyes y Normativas</a>
	<?php

	// Previous post link.
        previous_post_link( '%link', 'Anterior');
        
	// Next post link.
        next_post_link( '%link', 'Siguiente');

	?>
</div>
<?php do_action( 'ds8_entry_after_prev_next_posts' ); ?>
