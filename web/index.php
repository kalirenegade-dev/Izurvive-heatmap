	<!DOCTYPE html>
	<html>

	<br>
	<head>
		<title>Leaflet Custom Tile Map</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
		<script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
		<link href="assets/css/dayz.css" rel="stylesheet" />
	</head>
	<style>
	.custom-tooltip {
		#max-width: 100px; /* Adjust the maximum width as needed */
		min-height: 10px; /* Adjust the minimum height as needed */
		padding: 1px; /* Adjust padding as needed */
		background-color: white;
		border: 1px solid #ccc;
		border-radius: 5px;
		font-size: 10px; /* Adjust font size as needed */
	}

	/* Red marker for PVP */
	.marker-icon-red {
		width: 13px;
		height: 13px;
		background-color: red;
		border-radius: 50%;
	}

	/* Green marker for PVE */
	.marker-icon-green {
		width: 13px;
		height: 13px;
		background-color: green;
		border-radius: 50%;
	}


	</style>
<body>
    <center>
        <div id="search-bar">
            <!-- GamerTag Section -->
            <div class="search-section">
                <label for="GamerTag">GamerTag:</label>
                <input type="text" id="GamerTag" placeholder="Enter GamerTag" />
            </div>

            <!-- Map Type Section -->
            <div class="search-section">
                <label>Map Type:</label>
                <input type="radio" id="PVP" name="MapType" value="PVP" checked>
                <label for="PVP">PVP</label>

                <input type="radio" id="PVE" name="MapType" value="PVE">
                <label for="PVE">PVE</label>
            </div>

            <!-- Kill Type Section -->
            <div class="search-section">
                <label>Kill Type:</label>
                <input type="radio" id="Kill" name="KillType" value="kill" checked>
                <label for="Kill">Kill</label>

                <input type="radio" id="Death" name="KillType" value="death">
                <label for="Death">Death</label>
				
				
            </div>

            <!-- Search Button -->
            <div class="search-section">
                 <label for="RecordLimit">Record Limit:</label>
				<input type="number" id="RecordLimit" placeholder="Enter Record Limit" min="1" value="100" />

