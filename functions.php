<?php
/**
 * Core functions for the Rebecca Mercier block theme.
 *
 * @package rebecca-mercier-theme
 */

if ( ! function_exists( 'rebeccamercier_setup' ) ) {

	/**
	 * Theme setup.
	 */
	function rebeccamercier_setup() {

		// Make theme available for translation.
		load_theme_textdomain(
			'rebecca-mercier-theme',
			get_template_directory() . '/languages'
		);

		// Editor stylesheet (keeps editor closer to front-end).
		add_editor_style( get_template_directory_uri() . '/style.css' );

		// We’ll use our own patterns, not the core ones.
		remove_theme_support( 'core-block-patterns' );
	}
}
add_action( 'after_setup_theme', 'rebeccamercier_setup' );

/**
 * Front-end assets.
 */
function rebeccamercier_enqueue_assets() {

	wp_enqueue_style(
		'rebeccamercier-style',
		get_template_directory_uri() . '/style.css',
		array(),
		wp_get_theme()->get( 'Version' )
	);

	// Enqueue theme JavaScript for parallax and scroll effects
	wp_enqueue_script(
		'rebeccamercier-theme',
		get_template_directory_uri() . '/assets/js/theme.js',
		array(),
		wp_get_theme()->get( 'Version' ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'rebeccamercier_enqueue_assets' );

/**
 * Register custom block styles (same as Frost, just renamed).
 */
function rebeccamercier_register_block_styles() {

	$block_styles = array(
		'core/columns'      => array(
			'columns-reverse' => __( 'Reverse', 'rebecca-mercier-theme' ),
		),
		'core/group'        => array(
			'shadow-light' => __( 'Shadow', 'rebecca-mercier-theme' ),
			'shadow-solid' => __( 'Solid', 'rebecca-mercier-theme' ),
		),
		'core/list'         => array(
			'no-disc' => __( 'No Disc', 'rebecca-mercier-theme' ),
		),
		'core/quote'        => array(
			'shadow-light' => __( 'Shadow', 'rebecca-mercier-theme' ),
			'shadow-solid' => __( 'Solid', 'rebecca-mercier-theme' ),
		),
		'core/social-links' => array(
			'outline' => __( 'Outline', 'rebecca-mercier-theme' ),
		),
	);

	foreach ( $block_styles as $block => $styles ) {
		foreach ( $styles as $style_name => $style_label ) {
			register_block_style(
				$block,
				array(
					'name'  => $style_name,
					'label' => $style_label,
				)
			);
		}
	}
}
add_action( 'init', 'rebeccamercier_register_block_styles' );

/**
 * Register block pattern categories.
 */
function rebeccamercier_register_block_pattern_categories() {

	register_block_pattern_category(
		'rebeccamercier-page',
		array(
			'label'       => __( 'Page', 'rebecca-mercier-theme' ),
			'description' => __( 'Full page layouts for the site.', 'rebecca-mercier-theme' ),
		)
	);

	register_block_pattern_category(
		'rebeccamercier-pricing',
		array(
			'label'       => __( 'Pricing', 'rebecca-mercier-theme' ),
			'description' => __( 'Pricing / comparison layouts.', 'rebecca-mercier-theme' ),
		)
	);
}
add_action( 'init', 'rebeccamercier_register_block_pattern_categories' );

/**
 * Add favicon to site.
 */
function rebeccamercier_add_favicon() {
	echo '<link rel="icon" type="image/png" href="' . get_template_directory_uri() . '/assets/images/favicon.png">';
	echo '<link rel="apple-touch-icon" href="' . get_template_directory_uri() . '/assets/images/favicon.png">';
}
add_action( 'wp_head', 'rebeccamercier_add_favicon' );

/**
 * Handle contact form submission.
 */
function rebeccamercier_handle_contact_form() {
	if ( isset( $_POST['rebeccamercier_contact_submit'] ) ) {
		// Verify nonce
		if ( ! isset( $_POST['rebeccamercier_contact_nonce'] ) || 
			 ! wp_verify_nonce( $_POST['rebeccamercier_contact_nonce'], 'rebeccamercier_contact_form' ) ) {
			return;
		}

		// Sanitize inputs
		$first_name = sanitize_text_field( $_POST['first_name'] ?? '' );
		$last_name = sanitize_text_field( $_POST['last_name'] ?? '' );
		$email = sanitize_email( $_POST['email'] ?? '' );
		$message = sanitize_textarea_field( $_POST['message'] ?? '' );

		// Validate required fields
		if ( empty( $first_name ) || empty( $last_name ) || empty( $email ) || empty( $message ) ) {
			set_transient( 'rebeccamercier_form_error', 'Please fill in all required fields.', 30 );
			return;
		}

		if ( ! is_email( $email ) ) {
			set_transient( 'rebeccamercier_form_error', 'Please enter a valid email address.', 30 );
			return;
		}

		// Build email
		$to = 'rebeccaturley333@gmail.com';
		$subject = 'New Contact Form Submission from ' . $first_name . ' ' . $last_name;
		$body = "You have received a new message from your website contact form.\n\n";
		$body .= "Name: " . $first_name . " " . $last_name . "\n";
		$body .= "Email: " . $email . "\n\n";
		$body .= "Message:\n" . $message . "\n";
		$headers = array(
			'Content-Type: text/plain; charset=UTF-8',
			'Reply-To: ' . $first_name . ' ' . $last_name . ' <' . $email . '>',
		);

		// Send email
		$sent = wp_mail( $to, $subject, $body, $headers );

		if ( $sent ) {
			set_transient( 'rebeccamercier_form_success', 'Thank you for your message! Rebecca will be in touch soon.', 30 );
		} else {
			set_transient( 'rebeccamercier_form_error', 'There was an issue sending your message. Please try calling instead.', 30 );
		}

		// Redirect to prevent form resubmission
		wp_redirect( $_SERVER['REQUEST_URI'] );
		exit;
	}
}
add_action( 'init', 'rebeccamercier_handle_contact_form' );

/**
 * Display contact form.
 */
function rebeccamercier_contact_form_shortcode() {
	$output = '';

	// Check for success/error messages
	$success = get_transient( 'rebeccamercier_form_success' );
	$error = get_transient( 'rebeccamercier_form_error' );

	if ( $success ) {
		$output .= '<div class="form-message form-success">' . esc_html( $success ) . '</div>';
		delete_transient( 'rebeccamercier_form_success' );
	}

	if ( $error ) {
		$output .= '<div class="form-message form-error">' . esc_html( $error ) . '</div>';
		delete_transient( 'rebeccamercier_form_error' );
	}

	$output .= '<form class="contact-form" method="post" action="">';
	$output .= wp_nonce_field( 'rebeccamercier_contact_form', 'rebeccamercier_contact_nonce', true, false );
	$output .= '<div class="form-row">';
	$output .= '<div class="form-group form-group-half">';
	$output .= '<input type="text" name="first_name" placeholder="First Name *" required>';
	$output .= '</div>';
	$output .= '<div class="form-group form-group-half">';
	$output .= '<input type="text" name="last_name" placeholder="Last Name *" required>';
	$output .= '</div>';
	$output .= '</div>';
	$output .= '<div class="form-group">';
	$output .= '<input type="email" name="email" placeholder="Email Address *" required>';
	$output .= '</div>';
	$output .= '<div class="form-group">';
	$output .= '<textarea name="message" rows="5" placeholder="Your message... *" required></textarea>';
	$output .= '</div>';
	$output .= '<div class="form-group">';
	$output .= '<button type="submit" name="rebeccamercier_contact_submit" class="wp-block-button__link">Send Message</button>';
	$output .= '</div>';
	$output .= '</form>';

	return $output;
}
add_shortcode( 'contact_form', 'rebeccamercier_contact_form_shortcode' );
