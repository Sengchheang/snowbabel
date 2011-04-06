<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Daniel Alder <info@snowflake.ch>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Plugin 'Snowbabel' for the 'Snowbabel' extension.
 *
 * @author	Daniel Alder <info@snowflake.ch>
 * @package	TYPO3
 * @subpackage	tx_snowbabel
 */
class tx_snowbabel_Languages {

	/**
	 *
	 */
	private $confObj;

	/**
	 *
	 */
	private $debug;

	/**
	 *
	 */
	private $AvailableLanguages;

	/**
	 *
	 */
	private $IsAdmin;

	/**
	 *
	 */
	private $PermittedLanguages;

	/**
	 *
	 */
	private $AllocatedGroups;

	/**
	 *
	 */
	private $SelectedLanguages;

	/**
	 *
	 */
  public function __construct($confObj) {

		$this->confObj = $confObj;

		$this->debug = $confObj->debug;

			// get Application params
		$this->AvailableLanguages = $this->confObj->getApplicationConfiguration('AvailableLanguages');

			// get Extension params

			// get User parasm
		$this->IsAdmin = $this->confObj->getUserConfigurationIsAdmin();
		$this->PermittedLanguages = $this->confObj->getUserConfiguration('PermittedLanguages');
		$this->AllocatedGroups = $this->confObj->getUserConfiguration('AllocatedGroups');
		$this->SelectedLanguages = $this->confObj->getUserConfiguration('SelectedLanguages');
	}

	/**
	 *
	 */
	public function getLanguages() {

			// get user languages
		$UserLanguages = $this->getLanguagesUser();

			// set selected languages
		$UserLanguages = $this->getLanguagesSelected($UserLanguages);

		return $UserLanguages;
	}

	/**
	 *
	 */
	private function getLanguagesUser() {

		$Languages = array();

			// Admin - application languages
		if($this->IsAdmin) {
			$Languages = $this->AvailableLanguages;
		}
			// Cm - permitted languages
		else {

				// get permitted languages
			$PermittedLanguages = explode(',', $this->PermittedLanguages);

				// add application language if is permitted language
			if(is_array($this->AvailableLanguages)) {

				foreach($this->AvailableLanguages as $AvailableLanguage) {

					if(array_search($AvailableLanguage['LanguageId'], $PermittedLanguages) !== false) {

								// add permitted language to language array
							array_push($Languages, $AvailableLanguage);

					}

				}

			}

		}

		return $Languages;
	}

	/**
	 *
	 */
	private function getLanguagesSelected($UserLanguages) {

		$Languages = array();

			// selected languages
		$SelectedLanguages = explode(',', $this->SelectedLanguages);

		if(count($UserLanguages) > 0) {

			foreach($UserLanguages as $key => $UserLanguage) {

				if(array_search($UserLanguage['LanguageId'], $SelectedLanguages) !== false) {
					$selected = true;
				}
				else {
					$selected = false;
				}

					// add marker to array
				$UserLanguages[$key]['LanguageSelected'] = $selected;
			}


			$Languages = $UserLanguages;
		}

		return $Languages;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/snowbabel/Classes/Languages/class.tx_snowbabel_languages.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/snowbabel/Classes/Languages/class.tx_snowbabel_languages.php']);
}

?>