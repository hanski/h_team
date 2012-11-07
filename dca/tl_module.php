<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Add palettes to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['hanski_team']    = '{title_legend},name,headline,type;{config_legend},hanski_teams,hanski_team_template;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
//$GLOBALS['TL_DCA']['tl_module']['palettes']['hanski_teamlist']    = '{title_legend},name,headline,type;{config_legend},team_archives;{template_legend:hide},team_metaFields,team_template,imgSize;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';


/**
 * Add fields to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['hanski_teams'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['hanski_teams'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'foreignKey'			  => 'tl_hanski_team.title',
//	'options_callback'        => array('tl_module_news', 'getTeams'),
	'eval'                    => array('mandatory'=>true, 'tl_class'=>'w50')
);

/*
$GLOBALS['TL_DCA']['tl_module']['fields']['hanski_team_metaFields'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['team_metaFields'],
	'default'                 => array(),
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'options'                 => array('league', 'season', 'contact'),
	'reference'               => &$GLOBALS['TL_LANG']['MSC'],
	'eval'                    => array('multiple'=>true)
);
*/

$GLOBALS['TL_DCA']['tl_module']['fields']['hanski_team_template'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['team_template'],
	'default'                 => 'news_latest',
	'exclude'                 => true,
	'inputType'               => 'select',
	'options_callback'        => array('tl_module_team', 'getTeamTemplates'),
	'eval'                    => array('tl_class'=>'w50')
);


/**
 * Class tl_module_team
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Leo Feyer 2005-2012
 * @author     Leo Feyer <http://www.contao.org>
 * @package    Controller
 */
class tl_module_team extends Backend
{

	/**
	 * Import the back end user object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}


	/**
	 * Get all teams and return them as array
	 * @return array
	 */
	public function getTeams()
	{
		if (!$this->User->isAdmin && !is_array($this->User->hanski_team))
		{
			return array();
		}

		$arrArchives = array();
		$objArchives = $this->Database->execute("SELECT id, title FROM tl_hanski_team ORDER BY title");

		while ($objArchives->next())
		{
			if ($this->User->isAdmin || $this->User->hasAccess($objArchives->id, 'hanski_team'))
			{
				$arrArchives[$objArchives->id] = $objArchives->title;
			}
		}

		return $arrArchives;
	}


	/**
	 * Return all team templates as array
	 * @param DataContainer
	 * @return array
	 */
	public function getTeamTemplates(DataContainer $dc)
	{
		$intPid = $dc->activeRecord->pid;

		if ($this->Input->get('act') == 'overrideAll')
		{
			$intPid = $this->Input->get('id');
		}

		return $this->getTemplateGroup('hanski_team_', $intPid);
	}
}

?>