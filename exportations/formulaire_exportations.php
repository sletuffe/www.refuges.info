<?php
/**********************************************************************************************
Préparer un lien d'exportation direct de nos données vers plein de formats pour être 
ré-utiliser.
Le traitement proprement dit est dans exportations.php 
**********************************************************************************************/

require_once ("../modeles/config.php");
require_once ("fonctions_mode_emploi.php");
require_once ("fonctions_bdd.php");
require_once ("fonctions_exportations.php");
require_once ("fonctions_autoconnexion.php");

$modele = new stdclass;
$formats="";
foreach ($config['formats_exportation'] as $formats_possibles)
  if ($formats_possibles['interne']==false)
    $formats.=$formats_possibles['description_courte'].", ";

$modele->titre="Téléchargement et exportation de la base refuges.info aux formats $formats";
$modele->java_lib[]="/vues/formulaire_exportations.js";

include("../vues/_entete.html");

print("<h3>$modele->titre</h3>");

// comme on se "post" les informations à soit même, on vérifie dans quel cas on est
if (!isset($_POST['validation'])) // rien de valider, formulaire vierge
{
  print("<h4>Veuillez préciser les options pour l'exportation</h4>");
  //FIXME : fait à l'arrache
  $query_dossier = "Select id_point_type,nom_type,importance from point_type where nom_type!='censuré' order by importance desc";
  $res=$pdo->query($query_dossier);
  
    
  //Ecriture du formulaire HTML pour les cases à cocher
  $form_points="<form id='choix' method='post' action='".$_SERVER['SCRIPT_NAME']."'>";
	
  // Choix des types de points 
  $form_points.="<fieldset><legend>Choix des points de la base a exporter</legend>";
  $form_points.="<ul>";
  while ($row = $res->fetch()) {
    $form_points.="
			<li style='display: inline;
						display: inline-block;
						width: 13em;
						white-space: nowrap;'>
				<label>
					<input
						type='checkbox' 
						name='id_point_type[]'
						value='$row->id_point_type' ";
						
  if (isset($_GET['id_point_type']))
  {
    if (in_array($row->id_point_type, $_GET['id_point_type']) )
      $form_points.=" checked='checked' ";
  }
  elseif ($row->importance>62)
    $form_points.=" checked='checked' ";
  
  $form_points.="\n
					/>
					$row->nom_type &nbsp;
				</label>
			</li>";
  }
  $form_points.="</ul>";

  print($form_points);
  print("
		<button type='button' onclick=\"setCheckboxes('choix', true,'id_point_type[]'); return false;\">
			Tout cocher
		</button>
			&nbsp;/&nbsp;
		<button type='button' onclick=\"setCheckboxes('choix', false,'id_point_type[]'); return false;\">
			Tout décocher
		</button>
	</fieldset> <!-- fin de l'encadré type de points -->
  ");

  // choix des différents massifs
  print("<fieldset><legend>Choix des massifs de la base a exporter :</legend>");

  // Creation d'une case à cocher pour chaque type massif
  $conditions = new stdClass;
  $conditions->ids_polygone_type=$config['id_massif'];
  $massifs=infos_polygones($conditions);
  if ($massifs->erreur)
    die($massifs->message);

  print("\n<ul>");
  foreach ($massifs as $massif)
  {
    print("  
			<li style='display: inline;
						display: inline-block;
						width: 13em;
						white-space: nowrap;'>
				<label>
					<input
						type='checkbox'
						name='id_massif[]'
						value='$massif->id_polygone' ");
  if ( ! isset($_GET['id_massif']) )
    print(" checked='checked' "); // checked par defo
  else
    if ($_GET['id_massif'] == $massif->id_polygone) 
      print(" checked='checked' "); //checked seulement si bon massif
    print("/>$massif->nom_polygone &nbsp;
				</label>
			</li>");
  }
  print("\n</ul>");

  print("
		<button type='button' onclick=\"setCheckboxes('choix', true,'id_massif[]'); return false;\">
			Tout cocher
		</button>
			&nbsp;/&nbsp;
		<button type='button' onclick=\"setCheckboxes('choix', false,'id_massif[]'); return false;\">
			Tout décocher
		</button>
	</fieldset> <!-- fin du choix des massifs -->
	");

  // zone BBOX a exporter seulement si present en GET (on viendrait du navigator)
  if(isset($_GET["sud"]) )
  {
    print("
			<fieldset><legend>Limites (en degrés décimaux WGS84)</legend>
				<table>
					<tr>
						<td></td>
						<td style='text-align: center;'><input name='nord' id='bbox_latmax' type='text' size='4' value='" .$_GET["nord"]. "' /></td>
						<td></td>
					</tr>
					<tr style='height: 4em;'>
						<td><input name='ouest' id='bbox_lngmin' type='text' size='4' value='" .$_GET["ouest"]. "' /></td>
						<td style='background-color: #A6CEE7;'>Zone à exporter</td>
						<td><input name='est' id='bbox_lngmax' type='text' size='4' value='" .$_GET["est"]. "' /></td>
					</tr>
					<tr>
						<td></td>
						<td style='text-align: center;'><input name='sud' id='bbox_latmin' type='text' size='4' value='" .$_GET["sud"]. "' /></td>
						<td></td>
					</tr>
				</table>
			</fieldset>
			");
  }
  // choix du format d'exportation

print("
	<fieldset><legend>Choix du format d'exportation :</legend>
		<select name=\"format\">\n");
			foreach ($config['formats_exportation'] as $clef=>$formats_possibles)
			  if ($formats_possibles['interne']==false)
			    print("\t\t\t<option value=\"$clef\">$formats_possibles[description_courte]</option>\n");
			print("\t\t</select> 
	</fieldset>");


	// bouton pour obtenir le lien
	print("
		<fieldset><legend>Obtenir le lien d'accès direct</legend>
			<input type='submit' name='validation' value='Obtenir le lien' />
		</fieldset>
		</form>");

//-------------------------
// Fin du formulaire
}
/**
Si on est en mode "action" donc on présente un lien, on aurait pu zapper cette
étape en postant directement le fichier souhaité, mais je ne le veux pas
car le but étant de permettre le partage à des partenaires 
(et donc avec des robots de synchronisation)
je veux leur indiquer qu'il existe un lien en GET pour le faire
sly 02/11/2008
Autre avantage : si on parle d'exportation de la base, on a peut être à faire à un informaticien qui ne veut 
peut-être pas l'avoir sur son disque mais le télécharger directement depuis un serveur distant, là, il n'a pas forcément
de navigateur, donc il prend le lien, fait :
wget $lien
et zou
**/
else // formulaire validé, affichage du lien et d'un blabla
{
	print("
	<h4>Licence</h4>
		<p>
			<a href=\"".lien_mode_emploi("licence")."\">Voir les détails sur la licence de refuges.info</a>	
		</p>
	");
	print($config['formats_exportation'][$_POST['format']]['description']);

	print("
	<h4>Exportation demandée</h4>
		<p>Voici le lien permanent d'accès direct aux données :<br>");

	if ($_POST['id_point_type']=="" OR $_POST['id_massif']=="")
		print("<strong>Vous demandez vraiment quelque chose de vide ??</strong>");
	else
	{
		$liste_id_point_type = '';
		foreach ($_POST['id_point_type'] as $id_point_type)
			$liste_id_point_type.="$id_point_type,";

		$liste_id_point_type=trim($liste_id_point_type,",");

		foreach ($_POST['id_massif'] as $id_massif)
			$liste_id_massif.="$id_massif,";

		$liste_id_massif=trim($liste_id_massif,",");

		// limiter à une bbox (si demandé depuis les cartes)
		if(isset($_POST['sud']) ) {
			$bbox = "&amp;bbox=" . $_POST['ouest'] . ",". $_POST['sud'] ;
			$bbox .= "," . $_POST['est'] . "," . $_POST['nord'] ;
		} else {
			$bbox = "";
		}
			
		$options_lien="?limite=sans&amp;format=".$_POST['format']."&amp;liste_id_point_type=$liste_id_point_type&amp;liste_id_massif=$liste_id_massif" . $bbox;
		$lien="http://".$config['nom_hote']."/exportations/exportations.php$options_lien";

		print("
				<a href='$lien'>
					$lien
				</a>
			</p>");
	} // fin du else 'demande pas vide"

} // fin du else affichage lien

include("../vues/_pied.html");
?>
