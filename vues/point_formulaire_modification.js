<?// Script lié à la page de modification de fiche

// Ce fichier ne doit contenir que du code javascript destiné à être inclus dans la page
// $vue contient les données passées par le fichier PHP
// $config les données communes à tout WRI
?>

var map, curseur, gps;

window.addEventListener('load', function() {
	var baseLayers = {
		'maps.refuges.info': L.tileLayer('http://maps.refuges.info/hiking/{z}/{x}/{y}.png', {
				attribution: '&copy; <a href="http://osm.org/copyright">Contributeurs OpenStreetMap</a> & <a href="http://wiki.openstreetmap.org/wiki/Hiking/mri">MRI</a>'
		}),
		'OpenStreetMap': L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
				attribution: '&copy; <a href="http://osm.org/copyright">Contributeurs OpenStreetMap</a>'
		}),
		'Outdoors': L.tileLayer('http://{s}.tile.thunderforest.com/outdoors/{z}/{x}/{y}.png', {
				attribution: '&copy; <a href="http://osm.org/copyright">Contributeurs OpenStreetMap</a> & <a href="http://www.thunderforest.com">Thunderforest</a>'
		}),
		'Bing photo': new L.BingLayer(key.bing), // Idem type:'Aerial'
	};

	map = new L.Map('carte-edit', {
		fullscreenControl: true,
		center: new L.LatLng( <?=$vue->point->latitude?> , <?=$vue->point->longitude?>),
		zoom: 13,
		layers: [
			baseLayers['maps.refuges.info'], // Le fond de carte visible

			new L.GeoJSON.Ajax( // Les points d'intérêt WRI
				'<?=$config['sous_dossier_installation']?>api/bbox', {
					argsGeoJSON: {
						type_points: 'all'
					},
					bbox: true,
					icon: function(feature) {
						return {
							url: '<?=$config['sous_dossier_installation']?>images/icones/' + feature.properties.type.icone + '.png',
							size: 16
						}
					}
				}
			),

			curseur = new L.Marker.Position( // Le pointeur à déplacer
				new L.LatLng(<?=$vue->point->latitude?>, <?=$vue->point->longitude?>), {
					prefixe: 'curseur-',
					draggable: true,
					riseOnHover: true, // The marker will get on top of others when you hover the mouse over it.
					icon: L.icon({
						iconUrl: '<?=$config['sous_dossier_installation']?>images/curseur.png',
						iconSize: [30, 30],
						iconAnchor: [15, 15]
					}),
				}
			)
		],
	});

	map.addControl(new L.Control.Layers(baseLayers)); // Le controle de changement de couche de carte avec la liste des cartes dispo
	gps = new L.Control.Gps();
	map.addControl(gps);
	gps.on ('gpslocated', function (args){
		curseur.setLatLng(args.latlng);
	});
	map.addControl(new L.Control.Scale());
	new L.Control.geocoder().addTo(map);
});

function affiche_et_set( el , affiche, valeur ) {
    document.getElementById(el).style.visibility = affiche ;
    document.getElementById(el).value = valeur ;
    return false;
}