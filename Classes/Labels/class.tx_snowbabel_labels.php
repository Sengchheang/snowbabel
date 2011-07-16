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
class tx_snowbabel_Labels {

	/**
	 *
	 */
	private $confObj;

	/**
	 *
	 */
	private $langObj;

	/**
	 * @var
	 */
	private $extObj;

	/**
	 *
	 */
	private $cacheObj;

	/**
	 *
	 */
	private $debug;

	/**
	 *
	 */
	private $Languages;

	/**
	 *
	 */
	private $CopyDefaultLanguage;

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
	private $CacheActivated;

	/**
	 *
	 */
	private $SitePath;

	/**
	 *
	 */
	private $L10nPath;

	/**
	 *
	 */
	private $ColumnsConfiguration;

	/**
	 *
	 */
	private $ShowColumnLabel;

	/**
	 *
	 */
	private $ShowColumnDefault;

	/**
	 *
	 */
	private $ShowColumnPath;

	/**
	 *
	 */
	private $ShowColumnLocation;

	/**
	 *
	 */
	private $Labels;

	/**
	 *
	 */
	private $RawLabels = array();

	/**
	 *
	 */
	private $RawLabelsCache = array();

	/**
	 *
	 */
	private $TranslationCache = array();

	/**
	 *
	 */
	private $SearchString;

	/**
	 *
	 */
	private $SearchGlobal;

	/**
	 *
	 */
	private $ExtensionKey;

	/**
	 *
	 */
	private $ListViewStart;

	/**
	 *
	 */
	private	$ListViewLimit;

	/**
	 *
	 */
	private $LabelValue;

	/**
	 *
	 */
	private $LabelName;

	/**
	 *
	 */
	private $LabelPath;

	/**
	 *
	 */
	private $LabelLanguage;

	/**
	 *
	 */
	private $LabelLocation;

	/**
	 *
	 */
	private $LabelExtension;

	/**
	 *
	 */
	private $Translations;

	/**
	 *
	 */
	private $LabelCounter = 0;

	/**
	 *
	 */
  public function __construct($confObj) {

		$this->confObj = $confObj;

		$this->debug = $confObj->debug;

			// get Application params
		$this->CopyDefaultLanguage = $this->confObj->getApplicationConfiguration('CopyDefaultLanguage');
		$this->LocalExtensionPath = $this->confObj->getApplicationConfiguration('LocalExtensionPath');
		$this->SystemExtensionPath = $this->confObj->getApplicationConfiguration('SystemExtensionPath');
		$this->GlobalExtensionPath = $this->confObj->getApplicationConfiguration('GlobalExtensionPath');

		$this->CacheActivated = $this->confObj->getApplicationConfiguration('CacheActivated');

			// get Extension params
		$this->SitePath = $this->confObj->getExtensionConfiguration('SitePath');
		$this->L10nPath = $this->confObj->getExtensionConfiguration('L10nPath');

			// get User params
		$this->ColumnsConfiguration = $this->confObj->getUserConfigurationColumns();

		$this->ShowColumnLabel = $this->ColumnsConfiguration['ShowColumnLabel'];
		$this->ShowColumnDefault = $this->ColumnsConfiguration['ShowColumnDefault'];
		$this->ShowColumnPath = $this->ColumnsConfiguration['ShowColumnPath'];
		$this->ShowColumnLocation = $this->ColumnsConfiguration['ShowColumnLocation'];

	  		// extjs params
		$this->SearchString = $this->confObj->getExtjsConfiguration('SearchString');
		$this->SearchGlobal = $this->confObj->getExtjsConfiguration('SearchGlobal');

		$this->ExtensionKey = $this->confObj->getExtjsConfiguration('ExtensionKey');

		$this->ListViewStart = $this->confObj->getExtjsConfigurationListViewStart();
		$this->ListViewLimit = $this->confObj->getExtjsConfigurationListViewLimit();

	  	$this->LabelValue = $this->confObj->getExtjsConfiguration('LabelValue');
	  	$this->LabelName = $this->confObj->getExtjsConfiguration('LabelName');
	  	$this->LabelPath = $this->confObj->getExtjsConfiguration('LabelPath');
	  	$this->LabelLanguage = $this->confObj->getExtjsConfiguration('LabelLanguage');
	  	$this->LabelLocation = $this->confObj->getExtjsConfiguration('LabelLocation');
	  	$this->LabelExtension = $this->confObj->getExtjsConfiguration('LabelExtension');

			// get language object
		$this->getLanguageObject();

			// get available languages
		$this->Languages = $this->langObj->getLanguages();

	  		// get extensions object
	  	$this->getExtensionsObject();

	  		// Only Needed If Caching Is Activated
	  	if($this->CacheActivated) {

				// Cache Object
			$this->getCacheObject();

		}

	}

