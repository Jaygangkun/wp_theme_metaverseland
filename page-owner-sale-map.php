<?php
/**
* Template Name: For Sale By Owner Map Page
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
	<script src='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v4.7.0/mapbox-gl-geocoder.min.js'></script>
	<link rel='stylesheet' href='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v4.7.0/mapbox-gl-geocoder.css' type='text/css' />

	<link type="text/css" rel="stylesheet" href="<?php echo get_template_directory_uri()?>/assets/lib/bootstrap.min.css">
	<link type="text/css" rel="stylesheet" href="<?php echo get_template_directory_uri()?>/custom.css">
	<script>
		var ajax_url = '<?php echo admin_url('admin-ajax.php')?>';
	</script>
</head>
<body <?php body_class(); ?>>
	<div class="home-header page-header">
		<div class="home-header-left">
			<h1 class="home-header-title">For Sale By Owner Map</h1>
			<h3 class="home-header-sub-title">The Home of Afforadable Metaverse Land Deals</h3>
		</div>
		<div class="home-header-right">
			<a class="home-header-link" href="/">Home</a>
			<a class="home-header-link" href="/buy-land-map-page/">Buy Land Now Map</a>
			<a class="home-header-link" href="/faq">FAQ</a>
			<a class="home-header-link">Signup/Login</a>
		</div>
	</div>
	<div class="page-content">
		<div class="map-wrap">
			<div id='map'></div>
		</div>
	</div>
	<script>
		var blogin = false;
		<?php 
		if(is_user_logged_in()) {
			?>
			blogin = true;
			<?php
		}
		?>
		var cur_map_popup = null;
		var parcel_clicked = false;

		var buy_parcel_address = '';
		var buy_parcel_lat = '';
		var buy_parcel_lng = '';
		var buy_parcel_country = '';

		var parcel_price = '';
		<?php 
		$option_metaverseland_setting_parcel_price = get_option('metaverseland_setting_parcel_price');
		if($option_metaverseland_setting_parcel_price) {
			?>
			parcel_price = <?php echo $option_metaverseland_setting_parcel_price['parcel_price']?>;
			<?php
		}
		?>
		mapboxgl.accessToken = 'pk.eyJ1IjoibWV0YXZlcnNlIiwiYSI6ImNsMHNhdDFqNDAxbDIzcHBmZ2RkejZmNXEifQ.Tbn_kA1pvvrWUQgVQ0YJWg';
		const map = new mapboxgl.Map({
			container: 'map', // container ID
			style: 'mapbox://styles/mapbox/streets-v11', // style URL
			center: [-101.299591, 40.116386], // starting position [lng, lat]
			zoom: 3.5 // starting zoom,
		});

		map.addControl(new mapboxgl.NavigationControl());
 
		const geocoder = new MapboxGeocoder({
			// Initialize the geocoder
			accessToken: mapboxgl.accessToken, // Set the access token
			mapboxgl: mapboxgl, // Set the mapbox-gl instance
			marker: false // Do not use the default marker style
		});


		// Add the geocoder to the map
		map.addControl(geocoder);

		var owners_parcel_map_data = {};
		var initial_parcel_map_data = {
			type: "FeatureCollection",
			features: []
		}
		var owner_id = '';
		var feature = '';
		<?php
		// create map point data
		$purchased_parcels = get_posts(array(
			'post_type' => 'parcel',
			'numberposts' => -1,
			'post_status' => array('private')
		));
		
		foreach($purchased_parcels as $purchased_parcel) {
			$parcel_lat = get_post_meta($purchased_parcel->ID, 'lat', true);
			$parcel_lng = get_post_meta($purchased_parcel->ID, 'lng', true);
			$owner_id = get_post_meta($purchased_parcel->ID, 'owner', true);
			$purchase_price = get_post_meta($purchased_parcel->ID, 'purchase_price', true);
			if($owner_id) {
				?>
				owner_id = <?php echo $owner_id?>;
				if(!owners_parcel_map_data.hasOwnProperty(owner_id)) {
					owners_parcel_map_data[owner_id] = JSON.parse(JSON.stringify(initial_parcel_map_data));
				}

				feature = {
					type: 'Feature',
					properties: {
						owner_id: <?php echo $owner_id?>,
						purchase_price: <?php echo $purchase_price?>,
					},
					geometry: {
						type: 'Point',
						coordinates: [<?php echo $parcel_lat ?>, <?php echo $parcel_lng?>]
					}
				};
				
				owners_parcel_map_data[owner_id]['features'].push(feature);
				<?php
			}
		}
		?>

		console.log('owners_parcel_map_data:', owners_parcel_map_data);
		map.on('load', () => {

			var marker_colors = ['#0000FF', '#008000', '#800080', '#FF0000'];

			var marker_color_index = 0;
			Object.keys(owners_parcel_map_data).forEach(owner_id => {
				var parcel_map_data = owners_parcel_map_data[owner_id];

				// console.log(key, obj[key]);
				map.addSource('parcels' + owner_id, {
					type: 'geojson',
					data: parcel_map_data,
					// cluster: true,
					// clusterRadius: 80
				})

				parcel_map_data['features'].forEach(feature => {
					console.log('feature geometry:', feature);
				
					var url = "https://api.mapbox.com/geocoding/v5/mapbox.places/" + feature.geometry.coordinates[0] + "," + feature.geometry.coordinates[1] + ".json?access_token=pk.eyJ1IjoibWV0YXZlcnNlIiwiYSI6ImNsMHNhdDFqNDAxbDIzcHBmZ2RkejZmNXEifQ.Tbn_kA1pvvrWUQgVQ0YJWg";
					fetch(url).then((response) => {
						return response.json();
					})
					.then((data) => {

						var place_name = '';

						var country = '';

						for(var index = 0; index < data.features.length; index ++) {
							var feature_id = data.features[index].id;
							if(feature_id.indexOf('address.') != -1) {
								place_name = data.features[index].place_name;
							}

							if(feature_id.indexOf('postcode.') != -1) {
								
							}

							if(feature_id.indexOf('place.') != -1) {
								
							}

							if(feature_id.indexOf('district.') != -1) {
								
							}

							if(feature_id.indexOf('region.') != -1) {
								
							}

							if(feature_id.indexOf('country.') != -1) {
								country = data.features[index].text;
							}
						}

						var thumb_image = `https://maps.googleapis.com/maps/api/streetview?size=400x400&location=${feature.geometry.coordinates[1]},${feature.geometry.coordinates[0]}&fov=80&heading=70&pitch=0&key=AIzaSyC-Xc14Q7Gg8T8sFzDPPd2Qi_kAzC-mzt8`;
						var show_parcel_price = 'Not Set';
						if(parcel_price != '') {
							show_parcel_price = parcel_price;
						}

						var popuphtml = '';
						popuphtml = `<div class="map-popup-wrap">
							<div class="map-popup-img-wrap" style="background-image:url(${thumb_image})">
							</div>
							<div class="map-popup-content-wrap">
								<div class="map-popup-content-wrap-left">
									<h3 class="map-popup-title">${place_name}</h3>
									<p class="map-popup-country">${country}</p>
									<p class="map-popup-lat-lng" lat="${feature.geometry.coordinates[0]}" lng="${feature.geometry.coordinates[1]}">${feature.geometry.coordinates[0]}, ${feature.geometry.coordinates[1]}</p>
								</div>
								<div class="map-popup-content-wrap-right">
									<span class="map-popup-btn map-popup-btn-make-offer">Make Offer</span>
								</div>
							</div>
						</div>`;

						var cur_map_popup = new mapboxgl.Popup()
						// .setLngLat(coordinates)
						.setHTML(popuphtml)
						// .addTo(map);
						
						new mapboxgl.Marker({ color: marker_colors[feature.properties.owner_id % marker_colors.length]})
						.setLngLat(feature.geometry.coordinates)
						.setPopup(cur_map_popup)
						.addTo(map);
					})
				})
				
				marker_color_index ++;
				
			});
		})

		jQuery(document).on('click', '.map-popup-btn-purchase', function() {			
			var popup_wrap = jQuery(this).parents('.map-popup-wrap');
			buy_parcel_address = jQuery(popup_wrap).find('.map-popup-title').text();
			buy_parcel_lat = jQuery(popup_wrap).find('.map-popup-lat-lng').attr('lat');
			buy_parcel_lng = jQuery(popup_wrap).find('.map-popup-lat-lng').attr('lng');
			buy_parcel_country = jQuery(popup_wrap).find('.map-popup-country').text();

			console.log(buy_parcel_address, buy_parcel_lat, buy_parcel_lng, buy_parcel_country);
			if (!blogin) {
				jQuery('#modal_account').modal('toggle');
			}
			else {
				jQuery('#modal_purchase').modal('toggle');
			}
		})

	</script>
	
<?php wp_footer(); ?>
</body>
</html>
