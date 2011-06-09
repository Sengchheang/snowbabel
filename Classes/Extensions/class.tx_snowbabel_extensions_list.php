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
class tx_snowbabel_Extensions {

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
	private $ShowLocalExtensions;

	/**
	 *
	 */
	private $ShowSystemExtensions;

	/**
	 *
	 */
	private $ShowGlobalExtensions;

	/**
	 *
	 */
	private $BlacklistedExtensions;

	/**
	 *
	 */
	private $BlacklistedCategories;

	/**
	 *
	 */
	private $ShowOnlyLoadedExtensions;

	/**
	 *
	 */
	private $LocalExtensionPath;

	/**
	 *
	 */
	private $SystemExtensionPath;

	/**
	 *
	 */
	private $GlobalExtensionPath;

	/**
	 *
	 */
	private $SitePath;

	/**
	 *
	 */
	private $LoadedExtensions;

	/**
	 *
	 */
	private $IsAdmin;

	/**
	 *
	 */
	private $PermittedExtensions;

	/**
	 *
	 */
	private $AllocatedGroups;

	/**
	 * @param  $confObj
	 */
	public function __construct($confObj) {

		$this->confObj = $confObj;

		$this->debug = $confObj->debug;

			// get Application params
		$this->ShowLocalExtensions = $this->confObj->getApplicationConfiguration('ShowLocalExtensions');
		$this->ShowSystemExtensions = $this->confObj->getApplicationConfiguration('ShowSystemExtensions');
		$this->ShowGlobalExtensions = $this->confObj->getApplicationConfiguration('ShowGlobalExtensions');

		$this->BlacklistedExtensions = $this->confObj->getApplicationConfiguration('BlacklistedExtensions');
		$this->BlacklistedCategories = $this->confObj->getApplicationConfiguration('BlacklistedCategories');

		$this->ShowOnlyLoadedExtensions = $this->confObj->getApplicationConfiguration('ShowOnlyLoadedExtensions');

		$this->LocalExtensionPath = $this->confObj->getApplicationConfiguration('LocalExtensionPath');
		$this->SystemExtensionPath = $this->confObj->getApplicationConfiguration('SystemExtensionPath');
		$this->GlobalExtensionPath = $this->confObj->getApplicationConfiguration('GlobalExtensionPath');

			// get Extension params
		$this->SitePath = $this->confObj->getExtensionConfiguration('SitePath');
		$this->LoadedExtensions = $this->confObj->getExtensionConfigurationLoadedExtensions();

			// get User parasm
		$this->IsAdmin = $this->confObj->getUserConfigurationIsAdmin();
		$this->PermittedExtensions = $this->confObj->getUserConfiguration('PermittedExtensions');
		$this->AllocatedGroups = $this->confObj->getUserConfiguration('AllocatedGroups');
	}

	/**
	 *
	 */
	public function getExtensions($OutputData = false) {

		$Extensions = array();

			// Get All Extensions From local/global/system Directory
		$ExtensionList = $this->getExtensionList();

			// Merge Data To One Array
		$ExtensionList = $this->mergeExtensionList($ExtensionList);

			// Remove Not Allowed Extensions If User Is No Admin
		if(!$this->IsAdmin) {
			$ExtensionList = $this->removeNotAllowedExtensions($ExtensionList);
		}

			// Remove Blacklisted Extensions
		$ExtensionList = $this->removeBlacklistedExtensions($ExtensionList);

			// Get Data For Every Extension
		if(is_array($ExtensionList)) {
			foreach($ExtensionList as $ExtensionKey) {

				$ExtensionData = $this->getExtension($ExtensionKey, $OutputData);

					// Just Add If Data Available
				if($ExtensionData) {
					array_push($Extensions, $ExtensionData);
				}

			}
		}

		$Extensions = $this->sortExtensions($Extensions);

		return $Extensions;

	}