	/**
	 *
	 */
	public function setMetaData() {

			// Set metadata to configurate grid properties
		$MetaData['metaData']['idProperty'] = 'LabelId';
		$MetaData['metaData']['root'] = 'LabelRows';

			// Set field for totalcounts -> paging
		$MetaData['metaData']['totalProperty'] = 'ResultCount';

			// Set standard sorting
		$MetaData['metaData']['sortInfo']['field'] = 'LabelName';
		$MetaData['metaData']['sortInfo']['direction'] = 'ASC';

			// Set fields
		$MetaData['metaData']['fields'][0] = 'LabelName';
		$MetaData['metaData']['fields'][1] = 'LabelDefault';
		$MetaData['metaData']['fields'][2] = 'LabelPath';
		$MetaData['metaData']['fields'][3] = 'LabelLocation';
		$MetaData['metaData']['fields'][4] = 'LabelExtension';


			// Add fields for selected languages
		if(is_array($this->Languages)) {
			foreach($this->Languages as $Language) {

				if($Language['LanguageSelected']) {

					array_push($MetaData['metaData']['fields'], 'Label' . strtoupper($Language['LanguageKey']));
					array_push($MetaData['metaData']['fields'], 'Label' . strtoupper($Language['LanguageKey']) . 'Language');

				}

			}
		}

			// Set columns
		$MetaData['columns'] = array(

			array (
					'header' => $this->confObj->getLL('translation_listview_GridHeaderLabel'),
					'dataIndex' => 'LabelName',
					'sortable' => true,
					'hidden' => !$this->ShowColumnLabel
			),

			array (
					'header' => $this->confObj->getLL('translation_listview_GridHeaderDefault'),
					'dataIndex' => 'LabelDefault',
					'sortable' => true,
					'hidden' => !$this->ShowColumnDefault
			),

			array (
					'header' => $this->confObj->getLL('translation_listview_GridHeaderPath'),
					'dataIndex' => 'LabelPath',
					'sortable' => true,
					'hidden' => !$this->ShowColumnPath
			),

			array (
					'header' => $this->confObj->getLL('translation_listview_GridHeaderLocation'),
					'dataIndex' => 'LabelLocation',
					'sortable' => true,
					'hidden' => !$this->ShowColumnLocation
			),

			array (
					'header' => $this->confObj->getLL('translation_listview_GridHeaderExtension'),
					'dataIndex' => 'LabelExtension',
					'sortable' => false,
					'hidden' => true
			),

		);

			// Add columns for selected langauges
		if(is_array($this->Languages)) {
			foreach($this->Languages as $Language) {

				if($Language['LanguageSelected']) {

						// Language Label Texts
					$addColumn = array (
							'header' => $Language['LanguageName'],
							'dataIndex' => 'Label' . strtoupper($Language['LanguageKey']),
							'sortable' => true,
							'editor' => array (
								'xtype' => 'textarea',
								'multiline' => true,
								'grow' => true,
								'growMin' => 30,
								'growMax' => 200
							),
							'renderer' => 'CellPreRenderer'
					);

					array_push($MetaData['columns'], $addColumn);

						// Language Key
					$addColumn = array (
						'header' => 'Label' . $Language['LanguageName'] . 'Language',
						'dataIndex' => 'Label' . strtoupper($Language['LanguageKey']) . 'Language',
						'hidden' => true
					);

					array_push($MetaData['columns'], $addColumn);

				}
			}
		}

			// Add MetaData
		$this->Labels = $MetaData;

			// Add Data Array
		$this->Labels['LabelRows']   = array();

	}

