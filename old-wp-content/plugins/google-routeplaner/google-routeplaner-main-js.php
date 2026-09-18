<?php
header("Content-Type: application/x-javascript");
?>

var icons = {
	main: new google.maps.MarkerImage(
		'http://maps.google.com/mapfiles/marker.png',
		// (width,height)
		new google.maps.Size( 24, 38 ),
		// The origin point (x,y)
		new google.maps.Point( 0, 0 ),
		// The anchor point (x,y)
		new google.maps.Point( 12, 38 )
	),
	start: new google.maps.MarkerImage(
		'http://maps.gstatic.com/mapfiles/markers2/icon_greenA.png',
		// (width,height)
		new google.maps.Size( 24, 38 ),
		// The origin point (x,y)
		new google.maps.Point( 0, 0 ),
		// The anchor point (x,y)
		new google.maps.Point( 12, 38 )
  ),
  end: new google.maps.MarkerImage(
	   // URL
	   'http://maps.gstatic.com/mapfiles/markers2/icon_greenB.png',
	   // (width,height)
	   new google.maps.Size( 24, 38 ),
	   // The origin point (x,y)
	   new google.maps.Point( 0, 0 ),
	   // The anchor point (x,y)
	   new google.maps.Point( 12, 38 )
	)
};

<?php

echo '
var geocoder' . $_GET['plan_id'] . ';
var directionDisplay' . $_GET['plan_id'] . ';
var directionsService = new google.maps.DirectionsService();
var map' . $_GET['plan_id'] . ';
var marker' . $_GET['plan_id'] . ';


if(typeof map_type_' . $_GET['plan_id'] . ' === "undefined" || \'\' == map_type_' . $_GET['plan_id'] . ') {
	var a_map_type = \'ROADMAP\';
} else {
	var a_map_type = map_type_' . $_GET['plan_id'] . ';
}

if(typeof zoom_control_' . $_GET['plan_id'] . ' === "undefined" || \'NONE\' == zoom_control_' . $_GET['plan_id'] . ' || \'\' == zoom_control_' . $_GET['plan_id'] . ') {
	var a_zoom_control = false;
	var a_zoom_control_style = \'SMALL\';
} else {
	var a_zoom_control = true;
	var a_zoom_control_style = zoom_control_' . $_GET['plan_id'] . ';
}

if(typeof pan_control_' . $_GET['plan_id'] . ' === "undefined" || \'true\' == pan_control_' . $_GET['plan_id'] . ') {
	var a_pan_control = true;
} else {
	var a_pan_control = false;
}

if(typeof type_control_' . $_GET['plan_id'] . ' === "undefined" || \'NONE\' == type_control_' . $_GET['plan_id'] . ' || \'\' == type_control_' . $_GET['plan_id'] . ') {
	var a_type_control = false;
	var a_type_control_style = \'DEFAULT \';
} else {
	var a_type_control = true;
	var a_type_control_style = type_control_' . $_GET['plan_id'] . ';
}


function initialize' . $_GET['plan_id'] . '() {
	directionsDisplay' . $_GET['plan_id'] . ' = new google.maps.DirectionsRenderer({suppressMarkers: true});
	geocoder' . $_GET['plan_id'] . ' = new google.maps.Geocoder();
	var startplace' . $_GET['plan_id'] . ' = new google.maps.LatLng(52.52340510, 13.41139990);
	var myOptions' . $_GET['plan_id'] . ' = {		
			zoom: zoom_' . $_GET['plan_id'] . ',
			disableDefaultUI: true,
			mapTypeId: google.maps.MapTypeId[a_map_type],
			zoomControl: a_zoom_control,
			zoomControlOptions: {
				style: google.maps.ZoomControlStyle[a_zoom_control_style]
			},
			panControl: a_pan_control,
			mapTypeControl: a_type_control,
			mapTypeControlOptions: {style: google.maps.MapTypeControlStyle[a_type_control_style]},' . "\n";


		echo 'center: startplace' . $_GET['plan_id'] . '
	}
		
	map' . $_GET['plan_id'] . ' = new google.maps.Map(document.getElementById("map_canvas' . $_GET['plan_id'] . '"), myOptions' . $_GET['plan_id'] . ');
	directionsDisplay' . $_GET['plan_id'] . '.setMap(map' . $_GET['plan_id'] . ');
	directionsDisplay' . $_GET['plan_id'] . '.setPanel(document.getElementById("map_directions' . $_GET['plan_id'] . '"));

	google.maps.NavigationControlStyle.SMALL;
	codeAddress' . $_GET['plan_id'] . '(destination_' . $_GET['plan_id'] . ');
	
}

function codeAddress' . $_GET['plan_id'] . '(address) {
	if (geocoder' . $_GET['plan_id'] . ') {
		geocoder' . $_GET['plan_id'] . '.geocode( { \'address\': address}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				map' . $_GET['plan_id'] . '.setCenter(results[0].geometry.location);
				
				if(typeof alt_destination_' . $_GET['plan_id'] . ' === "undefined") {
					var infowindow' . $_GET['plan_id'] . ' = new google.maps.InfoWindow({
						content: destination_' . $_GET['plan_id'] . '
					});
					
					marker' . $_GET['plan_id'] . ' = new google.maps.Marker({
						position: results[0].geometry.location,
						icon: icons.main,
						title: destination_' . $_GET['plan_id'] . '
					});
				} else {
					var infowindow' . $_GET['plan_id'] . ' = new google.maps.InfoWindow({
						content: alt_destination_' . $_GET['plan_id'] . '
					});
					
					marker' . $_GET['plan_id'] . ' = new google.maps.Marker({
						position: results[0].geometry.location,
						icon: icons.main,
						title: alt_destination_' . $_GET['plan_id'] . '
					});		
				}
						  
				marker' . $_GET['plan_id'] . '.setMap(map' . $_GET['plan_id'] . '); 

				google.maps.event.addListener(marker' . $_GET['plan_id'] . ', \'click\', function() {
					infowindow' . $_GET['plan_id'] . '.open(map' . $_GET['plan_id'] . ',marker' . $_GET['plan_id'] . ');
				});

			} else {
				alert("' . 'Could not find the target location. Please be more specific!' . '");
			}
		});
	}
}
	
function calcRoute' . $_GET['plan_id'] . '() {
	var start = document.getElementById("fromAddress' . $_GET['plan_id'] . '").value;
	var end = destination_' . $_GET['plan_id'] . ';
	var request = {
		origin:start, 
		destination:end,
		travelMode: google.maps.DirectionsTravelMode.DRIVING
	};
	directionsService.route(request, function(result, status) {
		if (status == google.maps.DirectionsStatus.OK) {
			directionsDisplay' . $_GET['plan_id'] . '.setDirections(result);
			marker' . $_GET['plan_id'] . '.setMap(null);
			var leg = result.routes[0].legs[0];
			makeMarker( leg.start_location, icons.start, start );
			makeMarker( leg.end_location, icons.end, end );
		}
	});
}

function makeMarker( position, icon, title ) {
	new google.maps.Marker({
		position: position,
		map: map' . $_GET['plan_id'] . ',
		icon: icon,
		title: title
	});
}
';

?>

