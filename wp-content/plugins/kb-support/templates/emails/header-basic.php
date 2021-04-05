<?php
/**
 * Email Header (Basic)
 *
 * @author 		KB Support
 * @package 	KB Support/Templates/Emails
 * @version     1.1.10
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

$header_img = kbs_get_option( 'email_logo', '' );
$heading    = KBS()->emails->get_heading();
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title><?php echo get_bloginfo( 'name' ); ?></title>
	</head>
    <body>
    	<div>
			<?php if( ! empty( $header_img ) ) : ?>
                <div id="template_header_image">
                    <?php echo '<p style="margin-top:0;"><img src="' . esc_url( $header_img ) . '" alt="' . get_bloginfo( 'name' ) . '" /></p>'; ?>
                </div>
			<?php endif; ?>
			<?php if ( ! empty ( $heading ) ) : ?>
                <!-- Header -->
                <h1><?php echo $heading; ?></h1>
                <!-- End Header -->
            <?php endif; ?>