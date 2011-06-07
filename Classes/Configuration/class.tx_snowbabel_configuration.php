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
class tx_snowbabel_Configuration {
	/**
	 *
	 */
	private $configuration;

	/**
	 *
	 */
	private $db;

	/**
	 *
	 */
	private $lang;

	/**
	 *
	 */
	private $extensions;

	/**
	 *
	 */
	private $xmlPath = 'snowbabel/Resources/Private/Language/locallang_translation.xml';

	/**
	 *
	 */
	public $debug;

	/**
	 *
	 */
	private $extjsParams;

///////////////////////////////////////////////////////
///////////////////////////////////////////////////////

// PUBLIC

///////////////////////////////////////////////////////
///////////////////////////////////////////////////////

	/**
	 *
	 */
	public function __construct($extjsParams) {

			// TODO: remove after development !!!
		$this->initFirephp();

		$this->extjsParams = $extjsParams;

		$this->loadConfiguration();

	}



///////////////////////////////////////////////////////
// write config - set
///////////////////////////////////////////////////////

	/**
	 *
	 */
	public function setExtensionConfiguration($value, $name) {

		if(isset($value, $name)) {

			$this->configuration['Extension'][$name] = $value;

			return true;

		}
		else {
			return false;
		}

	}

	/**
	 *
	 */
	public function setExtensionConfigurationLoadedExtensions($value) {

		if(isset($value)) {

			$this->configuration['Extension']['LoadedExtensions'] = $value;

			return true;

		}
		else {
			return false;
		}

	}

	/**
	 *
	 */
	public function setApplicationConfiguration($value, $name) {

		if(isset($value, $name)) {

			$this->configuration['Application'][$name] = $value;

			return true;

		}
		else {
			return false;
		}

	}

	/**
	 *
	 */
	public function setUserConfiguration($value, $name) {

		if(isset($value, $name)) {

			$this->configuration['User'][$name] = $value;

			return true;

		}
		else {
			return false;
		}

	}

	/**
	 *
	 */
	public function setUserConfigurationColumn($value, $name) {

		if(isset($value, $name)) {

				// set 1 and 0 to true and false
			$value = $value ? true : false;

			$this->configuration['User']['Columns'][$name] = $value;

			return true;

		}
		else {
			return false;
		}

	}

	/**
	 *
	 */
	public function setUserConfigurationExtensions($ExtensionList) {

		if(is_array($ExtensionList)) {

			$this->configuration['User']['Extensions'] = $ExtensionList;

			return true;

		}
		else {
			return false;
		}

	}

	/**
	 *
	 */
	public function setExtjsConfiguration($ExtjsParams) {

		if(is_array($ExtjsParams)) {

			$this->configuration['Extjs'] = $ExtjsParams;

			return true;

		}
		else {
			return false;
		}

	}

	/**
	 *
	 */
	public function saveFormSettings() {

			// Get Defined Extjs Values
		$ExtjsParams = $this->getExtjsConfigurationFormSettings();

			// Write Defined Extjs Values To Database
		if(count($ExtjsParams) > 0) {
			foreach($ExtjsParams as $Name => $Value) {

					// checkbox
				if($Value == 'on') $Value = 1;
				if($Value == NULL) $Value = 0;

				$this->db->setAppConf($Name, $Value);

			}
		}

			// Set Languages If Added
		$Languages = $this->getExtjsConfiguration('AddedLanguages');

		if($Languages) {
			$this->db->setAppConf('AvailableLanguages', $Languages);
		}

	}


///////////////////////////////////////////////////////
// read config - get
///////////////////////////////////////////////////////

	/**
	 *
	 */
	public function getLL($LabelName) {

			// use typo3 system function
		return $GLOBALS['LANG']->sL('LLL:EXT:' . $this->xmlPath . ':' . $LabelName);

	}

	/**
	 *
	 */
	public function getConfiguration() {
		return $this->configuration;
	}

	/**
	 *
	 */
	public function getApplicationConfiguration($name) {

		if(isset($name)) {

			return $this->configuration['Application'][$name];

		}
		else {
			return NULL;
		}

	}

	/**
	 *
	 */
	public function getUserConfiguration($name) {

		if(isset($name)) {

			return $this->configuration['User'][$name];

		}
		else {
			return NULL;
		}

	}

