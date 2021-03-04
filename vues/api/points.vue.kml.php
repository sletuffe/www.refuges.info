<?php
if ($req->format=="kml") // FIXME sly 12/2019 : quelqu'un s'en sert encore à distance du kml ? pourquoi cette gestion de cache et de CORS ?
{
  $secondes_de_cache = 60;
  $ts = gmdate("D, d M Y H:i:s", time() + $secondes_de_cache) . " GMT";
  header("Content-disposition: filename=points-refuges-info.$req->format");
  header("Content-Type: application/vnd.google-earth.$req->format; UTF-8"); // rajout du charset
  header("Content-Transfer-Encoding: binary");
  header("Pragma: cache");
  header("Expires: $ts");
  if($config_wri['autoriser_CORS']===TRUE) header("Access-Control-Allow-Origin: *");
    header("Cache-Control: max-age=$secondes_de_cache");
}

// FIXME sly 12/2019 : c'est vraiment dommage d'avoir de belles vues ailleurs et se taper une horreur pareille, mais le format kmz a besoin de la même chose, mais en compressé, donc il lui faut capturer la variable $kml
$kml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n";
$kml .= "<kml xmlns=\"http://earth.google.com/kml/2.1\">\r\n";
$kml .= "<Document>\r\n";
$kml .= "	<name>points.kml</name>\r\n";
$kml .= "	<description>".$config_wri['copyright_API']."</description>\r\n\r\n";
	
$kml .= "<!-- Liste des STYLES -->\r\n\r\n";
$icones_possibles=liste_icones_possibles();
foreach ($icones_possibles as $nom_icone)
{
	$lien_icone = "https://".$config_wri['nom_hote'].$config_wri['url_chemin_icones'].$nom_icone.'.png';

	$tx = $ty = 24; // La plupart des icones

	$kml .= "	<Style id='icone_$nom_icone'>\r\n";
	$kml .= "		<IconStyle>\r\n";
	$kml .= "		<hotSpot x='0.5' y='0.5' xunits='fraction' yunits='fraction' />\r\n";
	$kml .= "		<scale>1</scale>\r\n";
	$kml .= "		<Icon>\r\n";
	$kml .= "			<href>$lien_icone</href>\r\n";
	$kml .= "			<w>$tx</w>\r\n";
	$kml .= "			<h>$ty</h>\r\n";
	$kml .= "		</Icon>\r\n";
	$kml .= "		</IconStyle>\r\n";
	$kml .= "	</Style>\r\n";
}
$kml .= "<!-- Fin des Styles ! -->\r\n\r\n";

$kml .= "<!-- Liste des POINTS -->\r\n";
$kml .= "	<Folder><name>Points</name>\r\n";
$kml .= "	<open>0</open>\r\n\r\n";
	
foreach ($points AS $point) {
	$kml .= "		<Placemark id='$point->id'>\r\n";
	$kml .= "			<name>$point->nom</name>\r\n";
	$kml .= "			<description>\r\n";
	$kml .= "				<![CDATA[\r\n";
	$kml .= "					<img src=\"https://".$config_wri['nom_hote']."/images/icones/".$point->type['icone'].".png\" />\r\n";
	$kml .= "					(<em>".$point->type['valeur']."</em>) <br />\r\n";
	$kml .= "					<center><a href='$point->lien'>Détails</a></center>\r\n";
	$kml .= "				]]>\r\n";
	$kml .= "			</description>\r\n";
	$kml .= "			<LookAt>\r\n";
	$kml .= "				<longitude>".($point->coord['long']+0.0002)."</longitude>\r\n";
	$kml .= "				<latitude>".($point->coord['lat']+0.0008)."</latitude>\r\n";
	$kml .= "				<range>".($point->coord['alt']+5500.0)."</range>\r\n";
	$kml .= "				<tilt>40</tilt>\r\n";
	$kml .= "				<heading>50</heading>\r\n";
	$kml .= "			</LookAt>\r\n";
	$kml .= "    		<styleUrl>#icone_".replace_url($point->type['valeur'])."</styleUrl>\n";
	$kml .= "			<Point>\r\n";
	$kml .= "				<coordinates>".$point->coord['long'].",".$point->coord['lat'].",0</coordinates>\r\n";
	$kml .= "			</Point>\r\n";
	$kml .= "			<ExtendedData>\r\n";
	$kml .= "				<Data name=\"url\">\r\n";
	$kml .= "				<value>$point->lien</value>\r\n";
	$kml .= "				</Data>\r\n";
	$kml .= "			</ExtendedData>\r\n";
	$kml .= "		</Placemark>\r\n\r\n";
}
$kml .= "	</Folder>\r\n\r\n";

$kml .= "</Document>\r\n";
$kml .= "</kml>\r\n";
if ($req->format=="kml")
  print($kml);
