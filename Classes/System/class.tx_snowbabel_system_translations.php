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
	 * @var tx_snowbabel_configuration
	 */
	private static $confObj;

	/**
	 * @var
	 */
	private static $debug;

	/**
	 * @var
	 */
	private static $CopyDefaultLanguage;

	/**
	 * @var
	 */
	private static $AvailableLanguages;

	/**
	 * @var
	 */
	private static $BlacklistedExtensions;

	/**
	 * @var
	 */
	private static $BlacklistedCategories;

	/**
	 * @var
	 */
	private static $LocalExtensionPath;

	/**
	 * @var
	 */
	private static $SystemExtensionPath;

	/**
	 * @var
	 */
	private static $GlobalExtensionPath;

	/**
	 * @var
	 */
	private static $SitePath;

	/**
	 * @var
	 */
	private static $L10nPath;

	/**
	 * @var
	 */
	private static $LoadedExtensions;

	/**
	 * @var
	 */
	private static $CacheTranslationsPath = '';

	/**
	 * @var
	 */
	private static $CachedTranslations;

	/**
	 * @var
	 */
	private static $CacheFilePath = '';

	/**
	 * @var
	 */
	private static $CacheLanguageFile = array();

	/**
	 * @param $confObj
	 * @return void
	 */
	public static function init($confObj) {

		self::$confObj = $confObj;
		self::$debug = $confObj->debug;

			// get Application params
		self::$CopyDefaultLanguage = self::$confObj->getApplicationConfiguration('CopyDefaultLanguage');
		self::$AvailableLanguages = self::$confObj->getApplicationConfiguration('AvailableLanguages');
		self::$BlacklistedExtensions = self::$confObj->getApplicationConfiguration('BlacklistedExtensions');
		self::$BlacklistedCategories = explode(',', self::$confObj->getApplicationConfiguration('BlacklistedCategories'));
		self::$LocalExtensionPath = self::$confObj->getApplicationConfiguration('LocalExtensionPath');
		self::$SystemExtensionPath = self::$confObj->getApplicationConfiguration('SystemExtensionPath');
		self::$GlobalExtensionPath = self::$confObj->getApplicationConfiguration('GlobalExtensionPath');

			// get Extension params
		self::$SitePath = self::$confObj->getExtensionConfiguration('SitePath');
		self::$L10nPath = self::$confObj->getExtensionConfiguration('L10nPath');
		self::$LoadedExtensions = self::$confObj->getExtensionConfigurationLoadedExtensions();
	}

	/**
	 * @return array
	 */
	public static function getExtensions() {

		$Extensions = self::getDirectories();

		$Extensions = self::removeBlacklistedExtensions($Extensions);

		$Extensions = self::getExtensionData($Extensions);

		return $Extensions;

	}

	/**
	 * @param array $Extensions
	 * @return array
	 */
	public static function getFiles($Extensions) {

		$Files = array();

	    if(count($Extensions) > 0) {
			foreach($Extensions as $Extension) {

					// Get Extension Files
				$Files[$Extension['uid']] = self::getSystemFiles($Extension['ExtensionPath'], $Extension['uid']);

			}
	    }


		return $Files;
	}

	/**
	 * @param array $Files
	 * @return array
	 */
	public static function getLabels($Files) {

		$Labels = array();

		if(count($Files)) {

			foreach($Files as $File) {

				$Labels[$File['FileId']] = self::getSystemLabels($File['ExtensionPath'] . $File['FileKey'], $File['FileId']);

			}

		}

		return $Labels;

	}

	/**
	 * @param  $Labels
	 * @return array
	 */
	public static function getTranslations($Labels) {

		$Translations = array();

		if(count($Labels)) {

			foreach($Labels as $Label) {

				$Translations[$Label['LabelId']] = self::getSystemTranslations($Label['LabelName'], $Label['ExtensionPath'] . $Label['FileKey'], $Label['LabelId']);

			}

		}

		return $Translations;

	}

	/**
	 * @param  $Translation
	 * @return void
	 */
	public static function updateTranslation($Translation) {

		$FilePath			= $Translation['ExtensionPath'] . $Translation['FileKey'];
		$Language			= $Translation['TranslationLanguage'];
		$LabelName			= $Translation['LabelName'];
		$TranslationValue	= $Translation['TranslationValue'];

			// Get l10n Location
		$TranslationFileName = t3lib_div::llXmlAutoFileName($FilePath, $Language);
		$TranslationFilePath = t3lib_div::getFileAbsFileName($TranslationFileName);

			// Get Data From L10n File
		$Translation = self::getSystemLanguageFile_New($TranslationFilePath);

			// Get Original Label
		$Extension = self::getSystemLanguageFile_New($FilePath);

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
		self::writeTranslation($Translation, $TranslationFilePath);

			// Delete Temp Files In typo3temp-Folder
		self::deleteSystemCache($FilePath, $Language);

	}

	/**
	 * @return array
	 */
	private static function getDirectories() {

		$Directories	= array();
		$RawDirectories = array();

		// get local extension dirs
		$RawDirectories['Local'] = self::getSystemDirectories(self::$SitePath.self::$LocalExtensionPath);

		// get system extension dirs
		$RawDirectories['System'] = self::getSystemDirectories(self::$SitePath.self::$SystemExtensionPath);

		// get global extension dirs
		$RawDirectories['Global'] = self::getSystemDirectories(self::$SitePath.self::$GlobalExtensionPath);


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
	private static function removeBlacklistedExtensions($RawExtensions) {

		$Extensions = array();
		$BlacklistedExtensions = array();

			// Get Blacklisted Extensions
		if (self::$BlacklistedExtensions) {
			 $BlacklistedExtensions = explode(',',self::$BlacklistedExtensions);
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
	private static function getExtensionData($ExtensionList) {

		$Extensions = array();

			// Get Data For Every Extension
		if(is_array($ExtensionList)) {
			foreach($ExtensionList as $ExtensionKey) {

				$ExtensionData = self::getExtension($ExtensionKey);

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
	private static function getExtension($ExtensionKey) {

		if(is_string($ExtensionKey)) {

				// Locate Where Extension Is Installed
			$ExtensionLocation = self::getSystemExtensionLocation($ExtensionKey);

				// Get Extension Data From EmConf
			$EMConf = self::getSystemEMConf($ExtensionLocation['Path']);

				// If Blacklisted Category Return
			if(self::isCategoryBlacklisted($EMConf['ExtensionCategory'])) return false;

				// Add Extension Data
			$ExtensionData = array(
				'ExtensionKey' 					=> $ExtensionKey,
				'ExtensionTitle'				=> $EMConf['ExtensionTitle'] ? self::getCleanedString($EMConf['ExtensionTitle']) : $ExtensionKey,
				'ExtensionDescription'			=> self::getCleanedString($EMConf['ExtensionDescription']),
				'ExtensionCategory'				=> self::getCleanedString($EMConf['ExtensionCategory']),
				'ExtensionIcon'					=> self::getExtensionIcon($ExtensionLocation, $ExtensionKey),
				'ExtensionLocation'				=> $ExtensionLocation['Location'],
				'ExtensionPath'					=> $ExtensionLocation['Path'],
				'ExtensionLoaded'				=> self::isExtensionLoaded($ExtensionKey)
			);

			return $ExtensionData;

		}

		return false;

	}

	/**
	 * @param  $ExtensionCategory
	 * @return bool
	 */
	private static function isCategoryBlacklisted($ExtensionCategory) {

			// Just Use Allowed Categories
		if($ExtensionCategory && is_array(self::$BlacklistedCategories)) {

			if(in_array($ExtensionCategory, self::$BlacklistedCategories)) {
				return true;
			}

		}

		return false;

	}

	/**
	 * @param  $ExtensionKey
	 * @return bool
	 */
	private static function getSystemExtensionLocation($ExtensionKey) {

		$ExtensionPath = false;

		// ORDER'S IMPORTANT!

			// Check System Extension
		$TempExtensionPath = self::$SitePath.self::$SystemExtensionPath.$ExtensionKey.'/';
		if(is_dir($TempExtensionPath)) {
			$ExtensionPath['Path'] = 		$TempExtensionPath;
			$ExtensionPath['Location'] =	'System';
		}

			// Check Global Extension
		$TempExtensionPath = self::$SitePath.self::$GlobalExtensionPath.$ExtensionKey.'/';
		if(is_dir($TempExtensionPath)) {
			$ExtensionPath['Path'] = 		$TempExtensionPath;
			$ExtensionPath['Location'] =	'Global';
		}


			// Check Local Extension
		$TempExtensionPath = self::$SitePath.self::$LocalExtensionPath.$ExtensionKey.'/';
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
	private static function getSystemEMConf($ExtensionPath) {

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
	private static function getSystemDirectories($Path) {

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
	private static function getSystemLabels($FilePath, $FileId) {

		$Labels = array();

		// TODO:
			// Get Language File
		//$LanguageFile = self::getSystemLanguageFile($FilePath);
		$LanguageFile = self::getSystemLanguageFile_New($FilePath);

			// Language File Available?
		if($LanguageFile) {

				// Set System Labels
			$LabelData = $LanguageFile['data']['default'];

			if(is_array($LabelData)) {
				foreach($LabelData as $LabelName => $LabelDefault) {

					$Labels[] = array(
						'FileId'		=> $FileId,
						'LabelName' 	=> $LabelName,
						'LabelDefault'	=> $LabelDefault
					);

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
	private static function getSystemTranslations($LabelName, $FilePath, $LabelId) {

		$Translations = array();

		if($FilePath != self::$CacheFilePath || !self::$CacheLanguageFile) {
			self::$CacheFilePath = $FilePath;
			self::$CacheLanguageFile = self::getSystemLanguageFile_New($FilePath);
		}

		if(self::$CacheLanguageFile){

				// Checks Translations To Show
			if(is_array(self::$AvailableLanguages) && count(self::$AvailableLanguages) > 0) {

					// Loop Languages
				foreach(self::$AvailableLanguages as $Language) {

					$Translation = self::getSystemTranslation($FilePath, $Language['LanguageKey'], $LabelName);

					$Translations[] = array(
						'LabelId'		=> $LabelId,
						'TranslationLanguage'	=> $Language['LanguageKey'],
						'TranslationValue'		=> $Translation,
						'TranslationEmpty'		=> $Translation ? 0 : 1
					);

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
    private static function getSystemTranslation($FilePath, $LanguageKey, $LabelName) {

	        // While First Loop Get Translation From l10n (And Create File If Not Done Yet)
	    if($FilePath != self::$CacheTranslationsPath || empty(self::$CachedTranslations[$LanguageKey])) {

				// Get l10n Location
			$TranslationFileName = t3lib_div::llXmlAutoFileName($FilePath, $LanguageKey);
			$TranslationFilePath = t3lib_div::getFileAbsFileName($TranslationFileName);

		        // Check If L10n File Available Otherwise Create One
			self::isSystemTranslationAvailable($LanguageKey, $TranslationFilePath);

		        // Get Data From L10n File
		    self::$CachedTranslations[$LanguageKey] = self::getSystemLanguageFile_New($TranslationFilePath);

				// Set New Cached Path
			self::$CacheTranslationsPath = $FilePath;

				// Sync Data From L10n With Extension XML
			self::syncSystemTranslation(
				$LanguageKey,
				$TranslationFilePath
			);

	    }

	        // Return Translation If Available
		if(self::$CachedTranslations[$LanguageKey]['data'][$LanguageKey][$LabelName]) {
			return self::$CachedTranslations[$LanguageKey]['data'][$LanguageKey][$LabelName];
		}

			 // We Always Need A Translation In DB
	    return '';
    }

	/**
	 * @param  $LanguageKey
	 * @param  $TranslationFilePath
	 * @return void
	 */
	private static function isSystemTranslationAvailable($LanguageKey, $TranslationFilePath) {

			// Create L10n File
		if ($TranslationFilePath && !@is_file($TranslationFilePath))	{

				// Copy XML Data From Extension To L10n
			if($LanguageKey == 'en' && self::$CopyDefaultLanguage) {
					// Copy Default Labels To English
				$File['data'][$LanguageKey] = self::$CacheLanguageFile['data']['default'];
			}
			else {
				$File['data'][$LanguageKey] = self::$CacheLanguageFile['data'][$LanguageKey];
			}

				// Set Directory
			$DeepDir = dirname(substr($TranslationFilePath,strlen(self::$SitePath))).'/';

				// Create XML & Directory
			if (t3lib_div::isFirstPartOfStr($DeepDir, self::$L10nPath . $LanguageKey . '/'))	{

				t3lib_div::mkdir_deep(self::$SitePath, $DeepDir);
				self::writeTranslation($File, $TranslationFilePath);

			}

		}
	}

	/**
	 * @static
	 * @param $FilePath
	 * @param $LanguageKey
	 * @param $TranslationFilePath
	 * @return
	 */
	private static function syncSystemTranslation($LanguageKey, $TranslationFilePath) {

		$Changes		= 0;
		$LabelsDefault	= self::$CacheLanguageFile['data']['default'];

		if(is_array($LabelsDefault)) {
			foreach($LabelsDefault as $LabelName => $LabelDefault) {

					// Label From L10n
				$LabelL10n = self::$CachedTranslations[$LanguageKey]['data'][$LanguageKey][$LabelName];


					// Sync EN With Default If Activated
				if($LanguageKey == 'en' && self::$CopyDefaultLanguage) {
					// Do Nothing
				}
				else {
					$LabelDefault = self::$CacheLanguageFile['data'][$LanguageKey][$LabelName];
				}

					// Compare Default Label With Label From L10n
				if(!empty($LabelDefault) && empty($LabelL10n)) {
					self::$CachedTranslations[$LanguageKey]['data'][$LanguageKey][$LabelName] = $LabelDefault;
					++$Changes;
				}

			}

				// If There Are Changes Write It To XML File
			if($Changes > 0) {
				self::writeTranslation(self::$CachedTranslations[$LanguageKey], $TranslationFilePath);
			}

		}

	}

	/**
	 * @param  $File
	 * @param  $Path
	 * @param bool $SaveToOriginal
	 * @return bool
	 */
	private static function writeTranslation($File, $Path, $SaveToOriginal=false) {

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
	private static function getSystemFiles($ExtensionPath, $ExtensionId) {

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

			// free memory
		unset($TempFiles1);

			// Adds New Keys
		if(is_array($TempFiles2)) {
			foreach($TempFiles2 as $Key => $File) {

					// Check Name Convention 'locallang'
				if(strstr($TempFiles2[$Key], 'locallang') !== false) {
					$Files[] = array(
						'ExtensionId'	=> $ExtensionId,
						'FileKey'   	=> $TempFiles2[$Key]
					);
				}

			}
		}

		return $Files;
	}

	/**
	 * @static
	 * @param  $File
	 * @return array|bool|SimpleXMLElement$
	 */
	private static function getSystemLanguageFile_New($File) {

		if(is_file($File)) {

				// Load Xml Object
			$xml = simplexml_load_file($File);

				// Format Xml Object
			$xml = self::formatSimpleXmlObject($xml);

			return $xml;

		}

		return false;
	}

	/**
	 * @static
	 * @param $simpleXmlObject
	 * @return array|bool
	 */
	private static function formatSimpleXmlObject($simpleXmlObject) {

		if($simpleXmlObject) {

			$xmlArray = array();

				// Meta Array
			if(is_array($simpleXmlObject->meta) || is_object($simpleXmlObject->meta)) {

				$xmlArray['meta'] = array();

				foreach($simpleXmlObject->meta as $meta) {
					foreach($meta as $metaData) {

						$metaKey = $metaData->getName();
						$metaValue = trim($metaData[0]);

						if(!empty($metaKey) && is_string($metaKey)) $xmlArray['meta'][$metaKey] = (string) $metaValue;

					}
				}

					// Unset If Not Used
				if(empty($xmlArray['meta'])) unset($xmlArray['meta']);

			}


				// Data Array
			if(is_array($simpleXmlObject->data->languageKey) || is_object($simpleXmlObject->data->languageKey)) {

				$xmlArray['data'] = array();

				foreach($simpleXmlObject->data->languageKey as $language) {

						// LanguageKey
					$languageKey = self::getSimpleXmlObjectAttributesIndex($language->attributes());

					if(!empty($languageKey) && is_string($languageKey)) {
						if(is_array($language->label) || is_object($language->label)) {
							foreach($language->label as $label) {

									// LabelName
								$labelName = self::getSimpleXmlObjectAttributesIndex($label->attributes());

									// LabelValue
								if(!empty($labelName) && is_string($labelName)) $xmlArray['data'][$languageKey][$labelName] = (string) trim($label[0]);

							}
						}
					}
				}

					// Unset If Not Used
				if(empty($xmlArray['data'])) unset($xmlArray['data']);

			}

			return $xmlArray;

		}

		return false;

	}

	/**
	 * @static
	 * @param $attributesObject
	 * @return string
	 */
	private static function getSimpleXmlObjectAttributesIndex($attributesObject) {

			// Get Attributes
		if(is_array($attributesObject) || is_object($attributesObject)) {

			$attributes = array();

			foreach($attributesObject as $name => $value){
				$attributes[$name] = trim($value);
			}

				// Return Index
			if(!empty($attributes['index'])) return (string) $attributes['index'];
		}

		return '';
	}

	/**
	 * @param  $String
	 * @return string
	 */
    private static function getCleanedString($String) {

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
	private static function getExtensionIcon($ExtensionPath, $ExtensionKey) {

		if($ExtensionPath && $ExtensionKey) {

			if(file_exists($ExtensionPath['Path'] . 'ext_icon.gif')) {

					// Check The Location And Get The CSS Path
				switch($ExtensionPath['Location']) {

					case 'Local':

						$ExtensionPath = self::$LocalExtensionPath;

						break;

					case 'Global':

						$ExtensionPath = self::$GlobalExtensionPath;

						break;

					case 'System':

						$ExtensionPath = self::$SystemExtensionPath;

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

		return '';

	}

	/**
	 * @param  $ExtensionKey
	 * @return bool
	 */
	private static function isExtensionLoaded($ExtensionKey) {

		if(isset($ExtensionKey)) {
			$InstalledExtensions = self::$LoadedExtensions;

			$Check = array_key_exists($ExtensionKey, $InstalledExtensions);

			if($Check) {
				return true;
			}
			else {
				return false;
			}
		}

		return false;

	}

	/**
	 * @param  $FilePath
	 * @param  $Language
	 * @return void
	 */
	private static function deleteSystemCache($FilePath, $Language) {

				// Delete Cached Language File
			$cacheFileName = self::getCacheFileName($FilePath, $Language);
			t3lib_div::unlink_tempfile($cacheFileName);

				// Delete 'default'
			if($Language != 'default') {
				$cacheFileNameDefault = self::getCacheFileName($FilePath);
				t3lib_div::unlink_tempfile($cacheFileNameDefault);
			}
	}

	/**
	 * @param  $FilePath
	 * @param string $Language
	 * @return string
	 */
	private static function getCacheFileName($FilePath, $Language='default') {

			$hashSource = substr($FilePath, strlen(PATH_site)) . '|' . date('d-m-Y H:i:s', filemtime($FilePath)) . '|version=2.3';
			$hash = '_' . t3lib_div::shortMD5($hashSource);
			$tempPath = PATH_site . 'typo3temp/llxml/';
			$fileExtension = substr(basename($FilePath), 10, 15);

			return $tempPath . $fileExtension . $hash . '.' . $Language . '.' . 'utf-8' . '.cache';
	}

		/**
		 * @var int
		 */
		private static $performanceFirstTime = 0;

		/**
		 * @var int
		 */
		private static $performanceLastTime = 0;

		/**
		 * @var int
		 */
		private static $performanceLastMemory = 0;

		/**
		 * @return void
		 */
		private function startPerformance() {

				// Time
			self::$performanceLastTime = microtime(true);
			self::$performanceFirstTime = self::$performanceLastTime;

				// Memory
			self::$performanceLastMemory = memory_get_usage();

		}

		/**
		 * @static
		 * @param string $Key
		 * @return void
		 */
		private static function logPerformance($Key) {

			// TIME

			$unit = ' micros';
			$current = microtime(true);

			$Time = array(
				'currentTime' => $current . $unit,
				'lastTime' => self::$performanceLastTime . $unit,
				'diffTime' => $current - self::$performanceLastTime . $unit,
				'sinceStart' => $current - self::$performanceFirstTime . $unit
			);

			$unit = ' byte';
			self::$performanceLastTime = $current;

			// MEMORY

			$current = memory_get_usage();

			$Memory = array(
				'currentMemory' => $current . $unit,
				'lastMemory' => self::$performanceLastMemory . $unit,
				'diffMemory' => $current - self::$performanceLastMemory . $unit
			);

			self::$performanceLastMemory = $current;


			t3lib_div::debug(array(
				'Time' => $Time,
				'Memory' => $Memory
			), $Key);
		}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/snowbabel/Classes/System/class.tx_snowbabel_system_translations.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/snowbabel/Classes/System/class.tx_snowbabel_system_translations.php']);
}

?>