	/**
	 *
	 */
	public function getExtension($ExtensionKey, $OutputData = false) {

		if(is_string($ExtensionKey)) {

				// Is Extension Loaded
			$ExtensionLoaded = $this->isExtensionLoaded($ExtensionKey);

				// Show Only Loaded Extensions If Activated
			if($this->ShowOnlyLoadedExtensions) {
					// If Not Loaded Return
				if(!$ExtensionLoaded) return false;
			}

				// Locate Where Extension Is Installed
			$ExtensionPath = $this->getExtensionLocation($ExtensionKey);

				// Get Extension Data From EmConf
			$EMConf = $this->getEMConf($ExtensionPath['Path']);

				// Is Extension In Blacklisted Category
			$ExtensionIsBlacklisted = $this->isCategoryBlacklisted($EMConf['ExtensionCategory']);

				// If Blacklisted Category Return
			if($ExtensionIsBlacklisted) return false;

				// Get Extension Icon
			$ExtensionIcon = $this->getExtensionIcon($ExtensionPath, $ExtensionKey);

				// Add CSS Class
			$ExtensionCssClass = '';

			if($ExtensionLoaded) {
				$ExtensionCssClass .= 'extension-loaded';
			}
			else {
				$ExtensionCssClass .= 'extension-installed';
			}

				// Add Extension Data
			$ExtensionData = array();
			$ExtensionData['ExtensionKey']              = $ExtensionKey;
			$ExtensionData['ExtensionTitle']            = $EMConf['ExtensionTitle'] ? $this->getCleanedString($EMConf['ExtensionTitle']) : $ExtensionKey;
			$ExtensionData['ExtensionDescription']      = $this->getCleanedString($EMConf['ExtensionDescription']);
			$ExtensionData['ExtensionCategory']         = $this->getCleanedString($EMConf['ExtensionCategory']);
			$ExtensionData['ExtensionIcon']             = $ExtensionIcon;
			$ExtensionData['ExtensionCss']              = $ExtensionCssClass;

                // Do Not Get Back Following Data Because It Will Be Displayed
            if(!$OutputData) {
				$ExtensionData['ExtensionLocation']		= $ExtensionPath['Location'];
                $ExtensionData['ExtensionPath']         = $ExtensionPath['Path'];
            }


			return $ExtensionData;

		}
		else {
			return false;
		}

	}

	/**
	 *
	 */
	public function getDirectories($Path) {

		if(isset($Path)) {

			$Directories = t3lib_div::get_dirs($Path);

			if(is_array($Directories)) {

					return $Directories;
			}
			else {
				return NULL;
			}

		}

	}

    /**
     *
     */
    public function getFiles($ExtensionKey) {

        $Files = array();

            // Get Extension Data
        $Extension = $this->getExtension($ExtensionKey);

            // Get Extension Files
        $TempFiles1 = t3lib_div::getAllFilesAndFoldersInPath(
            array(),
            $Extension['ExtensionPath'],
            'xml',
            0,
            99,
            '\.svn'
        );

        $TempFiles2 = t3lib_div::removePrefixPathFromList(
            $TempFiles1,
            $Extension['ExtensionPath']
        );

            // Adds New Keys
        if(is_array($TempFiles2)) {
            foreach($TempFiles2 as $Key => $File) {

					// Check Name Convention 'locallang'
				if(strstr($TempFiles2[$Key], 'locallang') !== false) {
					array_push($Files, array(
						'FilePath'  	=> $TempFiles1[$Key],
						'FileKey'   	=> $TempFiles2[$Key],
						'FileLocation'	=> $Extension['ExtensionLocation']
					));
				}

            }
        }

        return $Files;
    }

