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
	<script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
	<script src='<?php echo get_template_directory_uri()?>/assets/lib/bootstrap.min.js'></script>
	<script src='<?php echo get_template_directory_uri()?>/assets/lib/jquery-3.6.0.min.js'></script>

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
			<input type="text" placeholder="Search" id="map_search_input">
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
					owners_parcel_map_data[owner_id] = [];
				}
				
				owners_parcel_map_data[owner_id].push({
					owner_id: <?php echo $owner_id?>,
					purchase_price: <?php echo $purchase_price?>,
					coordinates: {
						lat: <?php echo $parcel_lat ?>,
						lng: <?php echo $parcel_lng?>
					}
				});
				<?php
			}
		}
		?>

		console.log('owners_parcel_map_data:', owners_parcel_map_data);

		let map;
		let geocoder;
		let infowindow;
		let purchased_markers;
		var marker_colors = ['#0000FF', '#008000', '#800080', '#FF0000'];

		function initMap() {
			map = new google.maps.Map(document.getElementById("map"), {
				center: { lat: 40.116386, lng: -101.299591 },
				zoom: 5,
			});

			geocoder = new google.maps.Geocoder();
			infowindow = new google.maps.InfoWindow();
			
			const input = document.getElementById("map_search_input");
  			const searchBox = new google.maps.places.SearchBox(input);

			map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
			// Bias the SearchBox results towards current map's viewport.
			map.addListener("bounds_changed", () => {
				searchBox.setBounds(map.getBounds());
			});
			
			let markers = [];

			searchBox.addListener("places_changed", () => {
				const places = searchBox.getPlaces();

				if (places.length == 0) {
					return;
				}

				// Clear out the old markers.
				markers.forEach((marker) => {
					marker.setMap(null);
				});
				markers = [];

				// For each place, get the icon, name and location.
				const bounds = new google.maps.LatLngBounds();

				places.forEach((place) => {
					if (!place.geometry || !place.geometry.location) {
						console.log("Returned place contains no geometry");
						return;
					}

					const icon = {
						url: place.icon,
						size: new google.maps.Size(71, 71),
						origin: new google.maps.Point(0, 0),
						anchor: new google.maps.Point(17, 34),
						scaledSize: new google.maps.Size(25, 25),
					};

					// Create a marker for each place.
					markers.push(
						new google.maps.Marker({
							map,
							icon,
							title: place.name,
							position: place.geometry.location,
						})
					);
					if (place.geometry.viewport) {
						// Only geocodes have viewport.
						bounds.union(place.geometry.viewport);
					} else {
						bounds.extend(place.geometry.location);
					}
				});
				map.fitBounds(bounds);
			});

			var owner_ids = Object.keys(owners_parcel_map_data);
			for(var oindex = 0; oindex < owner_ids.length; oindex ++) {
					
				var parcel_map_data_owner = owners_parcel_map_data[owner_ids[oindex]];
				for(var index = 0; index < parcel_map_data_owner.length; index ++) {
					var coordinates = {
						lat: parcel_map_data_owner[index].coordinates.lat, 
						lng: parcel_map_data_owner[index].coordinates.lng
					};

					

					const marker = new google.maps.Marker({
						position: coordinates,
						map,
						// title: "Uluru (Ayers Rock)",
					});
					
					google.maps.event.addListener(marker,'click',function() {
						// console.log("click:", marker.getPosition());
						// map.setCenter(marker.getPosition());
						var marker_coordinates = {
							lat: marker.getPosition().lat(),
							lng: marker.getPosition().lng(),
						};
						
						geocoder.geocode({ location: marker.getPosition() })
						.then((response) => {
							if (response.results[0]) {
								console.log('geocode:', response);

								let place_name = response.results[0].formatted_address;
								let country = 'country';
								
								let thumb_image = `https://maps.googleapis.com/maps/api/streetview?size=600x300&location=${marker_coordinates.lat},${marker_coordinates.lng}&key=AIzaSyC-Xc14Q7Gg8T8sFzDPPd2Qi_kAzC-mzt8`;

								let content = `<div class="map-popup-wrap">
									<div class="map-popup-img-wrap" style="background-image:url(${thumb_image})">
									</div>
									<div class="map-popup-content-wrap">
										<div class="map-popup-content-wrap-left">
											<h3 class="map-popup-title">${place_name}</h3>
											<p class="map-popup-country">${country}</p>
											<p class="map-popup-lat-lng" lat="${marker_coordinates.lat}" lng="${marker_coordinates.lng}">${marker_coordinates.lat}, ${marker_coordinates.lng}</p>
										</div>
										<div class="map-popup-content-wrap-right">
											<span class="map-popup-btn map-popup-btn-make-offer">Make Offer</span>
										</div>
									</div>
								</div>`;

								infowindow.setContent(content);
								infowindow.open(map,this);

							} else {
								console.log("No results found");
							}
						}).catch((e) => console.log("Geocoder failed due to: " + e));

					});
				}
			}
		}

	</script>

	<script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>
	<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC-Xc14Q7Gg8T8sFzDPPd2Qi_kAzC-mzt8&callback=initMap&libraries=places&v=weekly" defer></script>	
<?php wp_footer(); ?>
</body>
</html>