<button id="search-button">Search</button>
            </div>
        </div>
    </center>

    <div id="main">
        <center>
            <div id="map"></div>
            <div id="coords">x:0, y:0</div>
        </center>
    </div>
		<script type="module">
			let marker = [];
			import {
				Point,
				Coordinate,
				Size,
				Bounds
			} from './model.js';
			import {
				LinearTransform,
				SphericalMercator
			} from './transform.js';
			import {
				IzurviveTransformation,
				IzurviveMap
			} from './izurvive.js';

			<?php
   $defaultLocation = "8000;8000";
   $defaultMap = "sat";
   $defaultZoom = "1";

   $locationParam = $defaultLocation;
   $mapParam = $defaultMap;
   $zoomParam = $defaultZoom;

   $sanitizedLocation = preg_replace("/[^0-9;]+/", "", $locationParam);

   if ($mapParam == "sat") {
       $sanitizedmap = "Livonia-Sat";
   } elseif ($mapParam == "top") {
       $sanitizedmap = "Livonia-Top";
   } else {
       $sanitizedmap = "Livonia-Sat";
   }

   $sanitizedZoom = preg_replace("/[^1-8]+/", "", $zoomParam);

   $LocationParts = explode(";", $sanitizedLocation);
   $LocationPartsLength = count($LocationParts);

   if ($LocationPartsLength != 2) {
       $pointLocation_lat = "0";
       $pointLocation_lng = "0";
   } else {
       $pointLocation_lat = $LocationParts[0];
       $pointLocation_lng = $LocationParts[1];
   }

   $zoom = $sanitizedZoom;

   $map = $sanitizedmap;
   ?>

			const transformation = IzurviveTransformation.livonia();
			const izurviveCoordinate = transformation.dayzPointToIzurviveCoordinate(new Point(<?php echo $pointLocation_lat; ?>,<?php echo $pointLocation_lng; ?>));
		  
			let mymap = L.map('map', ).setView([izurviveCoordinate.lat, izurviveCoordinate.lng], <?php echo $zoom; ?>);
		  
	const satelliteLayer = L.tileLayer('https://maps.izurvive.com/maps/Livonia-Sat/1.19.0/tiles/{z}/{x}/{y}.webp', {
			noWrap: true,
			bounds: [
				[-90, -180],
				[90, 180]
			],tms: false,
			maxZoom: 8,
			minZoom: 1,
			attribution: 'satellite',
			errorTileUrl: './assets/img/missing256.png'
		})

		const topographicalLayer = L.tileLayer('https://maps.izurvive.com/maps/{map}/{version}/tiles/{z}/{x}/{y}.{fileType}', {
			map:'Livonia-Top', 
			version:'1.19.0',
			fileType:'webp',
			noWrap: true,
			bounds: [
				[-90, -180],
				[90, 180]
			],
			tileSize: 256,
			tms: false, //true
			minZoom: 1,
			maxZoom: 8, //7
			continuousWorld: false,
			attribution: 'topographical',
			id: 'CH-Top',
			errorTileUrl: './assets/img/missing256.png'
		})
	if ("<?php echo $map; ?>" == "Livonia-Sat"){
		satelliteLayer.addTo(mymap);  
	}else{
		topographicalLayer.addTo(mymap); 
	}
		
		 

		//set up map selector
		const mapSelector = L.Control.extend({
			options: {
				position: 'bottomleft'
			},
			onAdd: function (map) {
				const container = L.DomUtil.create('div', 'leaflet-control-image');
				container.onclick = function() {
					   if (mymap.hasLayer(satelliteLayer)) {
					mymap.removeLayer(satelliteLayer);
					mymap.addLayer(topographicalLayer);
				  } else {
					mymap.removeLayer(topographicalLayer);
					mymap.addLayer(satelliteLayer);
				  }
				};
				return container;
			  }
			});

			mymap.addControl(new mapSelector());
			//Add a div element to the HTML document to display the mouse coordinates
			const coordsDiv  = document.querySelector("#main #coords")
			const MarkerCoords  = document.querySelector("#main #MarkerCoords")

			// Add a mousemove event to the map to update the coordinates div
			mymap.on('mousemove', function(e) {
				const xlat = e.latlng.lat.toFixed(4) 
				const ylng = e.latlng.lng.toFixed(4)
				const izurviveCoordinate = new Coordinate(xlat, ylng)
				const transformation = IzurviveTransformation.livonia()
				const dayzPoint = transformation.izurviveCoordinateToDayzPoint(izurviveCoordinate)	
				coordsDiv.innerHTML = "x:" + dayzPoint.x.toFixed(2) + ", y:" +dayzPoint.y.toFixed(2) 
			});


			


		

	function DisplayJSON_PVP(geojsonData){
		geojsonLayer.clearLayers();
		// Function to convert DayZ Point coordinates to Izurvive coordinates
		function convertToIzurviveCoordinates(dayzPoint) {
			return IzurviveTransformation.livonia().dayzPointToIzurviveCoordinate(dayzPoint);
		}

		// Iterate through GeoJSON features and convert coordinates
		geojsonData.features.forEach(function (feature) {
			var dayzPointCoordinates = feature.geometry.coordinates;
			var izurviveCoordinate = convertToIzurviveCoordinates({ x: dayzPointCoordinates[0], y: dayzPointCoordinates[1] });
			
			// Update the GeoJSON feature's coordinates with Izurvive coordinates
			feature.geometry.coordinates = [izurviveCoordinate.lng, izurviveCoordinate.lat];
			});
			 geojsonLayer = L.geoJSON(geojsonData, {
			// Define options such as marker styles or popups
			pointToLayer: function (feature, latlng) {
				var marker = L.marker(latlng, {
					icon: L.divIcon({
						className: 'custom-icon',
						html: '<div class="marker-icon-red"></div>',
					}),
				});

				// Bind a popup with additional information
				  var popupContent = '<strong>' + feature.properties.name + '</strong><br>' +
					'Killer: ' + feature.properties.Killer + '<br>' +
					'Victom: ' + feature.properties.Victim + '<br>' +
					'WeaponType: ' + feature.properties.WeaponType + '<br>' +
					'Distance: ' + feature.properties.Distance + '<br>' +
					'HitLocation: ' + feature.properties.HitLocation + '<br>' +
					'HitDamage: ' + feature.properties.HitDamage + '<br>' +
					'POS_X: ' + feature.properties.POS_X + '<br>' +
					'POS_Y: ' + feature.properties.POS_Y + '<br>' +
					'TimeAlive: ' + feature.properties.TimeAlive + '<br>' +
					'Timestamp: ' + feature.properties.Timestamp + '<br>' +
					'InGameTime: ' + feature.properties.InGameTime;
					

				marker.bindPopup(popupContent);

				marker.bindTooltip(feature.properties.name, {
					permanent: true,
					direction: 'top',
					offset: [2	, -3],
					className: 'custom-tooltip',
				});

				return marker;
			},
		});


			// Add the GeoJSON layer to the map
			geojsonLayer.addTo(mymap);
	}
	
	let geojsonLayer = L.geoJSON().addTo(mymap);
	
	function DisplayJSON_PVE(geojsonData){
		geojsonLayer.clearLayers();
		
		// Function to convert DayZ Point coordinates to Izurvive coordinates
		function convertToIzurviveCoordinates(dayzPoint) {
			return IzurviveTransformation.livonia().dayzPointToIzurviveCoordinate(dayzPoint);
		}

		// Iterate through GeoJSON features and convert coordinates
		geojsonData.features.forEach(function (feature) {
			var dayzPointCoordinates = feature.geometry.coordinates;
			var izurviveCoordinate = convertToIzurviveCoordinates({ x: dayzPointCoordinates[0], y: dayzPointCoordinates[1] });
			
			// Update the GeoJSON feature's coordinates with Izurvive coordinates
			feature.geometry.coordinates = [izurviveCoordinate.lng, izurviveCoordinate.lat];
			});
			 geojsonLayer = L.geoJSON(geojsonData, {
			// Define options such as marker styles or popups
			pointToLayer: function (feature, latlng) {
				var marker = L.marker(latlng, {
					icon: L.divIcon({
						className: 'custom-icon',
						html: '<div class="marker-icon-green"></div>',
					}),
				});

				// Bind a popup with additional information
				  var popupContent = '<strong>' + feature.properties.name + '</strong><br>' +
					'Victom: ' + feature.properties.Victim + '<br>' +
					'POS_X: ' + feature.properties.POS_X + '<br>' +
					'POS_Y: ' + feature.properties.POS_Y + '<br>' +
					'TimeAlive: ' + feature.properties.TimeAlive + '<br>' +
					'Timestamp: ' + feature.properties.Timestamp + '<br>' +
					'InGameTime: ' + feature.properties.InGameTime;
					
				marker.bindPopup(popupContent);

				marker.bindTooltip(feature.properties.name, {
					permanent: true,
					direction: 'top',
					offset: [2	, -3],
					className: 'custom-tooltip',
				});

				return marker;
			},
		});


			// Add the GeoJSON layer to the map
			geojsonLayer.addTo(mymap);
			

	}

		    

