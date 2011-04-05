<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Daniel Alder <dalder@snowflake.ch>
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
 * @author	Daniel Alder <dalder@snowflake.ch>
 * @package	TYPO3
 * @subpackage	tx_snowbabel
 */
class tx_snowbabel_Db {

	/**
	 *
	 */
	private $db;

	/**
	 *
	 */
  public function __construct() {
			// set typo3 db
		$this->db =& $GLOBALS['TYPO3_DB'];
	}

///////////////////////////////////////////////////////
// select db - get
///////////////////////////////////////////////////////

	/**
	 *
	 */
	public function getAppConfLocalExtensionPath() {

			// set configuration
		$name = 'LocalExtensionPath';
		$standardValue = 'typo3conf/ext/';

			// get value
		return $this->getAppConf($name, $standardValue);

	}

	/**
	 *
	 */
	public function getAppConfSystemExtensionPath() {

			// set configuration
		$name = 'SystemExtensionPath';
		$standardValue = 'typo3/sysext/';

			// get value
		return $this->getAppConf($name, $standardValue);

	}

	/**
	 *
	 */
	public function getAppConfGlobalExtensionPath() {

			// set configuration
		$name = 'GlobalExtensionPath';
		$standardValue = 'typo3/ext/';

			// get value
		return $this->getAppConf($name, $standardValue);

	}

	/**
	 *
	 */
	public function getAppConfShowLocalExtensions() {

			// set configuration
		$name = 'ShowLocalExtensions';
		$standardValue = 1;

			// get value
		return $this->getAppConf($name, $standardValue);

	}

	/**
	 *
	 */
	public function getAppConfShowSystemExtensions() {

			// set configuration
		$name = 'ShowSystemExtensions';
		$standardValue = 1;

			// get value
		return $this->getAppConf($name, $standardValue);

	}

	/**
	 *
	 */
	public function getAppConfShowGlobalExtensions() {

			// set configuration
		$name = 'ShowGlobalExtensions';
		$standardValue = 1;

			// get value
		return $this->getAppConf($name, $standardValue);

	}

	/**
	 *
	 */
	public function getAppConfShowOnlyLoadedExtensions() {

			// set configuration
		$name = 'ShowOnlyLoadedExtensions';
		$standardValue = 1;

			// get value
		return $this->getAppConf($name, $standardValue);

	}

	/**
	 *
	 */
	public function getAppConfShowTranslatedLanguages() {

			// set configuration
		$name = 'ShowTranslatedLanguages';
		$standardValue = 0;

			// get value
		return $this->getAppConf($name, $standardValue);

	}

	/**
	 *
	 */
	public function getAppConfBlacklistedExtensions() {

			// set configuration
		$name = 'BlacklistedExtensions';
		$standardValue = 't3quixplorer,indexed_search,rtehtmlarea,t3editor,sv,sys_action,t3skin,belog,ics_awstats,ics_web_awstats,phpmyadmin,terminal,api_macmade,css_styled_content';

			// get value
		return $this->getAppConf($name, $standardValue);

	}

	/**
	 *
	 */
	public function getAppConfBlacklistedCategories() {

			// set configuration
		$name = 'BlacklistedCategories';
		$standardValue = 'module,services,misc,be';

			// get value
		return $this->getAppConf($name, $standardValue);

	}

	/**
	 *
	 */
	public function getAppConfXmlFilter() {

			// set configuration
		$name = 'XmlFilter';
		$standardValue = 1;

			// get value
		return $this->getAppConf($name, $standardValue);

	}

	/**
	 *
	 */
	public function getAppConfAutoBackupEditing() {

			// set configuration
		$name = 'AutoBackupEditing';
		$standardValue = 1;

			// get value
		return $this->getAppConf($name, $standardValue);

	}

	/**
	 *
	 */
	public function getAppConfAutoBackupCronjob() {

			// set configuration
		$name = 'AutoBackupCronjob';
		$standardValue = 0;

			// get value
		return $this->getAppConf($name, $standardValue);

	}

	/**
	 *
	 */
	public function getAppConfCopyDefaultLanguage() {

			// set configuration
		$name = 'CopyDefaultLanguage';
		$standardValue = 1;

			// get value
		return $this->getAppConf($name, $standardValue);

	}

	/**
	 *
	 */
	public function getAppConfAvailableLanguages($ShowTranslatedLanguages = false) {

			// set configuration
		$name			= 'AvailableLanguages';
		$standardValue	= '30';

			// get value
		$TempLanguages	= $this->getAppConf($name, $standardValue);
		$TempLanguages	= explode(",", $TempLanguages);

		$Languages		= array();

		if(is_array($TempLanguages)) {
			foreach($TempLanguages as $TempLanguageId) {

				$Language = $this->getStaticLanguages($TempLanguageId, $ShowTranslatedLanguages);

				if(!empty($Language)) {
					array_push($Languages, $Language);
				}

			}
		}

		return $Languages;
	}

	/**
	 *
	 */
	public function getAppConf($Name, $StandardValue) {

		if(isset($Name, $StandardValue)) {

			$select = $this->db->exec_SELECTgetRows(
				'confvalue',
				'tx_snowbabel_conf',
				'confname=\'' . $Name . '\'',
				'',
				'',
				'1'
			);

				// is there is no data available create new record
			if(!count($select)) {

					// insert
				$this->insertAppConf($Name, $StandardValue);

					// get again
				return $this->getAppConf($Name, $StandardValue);
			}
			else {
					// return value
				return $select[0]['confvalue'];
			}
		}
		else {
			return NULL;
		}

	}

