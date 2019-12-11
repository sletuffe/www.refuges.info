<?php

$secondes_de_cache = 60;
$ts = gmdate("D, d M Y H:i:s", time() + $secondes_de_cache) . " GMT";
header("Content-disposition: filename=points.json");
header("Content-Type: application/json; UTF-8"); // rajout du charset
header("Content-Transfer-Encoding: binary");
header("Pragma: cache");
header("Expires: $ts");
if($config_wri['autoriser_CORS']===TRUE) header("Access-Control-Allow-Origin: *");
header("Cache-Control: max-age=$secondes_de_cache");


echo '{
	"type": "FeatureCollection",
	"generator": "Refuges.info API",
	"copyright": "'.$config_wri['copyright_API'].'",
	"timestamp": "';
echo date(DATE_ATOM);
echo '",
	"features": [';

$j = 0;
foreach ($points as $point) {
	$j++;
	echo "\r\n\t".'{'."\r\n\t\t".'"type": "Feature",
            "id": '.$point->id.',
            "properties":'."\r\n\t\t\t";
	echo json_encode($point);
	echo ",\r\n\t\t".'"geometry": {
			"type": "Point",
			"coordinates": ['."\r\n";
	echo "\t\t\t\t".$point->coord['long'].",\r\n";
	echo "\t\t\t\t".$point->coord['lat']."\r\n";
	echo "\t\t\t]
		}
	}";
	if ($j != $nombre_points) echo ",";
}

echo "\r\n\t]\r\n}";