    /**
     *
     */
    public function getSearchGlobal() {

	        // Get All Extensions
	   $Extensions = $this->extObj->getExtensions();

	    if(is_array($Extensions)) {
			foreach($Extensions as $Extension) {

				$this->ExtensionKey = $Extension['ExtensionsKey'];

					// Get Extension Files
				$Files = $this->extObj->getFiles($Extension['ExtensionKey']);

					// Get Labels From Files -> Write To Parameter 'RawLabels'
				$this->getLabelsFromFiles($Files, $Extension['ExtensionKey']);

			}
	    }

			// Does Filter Labels In Parameter 'RawLabels'
		$this->searchLabels();

			// Sorts the RawLabels Var
		$this->sortLabelsFromFiles('LabelName', 'ASC');

			// Get Labels With Paging Configuration
		$this->getLabelsWithPaging();

		return $this->Labels;

    }

    /**
     *
     */
    public function getSearchExtension() {

            // Get Extension Files
        $Files = $this->extObj->getFiles($this->ExtensionKey);

            // Get Labels From Files -> Write To Parameter 'RawLabels'
        $this->getLabelsFromFiles($Files, $this->ExtensionKey);

			// Does Filter Labels In Parameter 'RawLabels'
		$this->searchLabels();

			// Sorts the RawLabels Var
		$this->sortLabelsFromFiles('LabelName', 'ASC');

			// Get Labels With Paging Configuration
		$this->getLabelsWithPaging();

		return $this->Labels;

    }

	/**
	 *
	 */
	public function getLabels() {

            // Get Extension Files
        $Files = $this->extObj->getFiles($this->ExtensionKey);

            // Get Labels From Files -> Write To Parameter 'RawLabels'
        $this->getLabelsFromFiles($Files, $this->ExtensionKey);

			// Sorts the RawLabels Var
		$this->sortLabelsFromFiles('LabelName', 'ASC');

			// Get Labels With Paging Configuration
		$this->getLabelsWithPaging();

		return $this->Labels;

	}

	/**
	 *
	 */
	public function updateTranslation() {

			// Create File Path
		$File = $this->SitePath;

		switch($this->LabelLocation) {
			case 'Local':

				$File .= $this->LocalExtensionPath;

				break;

			case 'System':

				$File .= $this->SystemExtensionPath;

				break;

			case 'Global':

				$File .= $this->GlobalExtensionPath;

				break;

		}

		$File .= $this->LabelExtension . '/' . $this->LabelPath;

			// Get l10n Location
		$TranslationFileName = t3lib_div::llXmlAutoFileName($File, $this->LabelLanguage);
		$TranslationFilePath = t3lib_div::getFileAbsFileName($TranslationFileName);

			// Get Data From L10n File
		$Translation = $this->getLanguageFile($TranslationFilePath);

			// Get Hash From Original Label
		$ExtensionFilePath = t3lib_extMgm::extPath($this->LabelExtension) . $this->LabelPath;
		$Extension = $this->getLanguageFile($ExtensionFilePath);

			// Set Hash To Translation File
		if($Extension) {
			$OriginalHash = t3lib_div::md5int($Extension['data']['default'][$this->LabelName]);
			$Translation['orig_hash'][$this->LabelLanguage][$this->LabelName] = $OriginalHash;
		}

			// Change Value If Not Empty
		if(strlen($this->LabelValue)) {
			$Translation['data'][$this->LabelLanguage][$this->LabelName] = $this->LabelValue;

				// Write Cache
			if($this->CacheActivated) {

				$Conf = array(
					'LabelExtension'			=> $this->LabelExtension,
					'LabelPath'					=> $this->LabelPath,
					'LabelLocation'				=> $this->LabelLocation,
					'LabelTranslationLanguage'	=> $this->LabelLanguage,
					'LabelTranslationName'		=> $this->LabelName,
					'LabelTranslationValue'		=> $this->LabelValue
				);

					// Update Entry
				$this->cacheObj->updateCache('Translation', $Conf);
			}

		}
			// Otherwise Unset Value
		else {
			if($Translation['data'][$this->LabelLanguage][$this->LabelName]){
				unset($Translation['data'][$this->LabelLanguage][$this->LabelName]);
			}

				// Write Cache
			if($this->CacheActivated) {

				$Conf = array(
					'LabelExtension'			=> $this->LabelExtension,
					'LabelPath'					=> $this->LabelPath,
					'LabelLocation'				=> $this->LabelLocation,
					'LabelTranslationLanguage'	=> $this->LabelLanguage,
					'LabelTranslationName'		=> $this->LabelName
				);

					// Delete Entry
				$this->cacheObj->deleteCache('Translation', $Conf);
			}
		}

			// Write File
		$this->writeTranslation($Translation, $TranslationFilePath);

	}