	/**
	 *
	 */
	public function getUserConfSelectedLanguages($BeUserId) {

			// set configuration
		$name = 'SelectedLanguages';

			// get value
		return $this->getUserConf($name, $BeUserId);

	}

	/**
	 *
	 */
	public function getUserConfShowColumnLabel($BeUserId) {

			// set configuration
		$name = 'ShowColumnLabel';

			// get value
		return $this->getUserConf($name, $BeUserId);

	}

	/**
	 *
	 */
	public function getUserConfShowColumnDefault($BeUserId) {

			// set configuration
		$name = 'ShowColumnDefault';

			// get value
		return $this->getUserConf($name, $BeUserId);

	}

	/**
	 *
	 */
	public function getUserConfShowColumnPath($BeUserId) {

			// set configuration
		$name = 'ShowColumnPath';

			// get value
		return $this->getUserConf($name, $BeUserId);

	}

	/**
	 *
	 */
	public function getUserConfShowColumnLocation($BeUserId) {

			// set configuration
		$name = 'ShowColumnLocation';

			// get value
		return $this->getUserConf($name, $BeUserId);

	}

	/**
	 *
	 */
	public function getUserConf($name, $BeUserId) {
		if(isset($name, $BeUserId)) {

			$select = $this->db->exec_SELECTgetRows(
				$name,
				'tx_snowbabel_users',
				'deleted=0 AND be_users_uid=' . $BeUserId,
				'',
				'',
				'1'
			);

				// return value
			return $select[0][$name];

		}
		else {
			return NULL;
		}
	}

	/**
	 *
	 */
	public function getUserConfCheck($BeUserId) {

		if($BeUserId > 0) {
			$select = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				$select_fields = 'uid',
				$from_table = 'tx_snowbabel_users',
				$where_clause = 'deleted=0 AND be_users_uid=' . $BeUserId,
				$groupBy = '',
				$orderBy = '',
				$limit = '1'
			);

			if(!$select) {

					// insert database row
				$this->insertUserConfCheck($BeUserId);
			}
		}

	}

	/**
	 *
	 */
	public function getStaticLanguages($LanguageId = false, $ShowTranslatedLanguages=false) {

		$WhereClause = '';

			// search single language
		if(is_numeric($LanguageId)) {
			$WhereClause = 'uid='.$LanguageId;
		}

			// sort by english or local
		if(!$ShowTranslatedLanguages) {
			$OrderBy = 'lg_name_en';
		}
		else {
			$OrderBy = 'lg_name_local';
		}

		$Select = $this->db->exec_SELECTgetRows(
			'*',
			'static_languages',
			$WhereClause,
			'',
			$OrderBy,
			''
		);

		if(!count($Select)) {

			return NULL;

		}
		else {

			if(is_array($Select)) {

				$Languages = array();

				foreach($Select as $Key => $Language) {

					$Languages[$Key]['LanguageId'] = $Language['uid'];

						// check if languages should be displayed in english or local
					if(!$ShowTranslatedLanguages) {
						$Languages[$Key]['LanguageName'] = $Language['lg_name_en'];
					}
					else {
						$Languages[$Key]['LanguageName'] = $Language['lg_name_local'];
					}

					$Languages[$Key]['LanguageNameEn'] = $Language['lg_name_en'];
					$Languages[$Key]['LanguageNameLocal'] = $Language['lg_name_local'];
					$Languages[$Key]['LanguageKey'] = $Language['lg_typo3'] ? $Language['lg_typo3'] : strtolower($Language['lg_iso_2']);

				}

				if($LanguageId) return $Languages[$Key];

				return $Languages;
			}
			
			else {
				return NULL;
			}

		}

	}

///////////////////////////////////////////////////////
// update db - set
///////////////////////////////////////////////////////

	/**
	 *
	 */
	public function setAppConf($Name, $Value) {
		$this->db->exec_UPDATEquery(
			$table = 'tx_snowbabel_conf',
			$where_clause = 'confname=\'' . $Name . '\'',
			$fields_values = array(
				'tstamp'=>time(),
				'confvalue' => $Value
			)
		);
	}

	/**
	 *
	 */
	public function setUserConf($Name, $Value, $BeUserId) {
		$this->db->exec_UPDATEquery(
			$table = 'tx_snowbabel_users',
			$where_clause = 'deleted=0 AND be_users_uid=' . $BeUserId,
			$fields_values = array(
				'tstamp'=>time(),
				$Name => $Value
			)
		);
	}

///////////////////////////////////////////////////////
// insert db - insert
///////////////////////////////////////////////////////

	/**
	 *
	 */
	public function insertAppConf($Name, $StandardValue) {

		$this->db->exec_INSERTquery(
			$table = 'tx_snowbabel_conf',
			$fields_values = array(
				'tstamp' => time(),
				'crdate' => time(),
				'confname' => $Name,
				'confvalue' => $StandardValue
			)
		);

	}

	/**
	 *
	 */
	public function insertUserConfCheck($BeUserId) {

		$insert = $this->db->exec_INSERTquery(
			$table = 'tx_snowbabel_users',
			$fields_values = array(
					'tstamp' => time(),
					'crdate' => time(),
					'be_users_uid' => $BeUserId
			)
		);

	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/snowbabel/Classes/Db/class.tx_snowbabel_db.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/snowbabel/Classes/Db/class.tx_snowbabel_db.php']);
}

?>