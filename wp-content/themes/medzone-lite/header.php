<?php

/**
 * File that renders the theme Header
 *
 * @package MedZone_Lite
 */

?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>

</head>

<?php
$uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri_segments = explode('/', $uri_path);
?>

<body <?php (in_array($uri_segments[3], ["contact", "our-service"])) ? "" : body_class('sticky-header'); ?>>
	<?php wp_body_open() ?>
	<div id="wrap">
		<div id="header">
			<!-- /// HEADER  //////////////////////////////////////////////////////////////////////////////////////////////////////////// -->
			<div class="container">
				<div class="row">
					<?php
					get_template_part('template-parts/misc/logo');
					get_template_part('template-parts/header/menu');
					?>
				</div><!-- end .row -->
			</div><!-- end .container -->
			<!-- //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// -->
		</div>