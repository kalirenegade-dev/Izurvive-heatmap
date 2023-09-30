<!DOCTYPE html>
<html>

<head>
    <title>Kalirenegade's Custom Killfeed Heatmap</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
	<script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>

    <link href="assets/css/dayz.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
</head>
<style>
  /* Define CSS styles for the custom div icons */
        .custom-div-icon {
			color: black;
             text-shadow: -.8px -.8px 0 white, .8px -.8px 0 white, -.8px .8px 0 white, .8px .8px 0 white;
			 padding: 5px 10px;
			border-radius: 5px;
			font-weight: bold;
            font-size: 14px;
            }
/* Customize the appearance of the Leaflet.Draw controls */
.leaflet-draw-toolbar {
  background-color: #fff; /* Set a background color for the toolbar */
  border: .8px solid #ccc;
  border-radius: 4px;
  padding: 5px;
}

/* Style the buttons in the toolbar */
.leaflet-draw-toolbar a {
  color: #333; /* Button text color */
  background-color: #f5f5f5; /* Button background color */
  border: .8px solid #ccc; /* Button border */
  padding: 5px 10px;
  margin: 5px;
  text-decoration: none;
  border-radius: 3px;
}

/* Style the buttons on hover */
.leaflet-draw-toolbar a:hover {
  background-color: #ddd;
}

/* Style the drawn rectangle */
.leaflet-draw-shape {
  stroke: #3388ff; /* Outline color for drawn shapes */
  fill-opacity: 0.2; /* Fill opacity for drawn shapes */
  fill: #3388ff; /* Fill color for drawn shapes */
}

/* Style the handles for resizing the rectangle */
.leaflet-draw-shape:hover .leaflet-draw-handle {
  background-color: #3388ff; /* Handle background color on hover */
}

/* Style the marker icon for rectangle center */
.leaflet-draw-marker-icon {
  background-color: #3388ff; /* Marker background color */
  border: .8px solid #fff; /* Marker border */
}

/* Style the delete button for drawn features */
.leaflet-draw-edit-remove {
  background-color: #ff6666; /* Delete button background color */
  color: #fff; /* Delete button text color */
  border: .8px solid #cc0000; /* Delete button border color */
  border-radius: 3px;
  padding: 3px 6px;
  text-align: center;
  cursor: pointer;
}

/* Style the delete button on hover */
.leaflet-draw-edit-remove:hover {
  background-color: #cc0000;
}

/* Style the save button for drawn features */
.leaflet-draw-edit-save {
  background-color: #66cc66; /* Save button background color */
  color: #fff; /* Save button text color */
  border: .8px solid #339933; /* Save button border color */
  border-radius: 3px;
  padding: 3px 6px;
  text-align: center;
  cursor: pointer;
}

