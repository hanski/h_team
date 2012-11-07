<?php

class ModuleHanskiTeam extends Module
{
	/**
	* Template
	* @var string
	*/
	protected $strTemplate = 'mod_hanski_team';

	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### TEAM ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		return parent::generate();
	}	

// TODO: parseTeam     => img, data, cleanRTE
// TODO: parsePlayers  => img, data


	/**
	 * Parse one or more items and return them as array
	 * @param Database_Result
	 * @param boolean
	 * @return array
	 */
	protected function parsePlayers(Database_Result $objPlayers)
	{
		if ($objPlayers->numRows < 1)
		{
			return array();
		}

		global $objPage;
		$this->import('String');

		$arrPlayers = array();
		$imgSize = false;

		// Override the default image size
		if ($this->size != '')
		{
			$imgSize = deserialize($this->size);

			if ($imgSize[0] > 0 || $imgSize[1] > 0)
			{
				$imgSize = $this->size;
			}
		}

		while ($objPlayers->next())
		{
			$objTemplate = new FrontendTemplate($this->hanski_team_template);
			$objTemplate->setData($objPlayers->row());

			$objTemplate->count = ++$count;
			$objTemplate->class = (($objPlayers->cssClass != '') ? ' ' . $objPlayers->cssClass : '') . (($count == 1) ? ' first' : '') . ((($count % 2) == 0) ? ' odd' : ' even');
            $objTemplate->email = encodeEmail($objPlayers->email);
//			$objTemplate->archive = objPlayers->archive;

			$objTemplate->addImage = false;

			// Add an image
			if ($objPlayers->addImage && is_file(TL_ROOT . '/' . $objPlayers->singleSRC))
			{
				if ($imgSize)
				{
					$objPlayers->imgSize = $imgSize;
				}

				$this->addImageToTemplate($objTemplate, $objPlayers->row());
			}

			$arrPlayers[] = $objTemplate->parse();
		}

		return $arrPlayers;
	}
    
	/**
	 * Parse one or more items and return them as array
	 * @param Database_Result
	 * @param boolean
	 * @return array
	 */
	protected function parseTeam(Database_Result $objTeam, Database_Result $objPlayers)
	{
		if ($objTeam->numRows < 1)
		{
			return array();
		}

		global $objPage;
		$this->import('String');

		$arrTeam = array();
		$imgSize = false;

		// Override the default image size
		if ($this->size != '')
		{
			$imgSize = deserialize($this->size);

			if ($imgSize[0] > 0 || $imgSize[1] > 0)
			{
				$imgSize = $this->size;
			}
		}

		// keine Schleife, da nur ein Element
        $objTemplate = new FrontendTemplate($this->hanski_team_template);
		$objTemplate->setData($objTeam->row());

		$objTemplate->class = (($objTeam->cssClass != '') ? ' ' . $objTeam->cssClass : '');
		//$objTemplate->title = $objTeam->title;
		//$objTemplate->name = $objTeam->name;
		//$objTemplate->archive = $objTeam->archive;

		/* Clean the RTE output
		if ($objTeam->teaser != '')
		{
			if ($objPage->outputFormat == 'xhtml')
			{
				$objTeam->teaser = $this->String->toXhtml($objTeam->teaser);
			}
			else
			{
				$objTeam->teaser = $this->String->toHtml5($objTeam->teaser);
			}

			$objTemplate->teaser = $this->String->encodeEmail($objTeam->teaser);
		}

		// Encode e-mail addresses
		else
		{
			// Clean the RTE output
			if ($objPage->outputFormat == 'xhtml')
			{
				$objTeam->text = $this->String->toXhtml($objTeam->text);
			}
			else
			{
				$objTeam->text = $this->String->toHtml5($objTeam->text);
			}

			$objTemplate->text = $this->String->encodeEmail($objTeam->text);
		}
        */

		$objTemplate->addImage = false;

		// Add an image
		if ($objTeam->addImage && is_file(TL_ROOT . '/' . $objTeam->singleSRC))
		{
			if ($imgSize)
			{
				$objTeam->imgSize = $imgSize;
			}

			$this->addImageToTemplate($objTemplate, $objTeam->row());
		}

		$arrTeam[] = $objTemplate->parse();
        
        $arrTeam['players'] = $this->parsePlayers($objPlayers);

		return $arrTeam;
	}
	
	/**
	 * Generate the module
	 */
	protected function compile()
	{
		$time = time();

/* Schleife über alle Einträge
			$objTemplate->addImage = false;

			// Add image
			if ($event['addImage'] && is_file(TL_ROOT . '/' . $event['singleSRC']))
			{
				if ($imgSize)
				{
					$event['size'] = $imgSize;
				}

				$this->addImageToTemplate($objTemplate, $event);
				$objTemplate->href = $event['href']; // Reset the href (see #3370)
			}
*/
/*		
		$objTeamStmt = $this->Database->prepare(
			"SELECT title as teamname, league as league, season as season, name as contact
			FROM tl_hanski_team WHERE id=? AND published=? AND (start=? OR start<?) AND (stop=? OR stop>?)
			"
		);
*/
		$objTeamStmt = $this->Database->prepare(
			"SELECT * FROM tl_hanski_team WHERE id=? AND published=? AND (start=? OR start<?) AND (stop=? OR stop>?)
			"
		);

		$objPlayersStmt = $this->Database->prepare(
            //"SELECT p.id as playerid, t.id as teamid, p.*, t.* 
			"SELECT p.*
			FROM tl_hanski_player p
			INNER JOIN tl_hanski_team t
			ON t.id = p.pid
			WHERE p.pid=?
			AND t.published=?
			AND (t.start=? OR t.start<?)
			AND (t.stop=? OR t.stop>?)
			AND p.published=?
			AND (p.start=? OR p.start<?)
			AND (p.stop=? OR p.stop>?)
			ORDER BY p.sorting
			"
		);
		$objTeam = $objTeamStmt->limit(1)->execute($this->hanski_teams, 1, '', $time, '', $time, 1, '', $time, '', $time);
		$objPlayers = $objPlayersStmt->execute($this->hanski_teams, 1, '', $time, '', $time, 1, '', $time, '', $time);

		$hanski_team = array();

		// get team data from db
		$teamInfo = $objTeam->fetchAssoc();
		/*
		foreach($teamInfo as $key=>$value) {
			$hanski_team[$key] = $value;
		}
		*/
		$hanski_team['teamname']  = $teamInfo['title'];
		$hanski_team['league']    = $teamInfo['league'];
		$hanski_team['contact']   = $teamInfo['name'];
		$hanski_team['season']    = $teamInfo['season'];

		$imgSize = false;

		// Override the default image size
		if ($teamInfo['size'] != '')
		{
			$size = deserialize($teamInfo['size']);

			if ($size[0] > 0 || $size[1] > 0)
			{
				$imgSize = $teamInfo['size'];
			}
		}
	
		// get player data from db
		$playerInfo = $objPlayers->fetchAllAssoc();
        //print_r($playerInfo);
		foreach($playerInfo as $key=>$value) {
			$hanski_team['players'][$key] = $value;
		}
		
		//$this->Template->hanski_team = $hanski_team;
        $this->Template->team = $this->parseTeam($objTeam, $objPlayers);
        //$this->Template->players = $this->parsePlayers($objPlayers);
		//print_r($hanski_team);
		//print_r($teamInfo);
	}
}