    /*
     *
     */
    private function getLabelsFromFiles($Files, $ExtensionKey) {

		$Labels = false;

			// Init Cache
		if($this->CacheActivated) {
			$Labels = $this->cacheObj->readCache('Labels', $ExtensionKey);

		}

			// No Cache Available
		if(!$Labels) {
			if(count($Files) > 0) {

				foreach($Files as $File) {

					$this->getLabelsFromFile($File['FilePath'], $File['FileKey'], $File['FileLocation'], $ExtensionKey);

				}

					// Write Cache
				if($this->CacheActivated) {
					$this->cacheObj->writeCache('Labels', $this->RawLabelsCache, $ExtensionKey);
					$this->cacheObj->writeCache('Translations', $this->TranslationCache, $ExtensionKey);
				}

			}
		}
			// Prepare Cache For Output
		else {
				// Prepare Cache Data For Output
			$this->getLabelsFromCache($Labels, $ExtensionKey);
		}

    }

    /*
     *
     */
    private function getLabelsFromFile($FilePath, $FileKey, $FileLocation, $ExtensionKey) {

            // Get Language File
        $LanguageFile = $this->getLanguageFile($FilePath);

        if($LanguageFile) {

	            // Default Labels
            $LabelData          = $LanguageFile['data']['default'];

	            // Reset Translation Var
	        $this->Translations  = array();

            if(is_array($LabelData)) {
                foreach($LabelData as $LabelName => $LabelDefault) {

	                    // Get Default LabelData
	                $this->RawLabels[$this->LabelCounter]['LabelName']      = $LabelName;
	                $this->RawLabels[$this->LabelCounter]['LabelDefault']   = $LabelDefault;
					$this->RawLabels[$this->LabelCounter]['LabelPath']		= $FileKey;
					$this->RawLabels[$this->LabelCounter]['LabelLocation']	= $FileLocation;
					$this->RawLabels[$this->LabelCounter]['LabelExtension']	= $ExtensionKey;

						// Write LabelData To Cache -> Separated From Translations
					$this->RawLabelsCache[$this->LabelCounter] = $this->RawLabels[$this->LabelCounter];

						// Checks Translations To Show
					if(is_array($this->Languages)) {
						foreach($this->Languages as $Language) {

							if($Language['LanguageSelected']) {

									// Add 'Language Col'
								$this->RawLabels[$this->LabelCounter]['Label' . strtoupper($Language['LanguageKey']) . 'Language'] = $Language['LanguageKey'];

									// Get Labels From Translation
								$LabelTranslation = $this->getLabelFromTranslation(
									$LanguageFile,
									$FilePath,
									$Language['LanguageKey'],
									$LabelName
								);

									// Add Translation
								if($LabelTranslation) {

									$this->RawLabels[$this->LabelCounter]['Label' . strtoupper($Language['LanguageKey'])] = $LabelTranslation;

										// Write Cache
									$this->TranslationCache[$this->LabelCounter] = array(
										'LabelTranslationValue'		=> $LabelTranslation,
										'LabelTranslationName'		=> $LabelName,
										'LabelTranslationLanguage'	=> $Language['LanguageKey'],
										'LabelPath'	=> $FileKey,
										'LabelLocation'	=> $FileLocation,
										'LabelExtension'	=> $ExtensionKey,
									);

								}

							}
						}
					}

	                ++$this->LabelCounter;
                }

            }

        }
    }

