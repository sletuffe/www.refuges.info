<?php
$numero = $controlleur->url_decoupee[3] ?: 0;
$where_list = [
	'accepte' => ' WHERE ext_error IS NULL AND mode != \'Rejeté\'',
	'rejete' => ' WHERE ext_error IS NOT NULL OR mode = \'Rejeté\'',
	'topic' => ' WHERE topic_id = '.$numero,
	'post' => ' WHERE post_id = '.$numero,
	'point' => ' WHERE point_id = '.$numero,
	'commentaire' => ' WHERE commentaire_id = '.$numero,
];

// Hook ext/RefugesInfo/trace/listener.php liste des colonnes à afficher
$where = $where_list[$controlleur->url_decoupee[2]];
$traces_html = '';
$type_trace = 'historique';
$vars = ['where', 'traces_html', 'type_trace'];
extract($phpbb_dispatcher->trigger_event('wri.list_traces', compact($vars)));

$vue->traces = $traces_html;