	private function getExtensionList() {

		$Directories = array();

			// get local extension dirs
		if($this->ShowLocalExtensions) {
			$Directories['Local'] = $this->getDirectories($this->SitePath.$this->LocalExtensionPath);
		}
			// get system extension dirs
		if($this->ShowSystemExtensions) {
			$Directories['System'] = $this->getDirectories($this->SitePath.$this->SystemExtensionPath);
		}

			// get global extension dirs
		if($this->ShowGlobalExtensions) {
			$Directories['Global'] = $this->getDirectories($this->SitePath.$this->GlobalExtensionPath);
		}

		return $Directories;
	}

	/**
	 *
	 */
	private function mergeExtensionList(array $RawExtensionList) {

		$ExtensionList = array();

		if(count($RawExtensionList) > 0) {

			if(count($RawExtensionList['System']) > 0) {
				$ExtensionList = array_merge($ExtensionList, $RawExtensionList['System']);
			}

			if(count($RawExtensionList['Global']) > 0) {
				$ExtensionList = array_merge($ExtensionList, $RawExtensionList['Global']);
			}

			if(count($RawExtensionList['Local']) > 0) {
				$ExtensionList = array_merge($ExtensionList, $RawExtensionList['Local']);
			}

		}

			// Removes Double Entries
		$ExtensionList = array_unique($ExtensionList);

		return $ExtensionList;
	}

	/**
	 *
	 */
	private function removeNotAllowedExtensions(array $RawExtensionList) {

			// Define Needed Arrays
		$ExtensionList = array();
		$AllowedExtensions1 = array();
		$AllowedExtensions2 = array();

			// Get Permitted Extensions -> System Configuration
		if ($this->PermittedExtensions) {
			 $Values = explode(',',$this->PermittedExtensions);
			 foreach($Values as $Extension) {
				 array_push($AllowedExtensions1, $Extension);
			 }
		}

			// Get Allocated Groups -> Group/User Permissions
		if(is_array($this->AllocatedGroups)){
			foreach($this->AllocatedGroups as $group){
				if ($group['tx_snowbabel_extensions']) {
					$Values = explode(',',$group['tx_snowbabel_extensions']);
					 foreach($Values as $Extension) {
						 array_push($AllowedExtensions2, $Extension);
					 }
				}
			}
		}

			// Merge Both Together
		$AllowedExtensions = array_merge($AllowedExtensions1, $AllowedExtensions2);

			// Just Use Allowed Extensions
		if(count($RawExtensionList) > 0) {
			foreach($RawExtensionList as $Extension) {

				if(in_array($Extension, $AllowedExtensions)) {
					array_push($ExtensionList, $Extension);
				}

			}
		}

		return $ExtensionList;

	}

	/**
	 *
	 */
	private function removeBlacklistedExtensions($RawExtensionList) {

		$ExtensionList = array();
		$BlacklistedExtensions = array();

			// Get Blacklisted Extensions
		if ($this->BlacklistedExtensions) {
			 $BlacklistedExtensions = explode(',',$this->BlacklistedExtensions);
		}

			// Just Use Allowed Extensions
		if(count($RawExtensionList)) {
			foreach($RawExtensionList as $Extension) {

				if(!in_array($Extension, $BlacklistedExtensions)) {
					array_push($ExtensionList, $Extension);
				}

			}
		}

		return $ExtensionList;

	}

	/**
	 *
	 */
	private function isCategoryBlacklisted($ExtensionCategory) {

			// Just Use Allowed Categories
		if($ExtensionCategory && is_array($this->BlacklistedCategories)) {

			if(in_array($ExtensionCategory, $this->BlacklistedCategories)) {
				return true;
			}

		}

		return false;

	}

