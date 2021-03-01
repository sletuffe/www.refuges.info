<?php
/* Cet utilitaire produit toutes les icônes SVG utilisées par les cartes
/images/icones/debut-du-nom-<code>_<code>_<code>.svg ou .png
Chaque code désigne une forme élémentaire :

Icônes
------
Les 2 première lettres comptent : i<une minuscule>[n'importe quelles minuscules]
	ie[au] (point d'eau)
	il[ac]
	is[ommet]
	it[riangle] (passage délicat)
	Défaut : cabane, forme de bâtiment

Couleurs (des formes de bâtiment)
--------------------------------
c suivi d'une couleur CSS
	cblack (batiment blanc, toit et murs noirs)
	cgreen (batiment vert, gîte)
	cred (batiment rouge, refuge gardé)
	c... (autres couleurs CSS)
	Défaut : cabane ocre et toit rouge

Attributs
---------
Seule la première lettre compte
	e[au] (petite goute d'eau)
	f'feu' (cheminée et fumée)
	k[ey] (clé)
	p[recaire] (manque un mur)
	x(une grande croix noire)

Caractère au centre
-------------------
	a123 le caractère ascii 123 (en décimal)

*/

//-------------------------------------
// Tableau de correspondance temporaire
// A retirer quand tous les noms des icones auront été codés
$alias = [
	'ancien-point-d-eau' => 'ieau_x',
	'batiment-en-montagne' => 'cblack_a63',
	'batiment-inutilisable' => 'cblack_x',
	'cabane-avec-eau' => 'eau',
	'cabane-avec-moyen-de-chauffage' => 'feu',
	'cabane-avec-moyen-de-chauffage-et-eau-a-proximite' => 'eau_feu', // Peut être résumé par 'e_f'
	'cabane-cle' => 'key',
	'cabane-eau-a-proximite' => 'eau',
	'cabane-manque-un-mur' => 'precaire',
	'cabane-non-gardee' => 'icabane', // Le vrai code est '' mais on peut mettre icabane, ça fait plus joli
	'cabane-sans-places-dormir' => 'a48',
	'gite-d-etape' => 'cblue', // Car la forme de bâtiment est par défaut
	'inutilisable' => 'cblack_x',
	'lac' => 'ilac',
	'passage-delicat' => 'itriangle_a33', // Peut être résumé par 'it_a33'
	'point-d-eau' => 'ieau',
	'refuge-garde' => 'cred',
	'sommet' => 'isommet', // Peut être résumé par 'is'
];
if (isset ($alias[$_GET['nom']]))
	$_GET['nom'] = $alias[$_GET['nom']];


//------------------------------------------------------
// On va rechercher les arguments dans le nom du fichier
preg_match_all ('/([a-z])([a-z0-9]*)_/', $_GET['nom'].'_', $matches);
foreach ($matches[1] AS $k=>$v)
	$args[$v] = $matches[2][$k];

// Arguments spéciaux
$taille = @$args['t'] ?: 24;
$icone = @$args['i'][0];
$couleur_toit = @$args['c'] ?: 'red';
$couleur_mur = @$args['c'] ?: '#e08020';
$couleur = @$args['c'] ?: '#ffeedd';
if (isset($args['p'])) // Pas de murs extérieurs aux bâtiments précaires
	$couleur_mur = '#ffeedd';
if ($couleur == 'black') // Murs et toit noir, murs blancs
	$couleur = 'white';


//------------------------------------------------
// On commence à capturer la sortie du template
ob_start();

// Template SVG
?><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" height="<?=$taille?>" width="<?=$taille?>">
<?php