/* Style the save button on hover */
.leaflet-draw-edit-save:hover {
  background-color: #339933;
}
</style>
<style>
    /* Style the autocomplete container */
    .ui-autocomplete {
        max-height: 200px;
        overflow-y: auto;
        position: absolute;
        background-color: #fff;
        border: .8px solid #ccc;
    }
	.ui-helper-hidden-accessible {
		display: none !important;
	}
    /* Style individual autocomplete items */
    .ui-autocomplete li {
        list-style: none;
        padding: 5px;
        cursor: pointer;
    }

    /* Highlight the selected item */
    .ui-autocomplete li.ui-state-focus {
        background-color: #007bff;
        color: #fff;
    }

    .custom-tooltip {
        min-width: 100px; /* Adjust the minimum width as needed */
        min-height: 10px; /* Adjust the minimum height as needed */
        padding: .8px; /* Adjust padding as needed */
        background-color: white;
        border: .8px solid #ccc;
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
            <!-- GamerTag and Tooltip Toggle Section -->
            <div class="search-section">
                <label for="GamerTag">GamerTag:</label>
                <input type="text" id="GamerTag" name="q" placeholder="Enter GamerTag" />
                <label for="tooltip-checkbox">Show Tooltip</label>
                <input type="checkbox" id="tooltip-checkbox" checked>
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

            <!-- Record Limit Section -->
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
			<div id="selected-coordinates"></div>

        </center>
    </div>
    <script type="module">
        $(document).ready(function () {
            $("#GamerTag").autocomplete({
                source: 'Users.php',
                paramName: 'q'
				
            });
        });

		// Add an event listener to the "Show Tooltips" checkbox
		const tooltipCheckbox = document.getElementById('tooltip-checkbox');
		tooltipCheckbox.addEventListener('change', function () {
		  const isChecked = tooltipCheckbox.checked;

		  // Check if the GeoJSON layer (geojsonLayer) is defined
		  if (geojsonLayer) {
			geojsonLayer.eachLayer(function (layer) {
			  if (isChecked) {
				layer.openTooltip();
			  } else {
				layer.closeTooltip();
			  }
			});
		  }
		});



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
AddremoveControl();
AddDrawControl();
function AddremoveControl() {
    // Create a feature group to handle the drawn items
    var drawnItems = new L.FeatureGroup();
    mymap.addLayer(drawnItems);

    // Initialize the drawing control
    var drawControl = new L.Control.Draw({
        draw: {
            rectangle: {
                shapeOptions: {
                    color: 'blue', // Set the color for the rectangle
                    fillOpacity: 0.2, // Set the fill opacity
                },
            },
            marker: false,
            circle: false,
            circlemarker: false,
            polyline: false,
            polygon: false,
        },
        edit: {
            featureGroup: drawnItems, // Add a feature group to handle the drawn items
            remove: true, // Enable the remove functionality
        },
    });

    // Create a custom "Remove" button and add it to the drawing control
    let removeButton = L.DomUtil.create('button', 'leaflet-draw-edit-remove leaflet-control');
    removeButton.innerHTML = 'Remove';
    removeButton.title = 'Remove drawn items';
    removeButton.style.margin = '5px';

    // Add a click event listener to the "Remove" button
    removeButton.addEventListener('click', function () {
        if (drawnItems.getLayers().length > 0) {
            drawnItems.clearLayers(); // Clear the drawn items from the feature group
            drawnRectangle = null; // Clear the drawn rectangle reference
        } else {
            alert('No drawn items to remove.');
        }
    });

    // Add the "Remove" button to the drawing control
    drawControl.onAdd = function (map) {
        var container = L.DomUtil.create('div', 'leaflet-draw leaflet-control');
        container.appendChild(removeButton);
        return container;
    };

    // Add the drawing control to the map, but initially, keep it disabled
    drawControl.addTo(mymap);
}
// Define a variable to store the drawn rectangle
var drawnRectangle = null;

function AddDrawControl() {
    // Create a feature group to handle the drawn items
    var drawnItems = new L.FeatureGroup();
    mymap.addLayer(drawnItems);

    // Initialize the drawing control
    var drawControl = new L.Control.Draw({
        draw: {
            rectangle: {
                shapeOptions: {
                    color: 'blue', // Set the color for the rectangle
                    fillOpacity: 0.2, // Set the fill opacity
                },
            },
            marker: false,
            circle: false,
            circlemarker: false,
            polyline: false,
            polygon: false,
        },
        
    });

    // Add the drawing control to the map
    drawControl.addTo(mymap);

    // Event handler for when a shape is drawn and added to the feature group
    mymap.on('draw:created', function (e) {
        var layer = e.layer;

        // Remove the previously drawn rectangle, if any
        if (drawnRectangle) {
            mymap.removeLayer(drawnRectangle);
        }

        // Add the newly drawn rectangle to the map
        mymap.addLayer(layer);

        // Display the Izurvive point (x, y) coordinates of the selected rectangle
        displaySelectedRectangleCoordinates(layer);

        // Store the newly drawn rectangle
        drawnRectangle = layer;
    });
}

function GetRecSelectionCoords(){
	 if (drawnRectangle) {
        // Get the bounds of the drawn rectangle
        const bounds = drawnRectangle.getBounds();
        const topLeft = bounds.getNorthWest();
        const bottomRight = bounds.getSouthEast();

        // Check if both coordinates are defined
        if (topLeft && bottomRight) {
            // Convert Leaflet coordinates to Izurvive coordinates if needed
            const izurviveTopLeft = convertToIzurviveCoordinates(topLeft);
            const izurviveBottomRight = convertToIzurviveCoordinates(bottomRight);

            // Display the coordinates as an alert or any other desired action
                       // Create a JavaScript object with the coordinates
            const coordinates = {
                topLeft: {
                    lat: izurviveTopLeft.lat,
                    lng: izurviveTopLeft.lng,
                },
                bottomRight: {
                    lat: izurviveBottomRight.lat,
                    lng: izurviveBottomRight.lng,
                },
            };

            // Convert the JavaScript object to JSON format
            const jsonCoordinates = JSON.stringify(coordinates);
			return jsonCoordinates;
        } else {
            // Handle the case where coordinates are undefined
            return null;
        }
    } else {
        // Handle the case where no rectangle is drawn
        return null;
    }
};

// Create a function to display the Izurvive point (x, y) coordinates of the selected rectangle
function displaySelectedRectangleCoordinates(layer) {
    const bounds = layer.getBounds();
    const topLeft = bounds.getNorthWest();
    const bottomRight = bounds.getSouthEast();

    // Check if both coordinates are defined
    if (topLeft && bottomRight) {
        // Convert Leaflet coordinates to Izurvive coordinates
        const izurviveTopLeft = convertToIzurviveCoordinates(topLeft);
        const izurviveBottomRight = convertToIzurviveCoordinates(bottomRight);
		
		const izurviveCoordinateTopLeft = new Coordinate(topLeft.lat, topLeft.lng);
		const izurviveCoordinateBottomRight = new Coordinate(bottomRight.lat, bottomRight.lng);
		const transformation = IzurviveTransformation.livonia();
		const dayzPointTopLeft = transformation.izurviveCoordinateToDayzPoint(izurviveCoordinateTopLeft)	;
		const dayzPointBottomRight = transformation.izurviveCoordinateToDayzPoint(izurviveCoordinateBottomRight);	

        // Format the coordinates as "x:10155.98, y:10900.00"
        //const formattedCoordinates = "x:" + dayzPointTopLeft.x.toFixed(2) + ", y:" + dayzPointTopLeft.y.toFixed(2)  + "<br>" +
        //                           "x:" + dayzPointBottomRight.x.toFixed(2) + ", y:" + dayzPointBottomRight.y.toFixed(2);
		// Create a JSON object with the specified structure
		const formattedCoordinates = {
			topLeft: {
				x: dayzPointTopLeft.x.toFixed(2),
				y: dayzPointTopLeft.y.toFixed(2),
			},
			bottomRight: {
				x: dayzPointBottomRight.x.toFixed(2),
				y: dayzPointBottomRight.y.toFixed(2),
			},
		};

        // Display the coordinates in the selected-coordinates div
       //document.getElementById('selected-coordinates').innerHTML = formattedCoordinates;
    } else {
        // Handle the case where coordinates are undefined
        document.getElementById('selected-coordinates').innerHTML = "Coordinates are undefined";
    }
}



AddCityNamesRUFirst();
function AddCityNames(){
	
	// Load the JSON data from the file
	fetch('citynames.json')
		.then(function(response) {
			if (!response.ok) {
				throw new Error('Network response was not ok');
			}
			return response.json();
		})
		.then(function(jsonData) {
			// Convert the JSON data into a GeoJSON FeatureCollection
			var geojsonData = {
				type: "FeatureCollection",
				features: jsonData.map(function(item) {
					return {
						type: "Feature",
						geometry: {
							type: "Point",
							coordinates: [item.lng, item.lat] // Swap lng and lat if needed
						},
						properties: {
							nameEN: item.nameEN
						}
					};
				})
			};

			// Create custom div icons for names
			L.geoJSON(geojsonData, {
				onEachFeature: function (feature, layer) {
					// Check if the "nameEN" property exists
					if (feature.properties && feature.properties.nameEN) {
						var customIcon = L.divIcon({
							className: 'custom-div-icon',
							html: feature.properties.nameEN
						});

						// Create a marker with the custom icon
						L.marker([feature.geometry.coordinates[1], feature.geometry.coordinates[0]], {
							icon: customIcon
						}).addTo(mymap);
					}
				}
			});
		})
		.catch(function(error) {
			console.error('There was a problem with the fetch operation:', error);
		});
}

function AddCityNamesRUFirst() {
    // Load the JSON data from the file
    fetch('citynames.json')
        .then(function(response) {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(function(jsonData) {
            // Convert the JSON data into a GeoJSON FeatureCollection
            var geojsonData = {
                type: "FeatureCollection",
                features: jsonData.map(function(item) {
                    var cityName = item.nameRU || item.nameEN; // Use nameRU if available, otherwise use nameEN

                    return {
                        type: "Feature",
                        geometry: {
                            type: "Point",
                            coordinates: [item.lng, item.lat] // Swap lng and lat if needed
                        },
                        properties: {
                            name: cityName
                        }
                    };
                })
            };

            // Create custom div icons for names
            L.geoJSON(geojsonData, {
                onEachFeature: function (feature, layer) {
                    // Check if the "name" property exists
                    if (feature.properties && feature.properties.name) {
                        var customIcon = L.divIcon({
                            className: 'custom-div-icon',
                            html: feature.properties.name
                        });

                        // Create a marker with the custom icon
                        L.marker([feature.geometry.coordinates[1], feature.geometry.coordinates[0]], {
                            icon: customIcon
                        }).addTo(mymap);
                    }
                }
            });
        })
        .catch(function(error) {
            console.error('There was a problem with the fetch operation:', error);
        });
}

// Function to convert Leaflet coordinates to Izurvive coordinates
function convertToIzurviveCoordinates(leafletCoordinate) {
    const transformation = IzurviveTransformation.livonia();
    const izurviveCoordinate = transformation.dayzPointToIzurviveCoordinate(new Point(leafletCoordinate.lat, leafletCoordinate.lng));
    return izurviveCoordinate;
}
	function DisplayJSON_PVP(geojsonData){
		geojsonLayer.clearLayers();
		// Function to convert DayZ Point coordinates to Izurvive coordinates
		function convertToIzurviveCoordinates(dayzPoint) {
			return IzurviveTransformation.livonia().dayzPointToIzurviveCoordinate(dayzPoint);
		}

		// Iterate through GeoJSON features and convert coordinates
		geojsonData.features.forEach(function (feature) {
			const dayzPointCoordinates = feature.geometry.coordinates;
			const izurviveCoordinate = convertToIzurviveCoordinates({ x: dayzPointCoordinates[0], y: dayzPointCoordinates[1] });
			
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
					'Weapon: ' + feature.properties.Weapon + '<br>' +
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
				const tooltipCheckbox = document.getElementById('tooltip-checkbox');
				const isChecked = tooltipCheckbox.checked;
				if (isChecked) {
					marker.bindTooltip(feature.properties.name, {
						permanent: true,
						direction: 'top',
						offset: [2	, -3],
						className: 'custom-tooltip',
					});
					
				}else{
					marker.bindTooltip(feature.properties.name, {
						permanent: false,
						direction: 'top',
						offset: [2	, -3],
						className: 'custom-tooltip',
					});
				}
				

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
				const tooltipCheckbox = document.getElementById('tooltip-checkbox');
				const isChecked = tooltipCheckbox.checked;
				if (isChecked) {
					marker.bindTooltip(feature.properties.name, {
						permanent: true,
						direction: 'top',
						offset: [2	, -3],
						className: 'custom-tooltip',
					});
				}else{
					marker.bindTooltip(feature.properties.name, {
						permanent: false,
						direction: 'top',
						offset: [2	, -3],
						className: 'custom-tooltip',
					});
				}


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
			  // Update tooltips visibility after adding new data
            updateTooltipsVisibility();
        })
        .catch((error) => {
            console.error('Error fetching data:', error);
        });
}



// Add an event listener to the "Search" button
document.getElementById('search-button').addEventListener('click', function () {
const JsonCoords = GetRecSelectionCoords();
console.log(JsonCoords);

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

	</body>

	</html>