	/**
	 *
	 */
	private function getExtensionLocation($ExtensionKey) {

		$ExtensionPath = false;

			// ORDER'S IMPORTANT!

			// Check System Extension
		if($this->ShowSystemExtensions) {
			$TempExtensionPath = $this->SitePath.$this->SystemExtensionPath.$ExtensionKey.'/';
			if(is_dir($TempExtensionPath)) {
				$ExtensionPath['Path'] = 		$TempExtensionPath;
				$ExtensionPath['Location'] =	'System';
			}
		}
			// Check Global Extension
		if($this->ShowGlobalExtensions) {
			$TempExtensionPath = $this->SitePath.$this->GlobalExtensionPath.$ExtensionKey.'/';
			if(is_dir($TempExtensionPath)) {
				$ExtensionPath['Path'] = 		$TempExtensionPath;
				$ExtensionPath['Location'] =	'Global';
			}
		}

			// Check Local Extension
		if($this->ShowLocalExtensions) {
			$TempExtensionPath = $this->SitePath.$this->LocalExtensionPath.$ExtensionKey.'/';
			if(is_dir($TempExtensionPath)) {
				$ExtensionPath['Path'] = 		$TempExtensionPath;
				$ExtensionPath['Location'] =	'Local';
			}
		}

		return $ExtensionPath;
	}

	/**
	 *
	 */
	private function getExtensionIcon($ExtensionPath, $ExtensionKey) {

		if($ExtensionPath && $ExtensionKey) {

			if(file_exists($ExtensionPath['Path'] . 'ext_icon.gif')) {

					// Check The Location And Get The CSS Path
				switch($ExtensionPath['Location']) {

					case 'Local':

						$ExtensionPath = $this->LocalExtensionPath;

						break;

					case 'Global':

						$ExtensionPath = $this->GlobalExtensionPath;

						break;

					case 'System':

						$ExtensionPath = $this->SystemExtensionPath;

						break;
				}

				$ExtensionIcon = '../../../../' . $ExtensionPath . $ExtensionKey . '/ext_icon.gif';

			}
                // Set Default Icon
            else {

                $ExtensionIcon = '../Resources/Public/Images/Miscellaneous/ext_icon.gif';

            }

            return $ExtensionIcon;

		}

	}

	/**
	 *
	 */
	private function getEMConf($ExtensionPath) {

		if($ExtensionPath) {

				// Set EMConf Path
			$EMConfPath = $ExtensionPath . 'ext_emconf.php';

			if(file_exists($EMConfPath)) {

					// Include EMConf
				$EM_CONF = NULL;
				include ($EMConfPath);

					// Add Needed EMConf Params To Array
				$EMConf['ExtensionCategory'] =		$EM_CONF['']['category'];
				$EMConf['ExtensionTitle'] =			$EM_CONF['']['title'];
				$EMConf['ExtensionDescription'] =	$EM_CONF['']['description'];

				return $EMConf;
			}

		}

		return false;

	}

	/**
	 * @param  $ExtensionKey
	 * @return bool
	 */
	private function isExtensionLoaded($ExtensionKey) {

		if(isset($ExtensionKey)) {
			$InstalledExtensions = $this->LoadedExtensions;

			$Check = array_key_exists($ExtensionKey, $InstalledExtensions);

			if($Check) {
				return true;
			}
			else {
				return false;
			}
		}

	}

	/**
	 * @param  $String
	 * @return string
	 */
    private function getCleanedString($String) {

        if($String) {

            $String = htmlentities($String);

        }

        return $String;

    }

	/**
	 * @param  $Extensions
	 * @return array
	 */
	private function sortExtensions($Extensions) {


		if(count($Extensions) > 0) {

			$TempExtensionList	= array();

				// Create Extension List
			foreach($Extensions as $Key => $Extension) {

				$TempExtensionList[$Key] = strtolower($Extension['ExtensionTitle']);

			}

				// Sort Extension List
			asort($TempExtensionList);

				// Reset Original Array
			$TempExtensions		= $Extensions;
			$Extensions			= array();

				// Add Data
			foreach($TempExtensionList as $Key => $Extension) {
				array_push($Extensions, $TempExtensions[$Key]);
			}

		}

		return $Extensions;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/snowbabel/Classes/Extensions/class.tx_snowbabel_extensions_list.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/snowbabel/Classes/Extensions/class.tx_snowbabel_extensions_list.php']);
}

?>