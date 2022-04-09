<?php
/**
* Template Name: Home Page
*
* @package WordPress
* @subpackage 
* @since 
*/

?>
<!doctype html>
<html>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
	<link type="text/css" rel="stylesheet" href="<?php echo get_template_directory_uri()?>/custom.css">
</head>
<body <?php body_class(); ?>>
	<div class="home-wrap">
		<div class="home-header">
			<div class="home-header-left">
				<h1 class="home-header-title">Metaverse Land Today</h1>
				<h3 class="home-header-sub-title">The Home of Afforadable Metaverse Land Deals</h3>
			</div>
			<div class="home-header-right">
				<a class="home-header-link" href="/buy-land-map-page/">Buy Land Now Map</a>
				<a class="home-header-link" href="/for-sale-by-owner-map/">For Sale By Owner Map</a>
				<a class="home-header-link" href="/faq">FAQ</a>
				<a class="home-header-link">Signup/Login</a>
			</div>
		</div>
		<div class="home-content">
			<div class="home-content-center">
				<p>Comming Soon! </p>
			</div>
			<div class="home-content-bottom">
				<div class="home-content-bottom-left">
					<h2>OUR GUARANTEE IS SIMPLE</h2>
					<p> 1) Values Will Never Go DOWN</p>
					<p> 2) Values Will Always Go UP</p>
				</div>
				<div class="home-content-bottom-right">
					<h3>DON'T MISS OUT ON THIS</h3>
					<h3>GROUNDBREAKING</h3>
					<h3>INVESTMENT</h3>
					<h3>OPPORTUNITY</h3>
					<h2>JOIN NOW!</h2>
				</div>
			</div>
		</div>
	</div>
<?php wp_footer(); ?>
</body>
</html>
