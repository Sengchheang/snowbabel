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
class tx_snowbabel_system_translations {

	/**
	 * @var
	 */
	private $confObj;

	/**
	 * @var
	 */
	private $CopyDefaultLanguage;

	/**
	 * @var
	 */
	private $AvailableLanguages;

	/**
	 * @var
	 */
	private $BlacklistedExtensions;

	/**
	 * @var
	 */
	private $BlacklistedCategories;

	/**
	 * @var
	 */
	private $LocalExtensionPath;

	/**
	 * @var
	 */
	private $SystemExtensionPath;

	/**
	 * @var
	 */
	private $GlobalExtensionPath;

	/**
	 * @var
	 */
	private $SitePath;

	/**
	 * @var
	 */
	private $L10nPath;

	/**
	 * @var
	 */
	private $LoadedExtensions;

	/**
	 * @var
	 */
	private $Translations;

	/**
	 * @var
	 */
	private $CacheFilePath = '';

	/**
	 * @var
	 */
	private $CacheLanguageFile = array();

	/**
	 * @param  $confObj
	 */
	public function __construct($confObj) {

		$this->confObj = $confObj;

			// get Application params
		$this->CopyDefaultLanguage = $this->confObj->getApplicationConfiguration('CopyDefaultLanguage');
		$this->AvailableLanguages = $this->confObj->getApplicationConfiguration('AvailableLanguages');
		$this->BlacklistedExtensions = $this->confObj->getApplicationConfiguration('BlacklistedExtensions');
		$this->BlacklistedCategories = $this->confObj->getApplicationConfiguration('BlacklistedCategories');
		$this->LocalExtensionPath = $this->confObj->getApplicationConfiguration('LocalExtensionPath');
		$this->SystemExtensionPath = $this->confObj->getApplicationConfiguration('SystemExtensionPath');
		$this->GlobalExtensionPath = $this->confObj->getApplicationConfiguration('GlobalExtensionPath');

			// get Extension params
		$this->SitePath = $this->confObj->getExtensionConfiguration('SitePath');
		$this->L10nPath = $this->confObj->getExtensionConfiguration('L10nPath');
		$this->LoadedExtensions = $this->confObj->getExtensionConfigurationLoadedExtensions();

	}

	/**
	 * @return array
	 */
	public function getExtensions() {

		$Extensions = $this->getDirectories();

		$Extensions = $this->removeBlacklistedExtensions($Extensions);

		$Extensions = $this->getExtensionData($Extensions);

		return $Extensions;

	}

	/**
	 * @param array $Extensions
	 * @return array
	 */
	public function getFiles($Extensions) {

		$Files = array();

	    if(count($Extensions) > 0) {
			foreach($Extensions as $Extension) {

					// Get Extension Files
				$Files[$Extension['uid']] = $this->getSystemFiles($Extension['ExtensionPath'], $Extension['uid']);

			}
	    }


		return $Files;
	}

	/**
	 * @param array $Files
	 * @return array
	 */
	public function getLabels($Files) {

		$Labels = array();

		if(count($Files)) {

			foreach($Files as $File) {

				$Labels[$File['FileId']] = $this->getSystemLabels($File['ExtensionPath'] . $File['FileKey'], $File['FileId']);

			}

		}

		return $Labels;

	}

	/**
	 * @param  $Labels
	 * @return array
	 */
	public function getTranslations($Labels) {

		$Translations = array();

		if(count($Labels)) {

				// Make Sure To Reset Variables
			unset($this->CacheFilePath);
			unset($this->CacheLanguageFile);

			foreach($Labels as $Label) {

				$Translations[$Label['LabelId']] = $this->getSystemTranslations($Label['LabelName'], $Label['ExtensionPath'] . $Label['FileKey'], $Label['LabelId']);

			}

				// Make Sure To Reset Variables
			unset($this->CacheFilePath);
			unset($this->CacheLanguageFile);

		}

		return $Translations;

	}

	/**
	 * @param  $Translation
	 * @return void
	 */
	public function updateTranslation($Translation) {

		$FilePath			= $Translation['ExtensionPath'] . $Translation['FileKey'];
		$Language			= $Translation['TranslationLanguage'];
		$LabelName			= $Translation['LabelName'];
		$TranslationValue	= $Translation['TranslationValue'];

			// Get l10n Location
		$TranslationFileName = t3lib_div::llXmlAutoFileName($FilePath, $Language);
		$TranslationFilePath = t3lib_div::getFileAbsFileName($TranslationFileName);

			// Get Data From L10n File
		$Translation = $this->getSystemLanguageFile($TranslationFilePath);

			// Get Original Label
		$Extension = $this->getSystemLanguageFile($FilePath);

		if($Extension) {
				// Get Hash From Original Label
			$OriginalHash = t3lib_div::md5int($Extension['data']['default'][$LabelName]);
				// Set Hash To Translation File
			$Translation['orig_hash'][$Language][$LabelName] = $OriginalHash;
		}

			// Change Value If Not Empty
		if(strlen($TranslationValue)) {
			$Translation['data'][$Language][$LabelName] = $TranslationValue;
		}
			// Otherwise Unset Value
		else {
			if($Translation['data'][$Language][$LabelName]){
				unset($Translation['data'][$Language][$LabelName]);
			}

		}

			// Write File
		$this->writeTranslation($Translation, $TranslationFilePath);

	}