	/**
	 *
	 */
	public function getUserConfigurationColumns() {
		return $this->configuration['User']['Columns'];
	}

	/**
	 *
	 */
	public function getUserConfigurationColumn($name) {
		if(isset($name)) {

			return $this->configuration['User']['Columns'][$name];

		}
		else {
			return NULL;
		}
	}

	/**
	 *
	 */
	public function getUserConfigurationId() {
		return $this->configuration['User']['Id'];
	}

	/**
	 *
	 */
	public function getUserConfigurationIsAdmin() {
		return $this->configuration['User']['IsAdmin'];
	}

	/**
	 *
	 */
	public function getExtensionConfiguration($name) {

		if(isset($name)) {

			return $this->configuration['Extension'][$name];

		}
		else {
			return NULL;
		}

	}

	/**
	 *
	 */
	public function getExtensionConfigurationLoadedExtensions() {
		return $this->configuration['Extension']['LoadedExtensions'];
	}

	/**
	 *
	 */
	public function getExtjsConfigurations() {
		if(count($this->configuration['Extjs']) > 0) {
			return $this->configuration['Extjs'];
		}
		else {
			return NULL;
		}
	}

	/**
	 *
	 */
	public function getExtjsConfiguration($name) {

		if(isset($name)) {

			return $this->configuration['Extjs'][$name];

		}
		else {
			return NULL;
		}

	}

	/**
	 *
	 */
	public function getExtjsConfigurationListViewStart() {

		if($this->configuration['Extjs']['start']) {
			return $this->configuration['Extjs']['start'];
		}
		else {
			return $this->configuration['Extjs']['ListViewStart'];
		}

	}

	/**
	 *
	 */
	public function getExtjsConfigurationFormSettings() {

		$ExtjsParams['LocalExtensionPath']			= $this->configuration['Extjs']['LocalExtensionPath'];
		$ExtjsParams['SystemExtensionPath']			= $this->configuration['Extjs']['SystemExtensionPath'];

		$ExtjsParams['ShowLocalExtensions']			= $this->configuration['Extjs']['ShowLocalExtensions'];
		$ExtjsParams['ShowSystemExtensions']			= $this->configuration['Extjs']['ShowSystemExtensions'];
		$ExtjsParams['ShowGlobalExtensions']		= $this->configuration['Extjs']['ShowGlobalExtensions'];

		$ExtjsParams['ShowOnlyLoadedExtensions']	= $this->configuration['Extjs']['ShowOnlyLoadedExtensions'];
		$ExtjsParams['ShowTranslatedLanguages']		= $this->configuration['Extjs']['ShowTranslatedLanguages'];

		$ExtjsParams['BlacklistedExtensions']		= $this->configuration['Extjs']['BlacklistedExtensions'];
		$ExtjsParams['BlacklistedCategories']		= $this->configuration['Extjs']['BlacklistedCategories'];

		$ExtjsParams['XmlFilter']					= $this->configuration['Extjs']['XmlFilter'];

		$ExtjsParams['AutoBackupEditing']			= $this->configuration['Extjs']['AutoBackupEditing'];
		$ExtjsParams['AutoBackupCronjob']			= $this->configuration['Extjs']['AutoBackupCronjob'];

		$ExtjsParams['CopyDefaultLanguage']			= $this->configuration['Extjs']['CopyDefaultLanguage'];

		return $ExtjsParams;
	}

	/**
	 *
	 */
	public function getExtjsConfigurationListViewLimit() {

		if($this->configuration['Extjs']['limit']) {
			return $this->configuration['Extjs']['limit'];
		}
		else {
			return $this->configuration['Extjs']['ListViewLimit'];
		}

	}

	/**
	 *
	 */
	public function getLanguages($AvailableLanguagesDiff = false) {

		//TODO: Get System Languages And Merge With Static
		// $this->db->getSystemLanguages();

		$Languages = $this->db->getStaticLanguages();

		if($AvailableLanguagesDiff) {

			$AvailableLanguages = $this->getApplicationConfiguration('AvailableLanguages');

			if(is_array($Languages)) {

				$LanguagesDiff = array();

				foreach($Languages as $Language) {

					if(!in_array($Language, $AvailableLanguages)) {
						array_push($LanguagesDiff, $Language);
					}

				}

				$Languages = $LanguagesDiff;

			}
		}

		return $Languages;


	}

///////////////////////////////////////////////////////
///////////////////////////////////////////////////////

// PRIVATE

///////////////////////////////////////////////////////
///////////////////////////////////////////////////////