    /**
     * @return void
     */
    private function getLabelFromTranslation($LanguageFile, $File, $LanguageKey, $LabelName) {

	        // While First Loop Get Translation From l10n (And Create File If Not Done Yet)
	    if(empty($this->Translations[$LanguageKey])) {

				// Get l10n Location
			$TranslationFileName = t3lib_div::llXmlAutoFileName($File, $LanguageKey);
			$TranslationFilePath = t3lib_div::getFileAbsFileName($TranslationFileName);

		        // Check If L10n File Available Otherwise Create One
			$this->isTranslationAvailable($LanguageFile, $LanguageKey, $TranslationFilePath);

		        // Get Data From L10n File
		    $this->Translations[$LanguageKey] = $this->getLanguageFile($TranslationFilePath);

				// Sync Data From L10n With Extension XML
			$this->Translations[$LanguageKey] = $this->syncTranslation(
				$LanguageFile,
				$this->Translations[$LanguageKey],
				$LanguageKey,
				$TranslationFilePath
			);

	    }

	        // Return Translation If Available
		if($this->Translations[$LanguageKey]['data'][$LanguageKey][$LabelName]) {
			return $this->Translations[$LanguageKey]['data'][$LanguageKey][$LabelName];
		}

	    return false;
    }

	/**
	 * @param  $ExtensionKey
	 * @return void
	 */
	private function getLabelsFromCache($Labels, $ExtensionKey) {

		// TODO:
		// - Paging via MySQL?
		// - Search via MySQL?

			// Translations
		$Translations = $this->cacheObj->readCache('Translations', $ExtensionKey);

		foreach($Labels as $RawLabel) {

				// Standard Data
			$this->RawLabels[$this->LabelCounter] = $RawLabel;

				// Add Translations
			if($Translations && is_array($this->Languages)) {
				foreach($this->Languages as $Language) {

					if($Language['LanguageSelected']) {

							// LabelName
						$this->RawLabels[$this->LabelCounter]['Label' . strtoupper($Language['LanguageKey']) . 'Language'] = $Language['LanguageKey'];

							// LabelValue
						$Translation = $Translations[$Language['LanguageKey']][$RawLabel['LabelName']];

						if(isset($Translation)) {
							$this->RawLabels[$this->LabelCounter]['Label' . strtoupper($Language['LanguageKey'])] = $Translation;
						}

					}
				}
			}

			++$this->LabelCounter;

		}

	}