	/**
	 * @return array
	 */
	private function getDirectories() {

		$Directories	= array();
		$RawDirectories = array();

		// get local extension dirs
		$RawDirectories['Local'] = $this->getSystemDirectories($this->SitePath.$this->LocalExtensionPath);

		// get system extension dirs
		$RawDirectories['System'] = $this->getSystemDirectories($this->SitePath.$this->SystemExtensionPath);

		// get global extension dirs
		$RawDirectories['Global'] = $this->getSystemDirectories($this->SitePath.$this->GlobalExtensionPath);


		if(is_array($RawDirectories['System']) && count($RawDirectories['System']) > 0) {
			$Directories = array_merge($Directories, $RawDirectories['System']);
		}

		if(is_array($RawDirectories['Global']) && count($RawDirectories['Global']) > 0) {
			$Directories = array_merge($Directories, $RawDirectories['Global']);
		}

		if(is_array($RawDirectories['Local']) && count($RawDirectories['Local']) > 0) {
			$Directories = array_merge($Directories, $RawDirectories['Local']);
		}

			// Removes Double Entries
		$Directories = array_unique($Directories);

		return $Directories;
	}

	/**
	 * @param  $RawExtensions
	 * @return array
	 */
	private function removeBlacklistedExtensions($RawExtensions) {

		$Extensions = array();
		$BlacklistedExtensions = array();

			// Get Blacklisted Extensions
		if ($this->BlacklistedExtensions) {
			 $BlacklistedExtensions = explode(',',$this->BlacklistedExtensions);
		}

			// Just Use Allowed Extensions
		if(count($RawExtensions)) {
			foreach($RawExtensions as $Extension) {

				if(!in_array($Extension, $BlacklistedExtensions)) {
					array_push($Extensions, $Extension);
				}

			}
		}

		return $Extensions;

	}

	/**
	 * @param  $ExtensionList
	 * @return array
	 */
	private function getExtensionData($ExtensionList) {

		$Extensions = array();

			// Get Data For Every Extension
		if(is_array($ExtensionList)) {
			foreach($ExtensionList as $ExtensionKey) {

				$ExtensionData = $this->getExtension($ExtensionKey);

					// Just Add If Data Available
				if($ExtensionData) {
					array_push($Extensions, $ExtensionData);
				}

			}
		}

		return $Extensions;

	}

	/**
	 * @param  $ExtensionKey
	 * @return array|bool
	 */
	private function getExtension($ExtensionKey) {

		if(is_string($ExtensionKey)) {

				// Locate Where Extension Is Installed
			$ExtensionLocation = $this->getSystemExtensionLocation($ExtensionKey);

				// Get Extension Data From EmConf
			$EMConf = $this->getSystemEMConf($ExtensionLocation['Path']);

				// If Blacklisted Category Return
			if($this->isCategoryBlacklisted($EMConf['ExtensionCategory'])) return false;

				// Add Extension Data
			$ExtensionData = array(
				'ExtensionKey' 					=> $ExtensionKey,
				'ExtensionTitle'				=> $EMConf['ExtensionTitle'] ? $this->getCleanedString($EMConf['ExtensionTitle']) : $ExtensionKey,
				'ExtensionDescription'			=> $this->getCleanedString($EMConf['ExtensionDescription']),
				'ExtensionCategory'				=> $this->getCleanedString($EMConf['ExtensionCategory']),
				'ExtensionIcon'					=> $this->getExtensionIcon($ExtensionLocation, $ExtensionKey),
				'ExtensionLocation'				=> $ExtensionLocation['Location'],
				'ExtensionPath'					=> $ExtensionLocation['Path'],
				'ExtensionLoaded'				=> $this->isExtensionLoaded($ExtensionKey)
			);

			return $ExtensionData;

		}

		return false;

	}