function fetchData(url, type) {
    fetch(url)
        .then((response) => response.json())
        .then((data) => {
            if (type === 'PVP') {
				console.log(data);
                DisplayJSON_PVP(data);
            } else if (type === 'PVE') {
				console.log(data);
                DisplayJSON_PVE(data);
            }
        })
        .catch((error) => {
            console.error('Error fetching data:', error);
        });
}



// Add an event listener to the "Search" button
document.getElementById('search-button').addEventListener('click', function () {
    const GamerTag = document.getElementById("GamerTag").value;
    const MapTypeRadio = document.querySelector('input[name="MapType"]:checked');
    const KillTypeRadio = document.querySelector('input[name="KillType"]:checked');
    const RecordLimit = document.getElementById("RecordLimit").value; // Get the Record Limit value

    if (MapTypeRadio && KillTypeRadio && GamerTag !== '') {
        const MapTypeValue = MapTypeRadio.value;
        const KillTypeValue = KillTypeRadio.value;

        if (KillTypeValue === 'kill') {
            // Include the Record Limit in the API request URL
            fetchData('https://katesserver.com/dayz/killfeed.php?type=' + MapTypeValue + '&Killer=' + GamerTag + '&limit=' + RecordLimit, MapTypeValue);
        } else if (KillTypeValue === 'death') {
            // Include the Record Limit in the API request URL
            fetchData('https://katesserver.com/dayz/killfeed.php?type=' + MapTypeValue + '&Victim=' + GamerTag + '&limit=' + RecordLimit, MapTypeValue);
        }
    }
});


		

	</script>


		
		
	</body>

	</html>



		</script>
	</body>

	</html>
