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
class tx_snowbabel_system_indexing extends tx_scheduler_Task {

	/**
	 * @var tx_snowbabel_Configuration
	 */
	private $confObj;

	/**
	 * @var tx_snowbabel_system_translations
	 */
	private $SystemTranslation;

	/**
	 * @var
	 */
	private $SystemStatistic;

	/**
	 * @var tx_snowbabel_Db
	 */
	private $Db;

	/**
	 * @var
	 */
	private $CurrentTableId;

	/**
	 * @return void
	 */
	public function init() {

			// Init Configuration
		$this->initConfiguration();

			// Init System Translations
		$this->initSystemTranslations();

			// Init System Statistics
			// TODO: not yet implemented
		$this->initSystemStatistics();

	}

	/**
	 * @return bool
	 */
	public function execute() {

		$this->init();

			// Get Current TableId
		$this->CurrentTableId = $this->Db->getCurrentTableId();

			// Indexing Extensions
		$this->indexingExtensions();

			// Indexing Files
		$this->indexingFiles();

			// Indexing Labels
		$this->indexingLabels();

			// Indexing Translations
		$this->indexingTranslations();

			// Switch CurrentTableId
		$this->Db->setCurrentTableId($this->CurrentTableId);

			// Add Scheduler Check To Localconf
		$this->confObj->setSchedulerCheck();

		return true;
	}

	/**
	 * @return void
	 */
	private function indexingExtensions() {

			// Get Extensions From Typo3
		$Extensions = $this->SystemTranslation->getExtensions();

			// Write Extensions To Database
		$this->Db->setExtensions($Extensions, $this->CurrentTableId);

	}

	/**
	 * @return void
	 */
	private function indexingFiles() {

			// Get Extensions From Database
		$Extensions = $this->Db->getExtensions($this->CurrentTableId);

			// Get Files From Typo3
		$Files = $this->SystemTranslation->getFiles($Extensions);

			// Write Extensions To Database
		$this->Db->setFiles($Files, $this->CurrentTableId);

	}

	/**
	 * @return void
	 */
	private function indexingLabels() {

			// Get Files From Database
		$Files = $this->Db->getFiles($this->CurrentTableId);

			// Get Labels From Typo
		$Labels = $this->SystemTranslation->getLabels($Files);

			// Write Labels To Database
		$this->Db->setLabels($Labels, $this->CurrentTableId);

	}

	/**
	 * @return void
	 */
	private function indexingTranslations() {

			// Get Labels From Database
		$Labels = $this->Db->getLabels($this->CurrentTableId);

			// Get Translations From Typo
		$Translations = $this->SystemTranslation->getTranslations($Labels);

			// Write Translations To Database
		$this->Db->setTranslations($Translations, $this->CurrentTableId);

	}

	/**
	 * @return void
	 */
	private function initConfiguration() {

		if (!is_object($this->confObj) && !($this->confObj instanceof tx_snowbabel_Configuration)) {
			$this->confObj = t3lib_div::makeInstance('tx_snowbabel_Configuration', array());

			$this->Db = $this->confObj->getDb();
		}

	}

	/**
	 * @return void
	 */
	private function initSystemTranslations() {
		if (!is_object($this->SystemTranslation) && !($this->SystemTranslation instanceof tx_snowbabel_system_translations)) {
			$this->SystemTranslation = t3lib_div::makeInstance('tx_snowbabel_system_translations', $this->confObj);
		}
	}

	/**
	 * @return void
	 */
	private function initSystemStatistics() {
		if (!is_object($this->SystemStatistic) && !($this->SystemStatistic instanceof tx_snowbabel_system_statistics)) {
			$this->SystemStatistic = t3lib_div::makeInstance('tx_snowbabel_system_statistics', $this->confObj);
		}
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/snowbabel/Classes/TCA/class.tx_snowbabel_system_indexing.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/snowbabel/Classes/TCA/class.tx_snowbabel_system_indexing.php']);
}

?>