if ($icone == 'e') { /* Eau */
?>	<ellipse cx="12" cy="15" rx="6.75" ry="6.75" stroke-width="0.75" stroke="#005e5e" fill="cyan" />
	<ellipse cx="12" cy="15" rx="4.5" ry="4.5" stroke-width="0" fill="#005e5e" />
	<ellipse cx="13" cy="14" rx="4.5" ry="4.5" stroke-width="0" fill="cyan" />
	<path d="M6.3 11.4 l5.7 -10,5.7 10" stroke-width="0.75" stroke="#005e5e" fill="cyan" />

<?php } elseif ($icone == 'l') { /* Lac */
?>	<ellipse cx="7.5" cy="9" rx="6" ry="4.5" stroke="#204A87" fill="#204A87" />
	<ellipse cx="13.5" cy="13.5" rx="9" ry="4.5" stroke="#204A87" fill="#204A87" />
	<ellipse cx="11" cy="12" rx="9" ry="4.5" stroke="#204A87" fill="#204A87" />
	
<?php } elseif ($icone == 's') { /* Sommet */
?>	<path d="M0 26 l8 -17,3 5,5 -12,8 24" stroke="white" fill="#583E24" />

<?php } elseif ($icone == 't') { /* Triangle (passage délicat) */
?>	<path d="M1.75 23 l10.3 -18,10.2 18 Z" stroke-width="2" stroke="red" fill="white" />

<?php } else { /* Bâtiments */
?>	<path d="M3 10.7 l0 13,18 0,0 -13" stroke-width="0.5" stroke="<?=$couleur_mur?>" fill="<?=$couleur?>" />
	<path d="M1.5 12.3 l10.5 -10.5,10.5 10.5" stroke-width="3" stroke-linecap="round" stroke="<?=$couleur_toit?>" fill="<?=$couleur?>" />
	<?php if (!isset ($args['couleur']) && /* Porte */
		!isset ($args['a']) &&
		!isset ($args['c']) &&
		!isset ($args['e']) &&
		!isset ($args['p'])) {
	?>	<rect x="9" y="13.5" width="6" height="10" stroke="none" fill="#e08020" />

	<?php }
	}

// Attributs
if (isset ($args['a'])) { /* Ascii */
?>	<text x="<?=$args['a']==33?9:7.5?>" y="21" font-size="18px" >&#<?=$args['a']?>;</text>

<?php } if (isset($args['e'])) { /* Eau */
?>	<ellipse cx="16.5" cy="19.2" rx="3.4" ry="3.4" stroke-width="0.75" stroke="#005e5e" fill="cyan" />
	<path d="M13.65 17.36 l2.85 -5,2.85 5" stroke-width="0.75" stroke="#005e5e" fill="cyan" />

<?php } if (isset($args['f'])) { /* Feu */
?>	<rect x="3" y="2" width="3" height="7" fill="black" />
	<ellipse cx="8" cy="2.5" rx="1.5" ry="2" stroke="#444444" fill="#888888" />
	<ellipse cx="12.5" cy="3.5" rx="2" ry="3" stroke="#444444" fill="#888888" />
	<ellipse cx="19.5" cy="5.5" rx="4" ry="5" stroke="#444444" fill="#888888" />

<?php } if (isset($args['k'])) { /* Clé */
?>	<ellipse cx="19.8" cy="7.8" rx="3" ry="3" stroke-width="2.4" stroke="black" fill="none" />
	<path d="M18 9 l-13.5 13.5,-2.25 -2.25,4.2 -0.45,0.15 -4,3 2.7" stroke-width="2.1" stroke="black" fill="none" />

<?php } if (isset($args['p'])) { /* Précaire (manque un mur) */
?>	<path d="M6 12 l12 0" stroke-width="1.5" stroke-linecap="round" stroke="red" />
	<path d="M4.5 17.25 l15 0" stroke-width="1.5" stroke-linecap="round" stroke="red" />
	<path d="M4.5 22.5 l15 0" stroke-width="1.5" stroke-linecap="round" stroke="red" />


<?php } if (isset($args['x'])) { /* Croix (barré) */
?>	<path d="M1 1 l22 22" stroke-width="2" stroke-linecap="round" stroke="black" fill="none" />
	<path d="M1 23 l22 -22" stroke-width="2" stroke-linecap="round" stroke="black" fill="none" />

<?php } ?></svg>
<?php


//--------------------------------------------
// On fini de capturer la sortie du template
$svg =  ob_get_contents();
ob_end_clean();


//---------------------
// Sortie en format SVG
if ($_GET['ext'] == 'svg') {
	header ('Content-type: image/svg+xml');
	header ('Cache-Control: max-age=86000');
	header ('Access-Control-Allow-Origin: *');

	echo $svg;
}


//---------------------
// Sortie en format PNG
if ($_GET['ext'] == 'png') {
	header ('Content-type: image/png');
	header ('Cache-Control: max-age=86000');
	header ('Access-Control-Allow-Origin: *');

	// Fabrique une image PNG à partir du script SVG
	$image = new Imagick();
	$image->setBackgroundColor(new ImagickPixel('transparent'));
	$image->readImageBlob($svg);
	$image->setImageFormat('png32');
	echo $image;
	$image->clear();
	$image->destroy();
}