	/**
	 *
	 */
	private function loadConfiguration() {

			// load db object
		$this->initDatabase();

			// load extjs parameters
		$this->loadExtjsConfiguration();

			// load extension configuration
		$this->loadExtensionConfiguration();

			// load application configuration
		$this->loadApplicationConfiguration();

			// load user configuration
		$this->loadUserConfiguration();

			// load actions
		$this->loadActions();
	}

	/**
	 *
	 */
	private function loadExtjsConfiguration() {

			// check if its an object or array
		if(is_object($this->extjsParams)) {
				// extjs obj var to array
			$extjsParams = get_object_vars($this->extjsParams);
				// if something's in add it to conf
			if(!empty($extjsParams)) $this->setExtjsConfiguration($extjsParams);
		}
		elseif(is_array($this->extjsParams)) {
				// if something's in add it to conf
			if(!empty($this->extjsParams)) $this->setExtjsConfiguration($this->extjsParams);
		}

	}

	/**
	 *
	 */
	private function loadExtensionConfiguration() {

		$this->setExtensionConfiguration(t3lib_extMgm::extPath('snowbabel'), 'ExtPath');

		$this->setExtensionConfigurationLoadedExtensions($GLOBALS['TYPO3_LOADED_EXT']);

		$this->setExtensionConfiguration(PATH_site, 'SitePath');

		$this->setExtensionConfiguration('typo3conf/l10n/', 'L10nPath');
	}

	/**
	 *
	 */
	private function loadApplicationConfiguration() {

			// local extension path
		$this->setApplicationConfiguration($this->db->getAppConfLocalExtensionPath(), 'LocalExtensionPath');
			// system extension path
		$this->setApplicationConfiguration($this->db->getAppConfSystemExtensionPath(), 'SystemExtensionPath');
			// global extension path
		$this->setApplicationConfiguration($this->db->getAppConfGlobalExtensionPath(), 'GlobalExtensionPath');


			// show local extension
		$this->setApplicationConfiguration($this->db->getAppConfShowLocalExtensions(), 'ShowLocalExtensions');
			// show system extension
		$this->setApplicationConfiguration($this->db->getAppConfShowSystemExtensions(), 'ShowSystemExtensions');
			// show global extension
		$this->setApplicationConfiguration($this->db->getAppConfShowGlobalExtensions(), 'ShowGlobalExtensions');


			// show only loaded extension
		$this->setApplicationConfiguration($this->db->getAppConfShowOnlyLoadedExtensions(), 'ShowOnlyLoadedExtensions');
			// show translated languages
		$this->setApplicationConfiguration($this->db->getAppConfShowTranslatedLanguages(), 'ShowTranslatedLanguages');


			// blacklist extensions
		$this->setApplicationConfiguration($this->db->getAppConfBlacklistedExtensions(), 'BlacklistedExtensions');
			// blacklist categories
		$this->setApplicationConfiguration($this->db->getAppConfBlacklistedCategories(), 'BlacklistedCategories');


			// xml filter
		$this->setApplicationConfiguration($this->db->getAppConfXmlFilter(), 'XmlFilter');


			// auto backup during editing
		$this->setApplicationConfiguration($this->db->getAppConfAutoBackupEditing(), 'AutoBackupEditing');
			// auto backup during cronjob
		$this->setApplicationConfiguration($this->db->getAppConfAutoBackupCronjob(), 'AutoBackupCronjob');


			// copy default language to english (en)
		$this->setApplicationConfiguration($this->db->getAppConfCopyDefaultLanguage(), 'CopyDefaultLanguage');


			// load available languages
		$this->setApplicationConfiguration(
			$this->db->getAppConfAvailableLanguages(
				$this->getApplicationConfiguration('ShowTranslatedLanguages')
			),
			'AvailableLanguages'
		);

	}

