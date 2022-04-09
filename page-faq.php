<?php
/**
* Template Name: FAQ Page
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
	<!-- <?php wp_head(); ?> -->
	<script src='https://api.mapbox.com/mapbox-gl-js/v2.7.0/mapbox-gl.js'></script>
	<script src='<?php echo get_template_directory_uri()?>/assets/lib/bootstrap.min.js'></script>
	<script src='<?php echo get_template_directory_uri()?>/assets/lib/jquery-3.6.0.min.js'></script>

	<link href='https://api.mapbox.com/mapbox-gl-js/v2.7.0/mapbox-gl.css' rel='stylesheet' />
	<link type="text/css" rel="stylesheet" href="<?php echo get_template_directory_uri()?>/assets/lib/bootstrap.min.css">
	<link type="text/css" rel="stylesheet" href="<?php echo get_template_directory_uri()?>/custom.css">
	<script>
		var ajax_url = '<?php echo admin_url('admin-ajax.php')?>';
	</script>
</head>
<body <?php body_class(); ?>>
	<div class="home-header page-header">
		<div class="home-header-left">
			<h1 class="home-header-title">FAQs</h1>
			<h3 class="home-header-sub-title">GROW WITH US IN THE METAVERSE LAND BOOM!</h3>
		</div>
		<div class="home-header-right">
			<a class="home-header-link" href="/">Home</a>
			<a class="home-header-link" href="/buy-land-map-page/">Buy Land Now Map</a>
			<a class="home-header-link" href="/for-sale-by-owner-map/">For Sale By Owner Map</a>
			<a class="home-header-link">Signup/Login</a>
		</div>
	</div>
	<div class="page-content page-content-faq">
		<div class="page-wrap">
			<div class="faq-p">
				<p class="color-red">What is BOGO, buy one get one free? </p>
				<p>Simple, sign up, buy any parcel and get one free. Limit 10 parcels per member, buy 10 get 10 free <span class="color-red">BUT HURRY: This is a limited introductory offer and will end at any time without notice.</span></p>
			</div>
			<div class="faq-p">
				<p class="color-red">What does guarantee purchase prices will always go up, never down mean? </p>
				<p>The purchase price is the price per parcel to buy that parcel and are set by metaverseland.today. Purchase prices are set the same for every parcel USA. Purchase prices will never be set lower than what you pay/paid at any time. <b>Purchase prices will always will go up, never down according to website demand. That's our guarantee.</b></p>
			</div>
			<div class="faq-p">
				<p class="color-red">What happens to the parcels I buy? </p>
				<p>Every parcel purchased on metaverseland.today will be marked red on the buy land now map, and if clicked it will link to you. It will appear in your owner’s members page, and be uploaded automatically onto the for sale by owner map, with a default make offer link to you. When an offer is made you will receive an email.</p>
			</div>
			<div class="faq-p">
				<p class="color-red">Can I set my own resale price instead of make offer? </p>
				<p>Absolutely. Just change the default make offer to a set price at any time in your members page to any price you want. The price you set will now appear on the for sale by owner list, from make offer, to your set price. REMEMBER: the METAVERSE is unlimited!! </p>
			</div>
			<div class="faq-p">
				<p class="color-red">Can I sell properties outside the website? </p>
				<p>No, all properties are sold only in the metaverseland.today world. Only members can sell from member to member.<span class="color-red">No purchase is necessary to become a member.</span></p>
			</div>
			<div class="faq-p">
				<p class="color-red">If I become an affiliate are there any limits? </p>
				<p>No, bring as many members as you like. If your member makes any purchase you will get a free parcel. Sorry, only 1 free parcel per each new member you bring. Sign up today!</p>
			</div>
			<div class="faq-p">
				<p class="color-red">Do you sell my information? </p>
				<p>We take your privacy very seriously and will never give out or sell any of your information. </p>
			</div>
			<div class="faq-p">
				<p class="color-red">Do I need a cryptocurrency account? </p>
				<p><b>No, you can buy in with cash, credit card or cryptocurrency using Stripe, PayPal cash, PayPal Crypto and Coinbase Crypto All prices are in simple USD</b></p>
			</div>
			<div class="faq-p">
				<p>Contact</p>
				<p>support@metaverseland.today</p>
			</div>
		</div>
	</div>
	
<?php wp_footer(); ?>
</body>
</html>