	/**
	 * @param  $ExtensionCategory
	 * @return bool
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
	 * @param  $ExtensionKey
	 * @return bool
	 */
	private function getSystemExtensionLocation($ExtensionKey) {

		$ExtensionPath = false;

		// ORDER'S IMPORTANT!

			// Check System Extension
		$TempExtensionPath = $this->SitePath.$this->SystemExtensionPath.$ExtensionKey.'/';
		if(is_dir($TempExtensionPath)) {
			$ExtensionPath['Path'] = 		$TempExtensionPath;
			$ExtensionPath['Location'] =	'System';
		}

			// Check Global Extension
		$TempExtensionPath = $this->SitePath.$this->GlobalExtensionPath.$ExtensionKey.'/';
		if(is_dir($TempExtensionPath)) {
			$ExtensionPath['Path'] = 		$TempExtensionPath;
			$ExtensionPath['Location'] =	'Global';
		}


			// Check Local Extension
		$TempExtensionPath = $this->SitePath.$this->LocalExtensionPath.$ExtensionKey.'/';
		if(is_dir($TempExtensionPath)) {
			$ExtensionPath['Path'] = 		$TempExtensionPath;
			$ExtensionPath['Location'] =	'Local';
		}


		return $ExtensionPath;
	}

	/**
	 * @param  $ExtensionPath
	 * @return bool
	 */
	private function getSystemEMConf($ExtensionPath) {

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
	 * @param  $Path
	 * @return array|null
	 */
	private function getSystemDirectories($Path) {

		if(isset($Path)) {

			$Directories = t3lib_div::get_dirs($Path);

			if(is_array($Directories)) {
					return $Directories;
			}

		}

		return NULL;

	}

	/**
	 * @param  $FilePath
	 * @param  $FileId
	 * @return array
	 */
	private function getSystemLabels($FilePath, $FileId) {

		$Labels = array();

		$this->CacheFilePath;
		$this->CacheLanguageFile;

			// Get Language File
		$LanguageFile = $this->getSystemLanguageFile($FilePath);

			// Language File Available?
		if($LanguageFile) {

				// Set System Labels
			$LabelData = $LanguageFile['data']['default'];

			if(is_array($LabelData)) {
				foreach($LabelData as $LabelName => $LabelDefault) {

					array_push($Labels, array(
						'FileId'		=> $FileId,
						'LabelName' 	=> $LabelName,
						'LabelDefault'	=> $LabelDefault
					));

				}

			}

		}

		return $Labels;
	}

	/**
	 * @param  $LabelName
	 * @param  $FilePath
	 * @param  $LabelId
	 * @return array
	 */
	private function getSystemTranslations($LabelName, $FilePath, $LabelId) {

		$Translations = array();

		if($FilePath != $this->CacheFilePath || !$this->CacheLanguageFile) {
			$this->CacheFilePath = $FilePath;
			$this->CacheLanguageFile = $this->getSystemLanguageFile($FilePath);
		}

		if($this->CacheLanguageFile){

				// Reset Translation Var
	        $this->Translations  = array();

				// Checks Translations To Show
			if(is_array($this->AvailableLanguages) && count($this->AvailableLanguages) > 0) {

					// Loop Languages
				foreach($this->AvailableLanguages as $Language) {

					$Translation = $this->getSystemTranslation($FilePath, $Language['LanguageKey'], $LabelName);

					array_push($Translations, array(
						'LabelId'		=> $LabelId,
						'TranslationLanguage'	=> $Language['LanguageKey'],
						'TranslationValue'		=> $Translation,
						'TranslationEmpty'		=> $Translation ? 0 : 1
					));

				}
			}
		}

		return $Translations;

	}

	/**
	 * @param  $FilePath
	 * @param  $LanguageKey
	 * @param  $LabelName
	 * @return bool
	 */
    private function getSystemTranslation($FilePath, $LanguageKey, $LabelName) {

	        // While First Loop Get Translation From l10n (And Create File If Not Done Yet)
	    if(empty($this->Translations[$LanguageKey])) {

				// Get l10n Location
			$TranslationFileName = t3lib_div::llXmlAutoFileName($FilePath, $LanguageKey);
			$TranslationFilePath = t3lib_div::getFileAbsFileName($TranslationFileName);

		        // Check If L10n File Available Otherwise Create One
			$this->isSystemTranslationAvailable($LanguageKey, $TranslationFilePath);

		        // Get Data From L10n File
		    $this->Translations[$LanguageKey] = $this->getSystemLanguageFile($TranslationFilePath);

				// Sync Data From L10n With Extension XML
			$this->Translations[$LanguageKey] = $this->syncSystemTranslation(
				$this->Translations[$LanguageKey],
				$LanguageKey,
				$TranslationFilePath
			);

	    }

	        // Return Translation If Available
		if($this->Translations[$LanguageKey]['data'][$LanguageKey][$LabelName]) {
			return $this->Translations[$LanguageKey]['data'][$LanguageKey][$LabelName];
		}

			 // We Always Need A Translation In DB
	    return '';
    }

	/**
	 * @param  $LanguageKey
	 * @param  $TranslationFilePath
	 * @return void
	 */
	private function isSystemTranslationAvailable($LanguageKey, $TranslationFilePath) {

			// Create L10n File
		if ($TranslationFilePath && !@is_file($TranslationFilePath))	{

				// Copy XML Data From Extension To L10n
			if($LanguageKey == 'en' && $this->CopyDefaultLanguage) {
					// Copy Default Labels To English
				$File['data'][$LanguageKey] = $this->CacheLanguageFile['data']['default'];
			}
			else {
				$File['data'][$LanguageKey] = $this->CacheLanguageFile['data'][$LanguageKey];
			}

				// Set Directory
			$DeepDir = dirname(substr($TranslationFilePath,strlen($this->SitePath))).'/';

				// Create XML & Directory
			if (t3lib_div::isFirstPartOfStr($DeepDir, $this->L10nPath . $LanguageKey . '/'))	{

				t3lib_div::mkdir_deep($this->SitePath, $DeepDir);
				$this->writeTranslation($File, $TranslationFilePath);

			}

		}
	}

	/**
	 * @param  $TranslationFile
	 * @param  $LanguageKey
	 * @param  $Path
	 * @return
	 */
	private function syncSystemTranslation($TranslationFile, $LanguageKey, $Path) {

		$Changes		= 0;
		$LabelsDefault	= $this->CacheLanguageFile['data']['default'];

		if(is_array($LabelsDefault)) {
			foreach($LabelsDefault as $LabelName => $LabelDefault) {

					// Label From L10n
				$LabelL10n = $TranslationFile['data'][$LanguageKey][$LabelName];


					// Sync EN With Default If Activated
				if($LanguageKey == 'en' && $this->CopyDefaultLanguage) {
					$LabelDefault = $LabelDefault;
				}
				else {
					$LabelDefault = $this->CacheLanguageFile['data'][$LanguageKey][$LabelName];
				}

					// Compare Default Label With Label From L10n
				if(!empty($LabelDefault) && empty($LabelL10n)) {
					$TranslationFile['data'][$LanguageKey][$LabelName] = $LabelDefault;
					++$Changes;
				}

			}

				// If There Are Changes Write It To XML File
			if($Changes > 0) {
				$this->writeTranslation($TranslationFile, $Path);
			}

		}

		return $TranslationFile;

	}

	/**
	 * @param  $File
	 * @param  $Path
	 * @param bool $SaveToOriginal
	 * @return bool
	 */
	private function writeTranslation($File, $Path, $SaveToOriginal=false) {

		$XmlOptions = array(
			'parentTagMap'=>array(
				'data'=>'languageKey',
				'orig_hash'=>'languageKey',
				'orig_text'=>'languageKey',
				'labelContext'=>'label',
				'languageKey'=>'label'
			)
    	);

		$XmlFile =	'<?xml version="1.0" encoding="utf-8" standalone="yes" ?>'.chr(10);
		$XmlFile .=	t3lib_div::array2xml($File, '', 0, $SaveToOriginal ? 'T3locallang' : 'T3locallangExt', 0, $XmlOptions);

		return t3lib_div::writeFile($Path, $XmlFile);
	}

	/**
	 * @param  $ExtensionPath
	 * @param  $ExtensionId
	 * @return array
	 */
	private function getSystemFiles($ExtensionPath, $ExtensionId) {

		$Files = array();

			// Get Extension Files
		$TempFiles1 = t3lib_div::getAllFilesAndFoldersInPath(
			array(),
			$ExtensionPath,
			'xml',
			0,
			99,
			'\.svn'
		);

		$TempFiles2 = t3lib_div::removePrefixPathFromList(
			$TempFiles1,
			$ExtensionPath
		);

			// Adds New Keys
		if(is_array($TempFiles2)) {
			foreach($TempFiles2 as $Key => $File) {

					// Check Name Convention 'locallang'
				if(strstr($TempFiles2[$Key], 'locallang') !== false) {
					array_push($Files, array(
						'ExtensionId'	=> $ExtensionId,
						'FileKey'   	=> $TempFiles2[$Key]
					));
				}

			}
		}

		return $Files;
	}

	/**
	 * @param  $File
	 * @return bool|mixed
	 */
    private function getSystemLanguageFile($File) {

        if($File) {

                // Gets XML String
            $XmlString = file_get_contents($File);

                // Converts XML String To Array
            $LanguageFile = t3lib_div::xml2array($XmlString);

                // Return If A Filled Array
            if(is_array($LanguageFile)) return $LanguageFile;

        }

        return false;
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
	 * @param  $ExtensionPath
	 * @param  $ExtensionKey
	 * @return string
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

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/snowbabel/Classes/System/class.tx_snowbabel_system_translations.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/snowbabel/Classes/System/class.tx_snowbabel_system_translations.php']);
}

?>