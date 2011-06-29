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
class tx_snowbabel_Cache {

	/**
	 * @var
	 */
	private $confObj;

	/**
	 * @var
	 */
	private $db;

	/**
	 * @var
	 */
	private $debug;

	/**
	 * @param  $confObj
	 */
	public function __construct($confObj) {

		$this->confObj = $confObj;

		$this->db = $confObj->getDb();
		$this->debug = $confObj->debug;

	}

	/**
	 * @param  $Type
	 * @param bool $Optional
	 * @return bool
	 */
	public function readCache($Type, $Optional=false) {

		switch($Type) {

			case 'Extensions':

				return $this->getCachedExtensions();

				break;

			case 'ExtensionData':

				return $this->getCachedExtensionData($Optional);

				break;

			default:
				return false;
		}

	}

	/**
	 * @param  $Type
	 * @param  $Data
	 * @return void
	 */
	public function writeCache($Type, $Data) {

		switch($Type) {

			case 'Extensions':

				$this->writeCachedExtensions($Type, $Data);

				break;

			case 'ExtensionData':

				$this->writeCachedExtensionData($Data);

				break;
		}

	}

	/**
	 * @param  $Type
	 * @return void
	 */
	public function deleteCache($Type, $Optional=false) {

		switch($Type) {
			case 'Extensions':

				$this->deleteCachedExtensions();

				break;

		}

	}

	/**
	 * @return array|bool
	 */
	private function getCachedExtensions() {

			// Get Cached Data From Database
		$tempExtensions = $this->db->getCachedExtensions();

			// Check Records If Cache Is Ok
		$CacheCheck = $this->checkCache($tempExtensions, true);

		if($CacheCheck) {

				// Prepare Extension-Array For Return
			$Extensions = array();

			foreach($tempExtensions as $Extension) {

				array_push($Extensions, $Extension['ExtensionKey']);

			}

			return $Extensions;

		}

		return false;

	}

	/**
	 * @param  $Type
	 * @param  $Data
	 * @return void
	 */
	private function writeCachedExtensions($Type, $Data) {
			// Delete Cache
		$this->deleteCache($Type);

			// Write Cache
		$this->db->insertCachedExtensions($Data);

	}

	/**
	 * @return void
	 */
	private function deleteCachedExtensions() {

			// Delete Cache
		$this->db->deleteCachedExtensions();

	}

	/**
	 * @param  $ExtensionKey
	 * @return array|bool
	 */
	private function getCachedExtensionData($ExtensionKey) {

			// Get Cached Data From Database
		$tempExtensionData = $this->db->getCachedExtensionData($ExtensionKey);

			// Check Records If Cache Is Ok
		$CacheCheck = $this->checkCache($tempExtensionData);

		if($CacheCheck) {

				// Prepare ExtensionData-Array For Return
			$ExtensionData = array();

			foreach($tempExtensionData as $Extension) {

				$ExtensionData = array(
					'ExtensionKey'			=> $Extension['ExtensionKey'],
					'ExtensionCategory'		=> $Extension['ExtensionCategory'],
					'ExtensionTitle'		=> $Extension['ExtensionTitle'],
					'ExtensionDescription'	=> $Extension['ExtensionDescription'],
					'ExtensionIcon'			=> $Extension['ExtensionIcon'],
					'ExtensionCss'			=> $Extension['ExtensionCss'],
					'ExtensionLocation'		=> $Extension['ExtensionLocation'],
					'ExtensionPath' 		=> $Extension['ExtensionPath']
				);

			}

			return $ExtensionData;

		}

		return false;

	}

	/**
	 * @param  $Type
	 * @param  $Data
	 * @return void
	 */
	private function writeCachedExtensionData($Data) {

			// Update Cache
		$this->db->updateCachedExtensionData($Data);

	}

	/**
	 * @param  $CacheArray
	 * @param bool $CheckCrdata
	 * @return bool
	 */
	private function checkCache($CacheArray, $CheckCrdata=false) {

			// Check If Records Available
		if(is_array($CacheArray) && count($CacheArray) > 0) {

			if($CheckCrdata) {
				$CacheTime = $CacheArray[0]['crdate'];
			}
			else {
				$CacheTime = $CacheArray[0]['tstamp'];
			}

				// Check First Record If Cache Is Ok
			$CacheCheck = $this->checkCacheLifetime($CacheTime);
			if($CacheCheck) return true;

		}

		return false;

	}

	/**
	 * @param  $CacheTime
	 * @return bool
	 */
	private function checkCacheLifetime($CacheTime) {

			// TODO: CacheLifetime should be editable
			// Set Cache Values
		$CacheLifetime	= time() - (60 * 60);
		$CurrentTime	= time();

			// Check First Record If Cache Is Ok
		if($CacheTime <= $CurrentTime && $CacheTime >= $CacheLifetime) {
			return true;
		}
		else {
			return false;
		}

	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/snowbabel/Classes/Cache/class.tx_snowbabel_cache.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/snowbabel/Classes/Cache/class.tx_snowbabel_cache.php']);
}

?>