	/**
	 *
	 */
	private function loadUserConfiguration() {

			// set admin mode
		$this->setUserConfiguration($GLOBALS['BE_USER']->user['admin'], 'IsAdmin');

			// set user id
		$this->setUserConfiguration($GLOBALS['BE_USER']->user['uid'], 'Id');

			// set user permitted extensions
		$this->setUserConfiguration($GLOBALS['BE_USER']->user['tx_snowbabel_extensions'], 'PermittedExtensions');

			// set user permitted languages
		$this->setUserConfiguration($GLOBALS['BE_USER']->user['tx_snowbabel_languages'], 'PermittedLanguages');

			// set user groups
		$this->setUserConfiguration($GLOBALS['BE_USER']->userGroups, 'AllocatedGroups');


			// checks if database record already written
		$this->db->getUserConfCheck($this->getUserConfigurationId());

			// get selected languages
		$this->setUserConfiguration($this->db->getUserConfSelectedLanguages($this->getUserConfigurationId()), 'SelectedLanguages');

			// get "showColumn" values from database
		$this->setUserConfigurationColumn($this->db->getUserConfShowColumnLabel($this->getUserConfigurationId()), 'ShowColumnLabel');
		$this->setUserConfigurationColumn($this->db->getUserConfShowColumnDefault($this->getUserConfigurationId()), 'ShowColumnDefault');
		$this->setUserConfigurationColumn($this->db->getUserConfShowColumnPath($this->getUserConfigurationId()), 'ShowColumnPath');
		$this->setUserConfigurationColumn($this->db->getUserConfShowColumnLocation($this->getUserConfigurationId()), 'ShowColumnLocation');

	}

	/**
	 *
	 */
	private function loadActions() {

		$ActionKey = $this->getExtjsConfiguration('ActionKey');
		$LanguageId = $this->getExtjsConfiguration('LanguageId');
		$ColumnId = $this->getExtjsConfiguration('ColumnId');

		if(!empty($LanguageId) && $ActionKey == 'LanguageSelection') {
			$this->actionUserConfSelectedLanguages($LanguageId);
		}

		if(!empty($ColumnId) && $ActionKey == 'ColumnSelection') {
			$this->actionUserConfigurationColumns($ColumnId);
		}

	}

	/**
	 *
	 */
	private function actionUserConfSelectedLanguages($LanguageId) {

		$SelectedLanguages  = $this->getUserConfiguration('SelectedLanguages');

			// Add
		if(!t3lib_div::inList($SelectedLanguages, $LanguageId)) {
			if(!$SelectedLanguages) {
				$SelectedLanguages = $LanguageId;
			}
			else {
				$SelectedLanguages .= ',' . $LanguageId;
			}
		}
			// Remove
		else {
			$SelectedLanguages = t3lib_div::rmFromList($LanguageId, $SelectedLanguages);
		}

			// Write Changes To Database
		$this->db->setUserConf('SelectedLanguages', $SelectedLanguages, $this->getUserConfigurationId());

			// Reset Configuration Array
		$this->setUserConfiguration($SelectedLanguages, 'SelectedLanguages');
	}

	/**
	 *
	 */
	private function actionUserConfigurationColumns($ColumnId) {

		$ColumnsConfiguration = $this->getUserConfigurationColumns();

			// Reverse Value
		$ColumnsConfiguration[$ColumnId] = !$ColumnsConfiguration[$ColumnId];

			// Write Changes To Database
		$this->db->setUserConf($ColumnId, $ColumnsConfiguration[$ColumnId], $this->getUserConfigurationId());

			// Reset Configuration Array
		$this->setUserConfiguration($ColumnsConfiguration, 'Columns');

	}

	/**
	 *
	 */
	private function initFirephp() {

			// TODO: check if firephp already included
			// TODO: add var to autoinclude firephp in configuration
			// firephp - for debugging only
		require_once (t3lib_extMgm::extPath('snowbabel') . 'Resources/Private/Firephp/FirePHP.class.php');

			// init firephp
		if (!is_object($this->debug) && !($this->debug instanceof FirePHP)) {
			$this->debug = FirePHP::getInstance(true);
		}
	}

	/**
	 *
	 */
	private function initDatabase() {

		if (!is_object($this->db) && !($this->db instanceof tx_snowbabel_Db)) {
			$this->db = t3lib_div::makeInstance('tx_snowbabel_Db');
		}

	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/snowbabel/Classes/Configuration/class.tx_snowbabel_configuration.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/snowbabel/Classes/Configuration/class.tx_snowbabel_configuration.php']);
}

?>