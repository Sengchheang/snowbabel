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
	 *
	 */
	private $confObj;

	/**
	 *
	 */
	private $db;

	/**
	 *
	 */
	private $debug;

	/**
	 *
	 */
	public function __construct($confObj) {

		$this->confObj = $confObj;

		$this->db = $confObj->getDb();
		$this->debug = $confObj->debug;

	}

	/**
	 *
	 */
	public function readCache($Type) {

		switch($Type) {
			case 'Extensions':

				$this->getCachedExtensions();

				break;

			default:
				return false;
		}

	}

	/**
	 *
	 */
	public function writeCache($Type, $Data) {

		switch($Type) {
			case 'Extensions':

					// Delete Cache
				$this->deleteCache($Type);

					// Write Cache
				$this->db->insertCachedExtensions($Data);
				break;
		}

	}

	public function deleteCache($Type) {

		switch($Type) {
			case 'Extensions':

				$this->db->deleteCachedExtensions();
				break;
		}

	}

	private function getCachedExtensions() {

			// Get Cached Data From Database
		$tempExtensions = $this->db->getCachedExtensions();

			// Check If Records Available
		if(is_array($tempExtensions) && count($tempExtensions) > 0) {

				// TODO: CacheLivetime should be editable
				// Set Cache Values
			$CacheTime		= $tempExtensions[0]['tstamp'];
			$CacheLifetime	= time() - (60 * 60);
			$CurrentTime	= time();

				// Check First Record If Cache Is Ok
			if($CacheTime <= $CurrentTime && $CacheTime >= $CacheLifetime) {

					// Prepare Extension-Array For Return
				$Extensions = array();

				foreach($tempExtensions as $Extension) {

					array_push($Extensions, $Extension['ExtensionKey']);

				}

				return $Extensions;

			}
		}

		return false;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/snowbabel/Classes/Cache/class.tx_snowbabel_cache.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/snowbabel/Classes/Cache/class.tx_snowbabel_cache.php']);
}

?>