    /**
     *
     */
    private function getLanguageFile($File) {

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
	 *
	 */
	private function searchLabels(){

		$SearchedLabels = array();
		$SearchCounter  = 0;

		if(is_array($this->RawLabels)) {
			foreach($this->RawLabels as $Label) {

				$Found = false;

					// Checks All Values From Array 'Label' If String Is Available
				if(is_array($Label))  {
					foreach($Label as $Key => $LabelProperty) {
						if(stristr($LabelProperty, $this->SearchString) !== FALSE) $Found = true;
					}
				}

					// Add To Result Array
				if($Found) {
					array_push($SearchedLabels, $Label);
					++$SearchCounter;
				}

			}
		}

		$this->RawLabels = $SearchedLabels;
		$this->LabelCounter = $SearchCounter;

	}

	/**
	 *
	 */
	private function sortLabelsFromFiles($Column, $Sort = 'ASC') {

		if($Sort == 'ASC') {
			$Sort = SORT_ASC;
		}
		else {
			$Sort = SORT_DESC;
		}

		$SortArray = Array();

			// Get Column From Array Which Should Be Sorted
		if(is_array($this->RawLabels)) {
			foreach($this->RawLabels as $Key => $Label) {

				$SortArray[$Key] = $Label[$Column];

			}
		}

			// Sort Array
		array_multisort($SortArray, $Sort, $this->RawLabels);

	}

	/**
	 *
	 */
	private function getLabelsWithPaging() {

		$Counter = 0;

		if(is_array($this->RawLabels) && count($this->RawLabels) > 0) {
			foreach($this->RawLabels as $Label) {

					// Just Add Shown Labels Because Of Paging
				if($Counter >= $this->ListViewStart AND $Counter < $this->ListViewStart + $this->ListViewLimit) {

						// Add Id For Extjs Output
					$Label['LabelId'] = $Counter +1;

						// Add Label to Output Array
					array_push($this->Labels['LabelRows'], $Label);
				}

				++$Counter;
			}
		}

		$this->Labels['ResultCount'] = $this->LabelCounter;
	}

	/**
	 * @return void
	 */
	private function isTranslationAvailable($LanguageFile, $LanguageKey, $TranslationFilePath) {

		// TODO: merge with function $this->writeTranslation

			// Create L10n File
		if ($TranslationFilePath && !@is_file($TranslationFilePath))	{

				// Set XML Options
			$XmlOptions = array(
				'parentTagMap'=>array(
					'data'=>'languageKey',
					'orig_hash'=>'languageKey',
					'orig_text'=>'languageKey',
					'labelContext'=>'label',
					'languageKey'=>'label'
				)
			);

				// Copy XML Data From Extension To L10n
			if($LanguageKey == 'en' && $this->CopyDefaultLanguage) {
					// Copy Default Labels To English
				$XmlData['data'][$LanguageKey] = $LanguageFile['data']['default'];
			}
			else {
				$XmlData['data'][$LanguageKey] = $LanguageFile['data'][$LanguageKey];
			}

				// Set XML File
			$XmlFile = '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>'.chr(10);
			$XmlFile .= t3lib_div::array2xml(
				$XmlData,
				'',
				0,
				'T3locallangExt',
				0,
				$XmlOptions
			);

				// Set Directory
			$DeepDir = dirname(substr($TranslationFilePath,strlen($this->SitePath))).'/';

				// Create XML & Directory
			if (t3lib_div::isFirstPartOfStr($DeepDir, $this->L10nPath . $LanguageKey . '/'))	{

				t3lib_div::mkdir_deep($this->SitePath, $DeepDir);
				t3lib_div::writeFile($TranslationFilePath, $XmlFile);

			}

		}
	}

	/**
	 *
	 */
	private function syncTranslation($LanguageFile, $TranslationFile, $LanguageKey, $Path) {

		$Changes		= 0;
		$LabelsDefault	= $LanguageFile['data']['default'];

		if(is_array($LabelsDefault)) {
			foreach($LabelsDefault as $LabelName => $LabelDefault) {

					// Label From L10n
				$LabelL10n = $TranslationFile['data'][$LanguageKey][$LabelName];


					// Sync EN With Default If Activated
				if($LanguageKey == 'en' && $this->CopyDefaultLanguage) {
					$LabelDefault = $LabelDefault;
				}
				else {
					$LabelDefault = $LanguageFile['data'][$LanguageKey][$LabelName];
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
	 *
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
	 *
	 */
	private function getLanguageObject() {
		if (!is_object($this->langObj) && !($this->langObj instanceof tx_snowbabel_languages)) {
			$this->langObj = t3lib_div::makeInstance('tx_snowbabel_languages', $this->confObj);
		}
	}

	/**
	 *
	 */
	private function getExtensionsObject() {
		if (!is_object($this->extObj) && !($this->extObj instanceof tx_snowbabel_extensions)) {
			$this->extObj = t3lib_div::makeInstance('tx_snowbabel_extensions', $this->confObj);
		}
	}

	/**
	 *
	 */
	private function getCacheObject() {
		if($this->CacheActivated) {
			if (!is_object($this->cacheObj) && !($this->cacheObj instanceof tx_snowbabel_cache)) {
				$this->cacheObj = t3lib_div::makeInstance('tx_snowbabel_cache', $this->confObj);
			}
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/snowbabel/Classes/Configuration/class.tx_snowbabel_labels.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/snowbabel/Classes/Configuration/class.tx_snowbabel_labels.php']);
}

?>
