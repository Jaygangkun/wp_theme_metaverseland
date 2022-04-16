<?php
/**
* Template Name: Buy Land Map Page
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
			<h1 class="home-header-title">Buy Land Now Map</h1>
			<h3 class="home-header-sub-title">Click the map to zoom in and find individual properties to purchase</h3>
		</div>
		<div class="home-header-right">
			<a class="home-header-link" href="/">Home</a>
			<a class="home-header-link" href="/for-sale-by-owner-map/">For Sale By Owner Map</a>
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
	<div class="modal" tabindex="-1" id="modal_purchase">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Purchase</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="mb-3 text-center">
						<button type="button" class="btn btn-primary" id="btn_purchase">Purchase</button>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="modal" tabindex="-1" id="modal_account" form="login">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="modal_title_login">Login</h5>
					<h5 class="modal-title" id="modal_title_signup">Create Account</h5>
					<h5 class="modal-title" id="modal_title_forget_password">Forget Password</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="account-form-content" id="account_form_content_login">
						<div class="mb-3">
							<label for="email" class="form-label">Email</label>
							<input type="email" class="form-control" id="email" placeholder="">
						</div>
						<div class="mb-3">
							<label for="password" class="form-label">Password</label>
							<input type="password" class="form-control" id="password" placeholder="">
						</div>
						<?php wp_nonce_field( 'ajax-login-nonce', 'security' ); ?>
						<div class="mb-3">
							<div class="text-center">
								<span type="button" class="btn btn-link" id="account_form_link_forget_password">Forget Password</span>
							</div>
							<div class="text-center">
								<span type="button" class="btn btn-link" id="account_form_link_signup">Sign up</span>
							</div>
						</div>
						<div class="mb-3 text-center">
							<button type="button" class="btn btn-primary" id="account_form_btn_login">Login</button>
						</div>
					</div>

					<div class="account-form-content" id="account_form_content_signup">
						<div class="mb-3">
							<label for="username" class="form-label">Username</label>
							<input type="text" class="form-control" id="username" placeholder="">
						</div>
						<div class="mb-3">
							<label for="email" class="form-label">Email</label>
							<input type="email" class="form-control" id="email" placeholder="">
						</div>
						<div class="mb-3">
							<label for="exampleFormControlTextarea1" class="form-label">Password</label>
							<input type="password" class="form-control" id="password" placeholder="">
						</div>
						<div class="mb-3">
							<div class="text-center">
								<span type="button" class="btn btn-link" id="account_form_link_login">Login</span>
							</div>
						</div>
						<div class="mb-3 text-center">
							<button type="button" class="btn btn-primary" id="account_form_btn_signup">Create Account</button>
						</div>
					</div>

					<div class="account-form-content" id="account_form_content_forget_password">
						<div class="mb-3">
							<label for="email" class="form-label">Email</label>
							<input type="email" class="form-control" id="email" placeholder="">
						</div>
						<div class="mb-3">
							<div class="text-center">
								<span type="button" class="btn btn-link" id="account_form_link_login">Return to Login</span>
							</div>
						</div>
						<div class="mb-3 text-center">
							<button type="button" class="btn btn-primary" id="account_form_btn_signup">Reset Password</button>
						</div>
					</div>
				</div>
			</div>
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
		
		var parcel_map_data = [];
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
				parcel_map_data.push({
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

		console.log('parcel_map_data:', parcel_map_data);
		
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

		jQuery(document).on('click', '#account_form_link_forget_password', function() {
			jQuery('#modal_account').attr('form', 'forget_password');
		})

		jQuery(document).on('click', '#account_form_link_signup', function() {
			jQuery('#modal_account').attr('form', 'signup');
		})

		jQuery(document).on('click', '#account_form_link_login', function() {
			jQuery('#modal_account').attr('form', 'login');
		})

		jQuery(document).on('click', '#account_form_content_login #account_form_btn_login', function() {
			if(jQuery('#account_form_content_login #email').val() == '') {
				alert('Please input email!');
				jQuery('#account_form_content_login #email').focus();
				return;
			}

			if(jQuery('#account_form_content_login #password').val() == '') {
				alert('Please input password!');
				jQuery('#account_form_content_login #password').focus();
				return;
			}

			jQuery.ajax({
				url: ajax_url,
				type: 'post',
				data: {
					action: 'metaverseland_login',
					email: jQuery('#account_form_content_login #email').val(),
					password: jQuery('#account_form_content_login #password').val(),
					security: jQuery('#account_form_content_login #security').val()
				},
				dataType: 'json',
				success: function(resp) {
					if(!resp.success) {
						alert(resp.message);
					}
					else {
						blogin = true;
						jQuery('#modal_account').modal('toggle');
						jQuery('#modal_purchase').modal('toggle');
					}
				}
			})
		})

		jQuery(document).on('click', '#account_form_content_signup #account_form_btn_signup', function() {
			if(jQuery('#account_form_content_signup #username').val() == '') {
				alert('Please input username!');
				jQuery('#account_form_content_signup #username').focus();
				return;
			}

			if(jQuery('#account_form_content_signup #email').val() == '') {
				alert('Please input email!');
				jQuery('#account_form_content_signup #email').focus();
				return;
			}

			if(jQuery('#account_form_content_signup #password').val() == '') {
				alert('Please input password!');
				jQuery('#account_form_content_signup #password').focus();
				return;
			}

			jQuery.ajax({
				url: ajax_url,
				type: 'post',
				data: {
					action: 'metaverseland_signup',
					username: jQuery('#account_form_content_signup #username').val(),
					email: jQuery('#account_form_content_signup #email').val(),
					password: jQuery('#account_form_content_signup #password').val()
				},
				dataType: 'json',
				success: function(resp) {
					if(!resp.success) {
						alert(resp.message);
					}
					else {
						alert('Signup successfully!');
						jQuery('#modal_account').attr('form', 'login');
					}
				}
			})
		})

		jQuery(document).on('click', '#account_form_content_forget_password #account_form_btn_signup', function() {
			if(jQuery('#account_form_content_forget_password #email').val() == '') {
				alert('Please input email!');
				jQuery('#account_form_content_forget_password #email').focus();
				return;
			}

			jQuery.ajax({
				url: ajax_url,
				type: 'post',
				data: {
					action: 'metaverseland_forget_password',
					email: jQuery('#account_form_content_forget_password #email').val(),
				},
				dataType: 'json',
				success: function(resp) {
					
				}
			})
		})

		jQuery(document).on('click', '#btn_purchase', function() {
			jQuery.ajax({
				url: ajax_url,
				type: 'post',
				data: {
					action: 'metaverseland_parcel_purchase',
					parcel_price: parcel_price,
					address: buy_parcel_address,
					lat: buy_parcel_lat,
					lng: buy_parcel_lng,
					country: buy_parcel_country
				},
				dataType: 'json',
				success: function(resp) {
					alert("Purchased Successfully!");
				}
			})
		})

		let map;
		let geocoder;
		let infowindow;
		let map_click_marker;
		let purchased_markers;

		function initMap() {
			map = new google.maps.Map(document.getElementById("map"), {
				center: { lat: 40.116386, lng: -101.299591 },
				zoom: 5,
			});

			geocoder = new google.maps.Geocoder();
			infowindow = new google.maps.InfoWindow();
			
			map_click_marker = new google.maps.Marker({
				map,
			});
			map_click_marker.setVisible(false);

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
			
			purchased_markers = [];

			for(var index = 0; index < parcel_map_data.length; index ++) {
				console.log(parcel_map_data[index]);
					
				var coordinates = {
					lat: parcel_map_data[index].coordinates.lat, 
					lng: parcel_map_data[index].coordinates.lng
				};

				const marker = new google.maps.Marker({
					position: coordinates,
					map,
					// title: "Uluru (Ayers Rock)",
				});

				purchased_markers.push(marker);
				
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

			new markerClusterer.MarkerClusterer({map: map, markers: purchased_markers});
				
			map.addListener("click", (e) => {
				var coordinates = {
					lat: e.latLng.lat(),
					lng: e.latLng.lng(),
				};
				
				geocoder.geocode({ location: e.latLng })
				.then((response) => {
					if (response.results[0]) {
						console.log('geocode1:', response);

						let place_name = response.results[0].formatted_address;
						let country = 'country';
						
						let thumb_image = `https://maps.googleapis.com/maps/api/streetview?size=600x300&location=${coordinates.lat},${coordinates.lng}&key=AIzaSyC-Xc14Q7Gg8T8sFzDPPd2Qi_kAzC-mzt8`;

						let show_parcel_price = 'Not Set';
						if(parcel_price != '') {
							show_parcel_price = parcel_price;
						}

						let content = `<div class="map-popup-wrap">
							<div class="map-popup-img-wrap" style="background-image:url(${thumb_image})">
							</div>
							<div class="map-popup-content-wrap">
								<div class="map-popup-content-wrap-left">
									<h3 class="map-popup-title">${place_name}</h3>
									<p class="map-popup-country">${country}</p>
									<p class="map-popup-lat-lng" lat="${coordinates.lat}" lng="${coordinates.lng}">${coordinates.lat}, ${coordinates.lng}</p>
								</div>
								<div class="map-popup-content-wrap-right">
									<p class="map-popup-budget">$${show_parcel_price} USD</p>
                                    <span class="map-popup-btn map-popup-btn-purchase">Purchase</span>
								</div>
							</div>
						</div>`;

						infowindow.setContent(content);
						map_click_marker.setPosition(coordinates);
						infowindow.open(map,map_click_marker);

					} else {
						console.log("No results found");
					}
				}).catch((e) => console.log("Geocoder failed due to: " + e));

			});
			
		}

		window.initMap = initMap;
	</script>
	<script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>
	<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC-Xc14Q7Gg8T8sFzDPPd2Qi_kAzC-mzt8&callback=initMap&libraries=places&v=weekly" defer></script>
<?php wp_footer(); ?>
</body>
</html>
