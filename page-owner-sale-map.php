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
	<link type="text/css" rel="stylesheet" href="<?php echo get_template_directory_uri()?>/assets/lib/bootstrap.min.css">
	<link type="text/css" rel="stylesheet" href="<?php echo get_template_directory_uri()?>/custom.css">
	<script>
		var ajax_url = '<?php echo admin_url('admin-ajax.php')?>';
	</script>
</head>
<body <?php body_class(); ?>>
	<div class="home-header page-header">
		<div class="home-header-left">
			<h1 class="home-header-title">Metaverse Land Today</h1>
			<h3 class="home-header-sub-title">The Home of Afforadable Metaverse Land Deals</h3>
		</div>
		<div class="home-header-right">
			<a class="home-header-link" href="/buy-land-map-page/">Buy Land Now Map</a>
			<a class="home-header-link" href="/for-sale-by-owner-map/">For Sale By Owner Map</a>
			<a class="home-header-link">Signup/Login</a>
			<?php echo json_encode(get_option('metaverseland_setting_parcel_price'))?>
		</div>
	</div>
	<div class="page-content">
		<div class="map-wrap">
			<div class="map-search-box">
				<input type="text" placeholder="Search">
			</div>
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
		mapboxgl.accessToken = 'pk.eyJ1IjoibWV0YXZlcnNlIiwiYSI6ImNsMHNhdDFqNDAxbDIzcHBmZ2RkejZmNXEifQ.Tbn_kA1pvvrWUQgVQ0YJWg';
		const map = new mapboxgl.Map({
			container: 'map', // container ID
			style: 'mapbox://styles/mapbox/streets-v11', // style URL
			center: [-101.299591, 40.116386], // starting position [lng, lat]
			zoom: 4 // starting zoom
		});

		map.addControl(new mapboxgl.NavigationControl());
 
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

				map.addLayer({
					'id': 'parcel_points' + owner_id,
					'type': 'circle',
					'source': 'parcels' + owner_id,
					'paint': {
						'circle-radius': 6,
						'circle-color': marker_colors[marker_color_index % marker_colors.length]
					},
					'filter': ['==', '$type', 'Point']
				});

				map.on('click', 'parcel_points' + owner_id, (e) => {
					console.log(`parcel point click `);
					// e.originalEvent.stopPropagation();
					// e.originalEvent.preventDefault();

					parcel_clicked = true;
					const coordinates = [e.lngLat.lng, e.lngLat.lat];
					
					showPopup(coordinates, true, e.features[0].properties);
					
				});

				marker_color_index ++;
				
			});
			

			function showPopup(coordinates, purchased, properties) {
				var url = "https://api.mapbox.com/geocoding/v5/mapbox.places/" + coordinates[0] + "," + coordinates[1] + ".json?access_token=pk.eyJ1IjoibWV0YXZlcnNlIiwiYSI6ImNsMHNhdDFqNDAxbDIzcHBmZ2RkejZmNXEifQ.Tbn_kA1pvvrWUQgVQ0YJWg";
				fetch(url).then((response) => {
					return response.json();
				})
				.then((data) => {
					console.log('url data:', data);

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

					var thumb_image = `https://maps.googleapis.com/maps/api/streetview?size=400x400&location=${coordinates[1]},${coordinates[0]}&fov=80&heading=70&pitch=0&key=AIzaSyC-Xc14Q7Gg8T8sFzDPPd2Qi_kAzC-mzt8`;
					var show_parcel_price = 'Not Set';
					if(parcel_price != '') {
						show_parcel_price = parcel_price;
					}

					var popuphtml = '';
					if(!purchased) {
						popuphtml = `<div class="map-popup-wrap">
							<div class="map-popup-img-wrap" style="background-image:url(${thumb_image})">
							</div>
							<div class="map-popup-content-wrap">
								<div class="map-popup-content-wrap-left">
									<h3 class="map-popup-title">${place_name}</h3>
									<p class="map-popup-country">${country}</p>
									<p class="map-popup-lat-lng" lat="${coordinates[0]}" lng="${coordinates[1]}">${coordinates[0]}, ${coordinates[1]}</p>
								</div>
								<div class="map-popup-content-wrap-right">
									<p class="map-popup-budget">$${show_parcel_price} USD</p>
									<span class="map-popup-btn map-popup-btn-purchase">Purchase</span>
								</div>
							</div>
						</div>`;
					}
					else {
						popuphtml = `<div class="map-popup-wrap">
							<div class="map-popup-img-wrap" style="background-image:url(${thumb_image})">
							</div>
							<div class="map-popup-content-wrap">
								<div class="map-popup-content-wrap-left">
									<h3 class="map-popup-title">${place_name}</h3>
									<p class="map-popup-country">${country}</p>
									<p class="map-popup-lat-lng" lat="${coordinates[0]}" lng="${coordinates[1]}">${coordinates[0]}, ${coordinates[1]}</p>
								</div>
								<div class="map-popup-content-wrap-right">
									<span class="map-popup-btn map-popup-btn-make-offer">Make Offer</span>
								</div>
							</div>
						</div>`;
					}

					if(cur_map_popup == null) {
						cur_map_popup = new mapboxgl.Popup()
						.setLngLat(coordinates)
						.setHTML(popuphtml)
						.addTo(map);
					}
					else {
						cur_map_popup.remove();
						cur_map_popup = null;
					}
					
					parcel_clicked = false;
					
				})
				
				
			}

			// map.on('click', (e) => {
			// 	console.log('map click');

			// 	if(parcel_clicked) {
			// 		return;
			// 	}

			// 	const coordinates = [e.lngLat.lng, e.lngLat.lat];
				
			// 	showPopup(coordinates, false,  null);
			// });
			// objects for caching and keeping track of HTML marker objects (for performance)
			const markers = {};
			let markersOnScreen = {};
			
			function updateParcelMarkers() {
				const newMarkers = {};
				const features = map.querySourceFeatures('parcels');
				
				// for every cluster on the screen, create an HTML marker for it (if we didn't yet),
				// and add it to the map if it's not there already
				for (const feature of features) {
					const coords = feature.geometry.coordinates;
					const props = feature.properties;
					if (!props.cluster) continue;
					const id = props.cluster_id;
					
					let marker = markers[id];
					if (!marker) {
						// const el = createDonutChart(props);
						const el = createParcelMarker(props);
						marker = markers[id] = new mapboxgl.Marker({
							element: el
						}).setLngLat(coords);
					}
					newMarkers[id] = marker;
					
					if (!markersOnScreen[id]) marker.addTo(map);
				}
				// for every marker we've added previously, remove those that are no longer visible
				for (const id in markersOnScreen) {
					if (!newMarkers[id]) markersOnScreen[id].remove();
				}
				markersOnScreen = newMarkers;
			}
				
			// after the GeoJSON data is loaded, update markers on the screen on every frame
			map.on('render', () => {
				// if (!map.isSourceLoaded('parcels')) return;
				// updateMarkers();
				updateParcelMarkers();
			});
		});

		function createParcelMarker(props) {
			console.log('createParcelMarker', props);
			let html = `<div class="parcel-cluster-marker">${props['point_count']}</div>`;
			
			const el = document.createElement('div');
			el.innerHTML = html;
			return el.firstChild;
		}

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
	</script>
	
<?php wp_footer(); ?>
</body>
</html>
