<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Back end modules
 */
array_insert($GLOBALS['BE_MOD']['content'], 6, array
(
	'hanski_team' => array
	(
		'tables'  => array('tl_hanski_team', 'tl_hanski_player'),
		'icon'    => 'system/modules/hanski_team/assets/icon.png'
	)
));

/**
 * Front end modules
 */
array_insert($GLOBALS['FE_MOD']['miscellaneous'], 0, array
(
	'hanski_team' => 'ModuleHanskiTeam'
));

?>