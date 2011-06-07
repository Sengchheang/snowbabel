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
 * Extjs for the 'Snowbabel' extension.
 *
 * @author	Daniel Alder <info@snowflake.ch>
 * @package	TYPO3
 * @subpackage	tx_snowbabel
 */
Ext.ns('TYPO3.Snowbabel', 'TYPO3.Snowbabel.Generals', 'TYPO3.Snowbabel.ExtDirect');

	// Standard Values For Baseparams
TYPO3.Snowbabel.Generals.ExtensionKey		= '';

TYPO3.Snowbabel.Generals.ActionKey			= '';

TYPO3.Snowbabel.Generals.LanguageId			= '';
TYPO3.Snowbabel.Generals.ColumnId			= '';

TYPO3.Snowbabel.Generals.LabelValue			= '';
TYPO3.Snowbabel.Generals.LabelName			= '';
TYPO3.Snowbabel.Generals.LabelPath			= '';
TYPO3.Snowbabel.Generals.LabelLanguage		= '';

TYPO3.Snowbabel.Generals.LoadListView		= false;
TYPO3.Snowbabel.Generals.ListViewStart		= 0;
TYPO3.Snowbabel.Generals.ListViewLimit		= 50;

TYPO3.Snowbabel.Generals.SearchGlobal		= false;
TYPO3.Snowbabel.Generals.SearchString		= '';

	// Array With Standard Values From Above
TYPO3.Snowbabel.Generals.ListViewBaseParams = {

        // Extension Selection
	ExtensionKey:   TYPO3.Snowbabel.Generals.ExtensionKey,

		// Listview
    ListViewStart:  TYPO3.Snowbabel.Generals.ListViewStart,
	ListViewLimit:  TYPO3.Snowbabel.Generals.ListViewLimit,

		// Search
	SearchGlobal:   TYPO3.Snowbabel.Generals.SearchGlobal,
	SearchString:   TYPO3.Snowbabel.Generals.SearchString
};

TYPO3.Snowbabel.Generals.LoadListView = function(LoadParams) {

	var Store = Ext.StoreMgr.lookup('ListViewStore');

	///////////////////////////
	// Extension Selection
	///////////////////////////
	TYPO3.Snowbabel.Generals.SetLoadParams('ExtensionKey', Store, LoadParams);

	///////////////////////////
	// Search
	///////////////////////////
	TYPO3.Snowbabel.Generals.SetLoadParams('SearchGlobal', Store, LoadParams);
	TYPO3.Snowbabel.Generals.SetLoadParams('SearchString', Store, LoadParams);

		// Global Search Toggle Button
	TYPO3.Snowbabel.Generals.SetGlobalSearchToggleButton(LoadParams);

		// Search Field
	TYPO3.Snowbabel.Generals.SetSearchField(Store, LoadParams);

		// Load Listview
	Store.load();

};

/**
 *
 */
TYPO3.Snowbabel.Generals.ActionController = function(ActionParams) {

	var Store = Ext.StoreMgr.lookup('ActionControllerStore');

	TYPO3.Snowbabel.Generals.SetLoadParams('ActionKey', Store, ActionParams);

	if(ActionParams['ActionKey'] == 'LanguageSelection') {
		TYPO3.Snowbabel.Generals.SetLoadParams('LanguageId', Store, ActionParams);
	}
	else if(ActionParams['ActionKey'] == 'ColumnSelection') {
		TYPO3.Snowbabel.Generals.SetLoadParams('ColumnId', Store, ActionParams);
	}
	else if(ActionParams['ActionKey'] == 'ListView') {
		TYPO3.Snowbabel.Generals.SetLoadParams('LabelValue', Store, ActionParams);
		TYPO3.Snowbabel.Generals.SetLoadParams('LabelName', Store, ActionParams);
		TYPO3.Snowbabel.Generals.SetLoadParams('LabelPath', Store, ActionParams);
		TYPO3.Snowbabel.Generals.SetLoadParams('LabelLanguage', Store, ActionParams);
		TYPO3.Snowbabel.Generals.SetLoadParams('LabelLocation', Store, ActionParams);
		TYPO3.Snowbabel.Generals.SetLoadParams('LabelExtension', Store, ActionParams);
	}

	Store.load({
		callback: function() {

			if(ActionParams['ActionKey'] == 'LanguageSelection') {
				var LoadParams = new Array();
				TYPO3.Snowbabel.Generals.LoadListView(LoadParams);
			}
			else if(ActionParams['ActionKey'] == 'ColumnSelection') {
				var LoadParams = new Array();
				TYPO3.Snowbabel.Generals.LoadListView(LoadParams);
			}
			else if(ActionParams['ActionKey'] == 'ListView') {
				ActionParams['Record'].commit();
			}
		}
	});

};

/**
 *
 * @param Key
 * @param Store
 * @param LoadParams
 */
TYPO3.Snowbabel.Generals.SetLoadParams = function(Key, Store, LoadParams) {

		// Checks if new record available
	if(LoadParams[Key] || LoadParams[Key] === '' || LoadParams[Key] === false || LoadParams[Key] === true) {

		Store.setBaseParam(Key,  LoadParams[Key]);
		TYPO3.Snowbabel.Generals[Key] = LoadParams[Key];

	}
		// If Nothing Set/Add Last Value
	else {
		Store.setBaseParam(Key, TYPO3.Snowbabel.Generals[Key]);
	}
};

/**
 *
 * @param LoadParams
 */
TYPO3.Snowbabel.Generals.SetGlobalSearchToggleButton = function(LoadParams) {

		// Button
	var GlobalSearchToggleButton = Ext.getCmp('SearchToggle');

		// Enable/Disable Button
	if(LoadParams['SearchGlobal']) {
		GlobalSearchToggleButton.setDisabled(true);
	}
	else {
		GlobalSearchToggleButton.setDisabled(false);
	}
};

/**
 *
 * @param LoadParams
 */
TYPO3.Snowbabel.Generals.SetSearchField = function(Store, LoadParams) {

	if(!LoadParams['SearchString'] && Store.baseParams.SearchString == '') {

			// Empty Search Field
		var SearchField = Ext.getCmp('SearchField').el.dom;
		SearchField.value = '';

	}
};

TYPO3.Snowbabel.Generals.Typo3Header = '<div id="typo3-docheader"><div id="typo3-docheader-row1"><div class="buttonsleft"></div><div class="buttonsright no-border"></div></div><div id="typo3-docheader-row2"></div></div></div>';

TYPO3.Snowbabel.Generals.GeneralSettingsFormSubmit = function() {

	var Form = Ext.getCmp('GeneralSettingsForm').getForm();

	Form.submit({
			// TODO: translation
		//waitMsg: 'Save settings...'
	});

};

/**
 * function is defined in metadata -> see listview
 * Ext will automatically look in the Ext.util.Format namespace
 * when specifying a string for a renderer
 */
Ext.util.Format.CellPreRenderer = function (value, p, records, rowIndex, colIndex, store) {

		// Define Configuration
	var Css		= '';
	var qTitle	= records.data.LabelName;
	var qTip	= value;

		// Set Stati From Above To Css & Quicktip
	p.css = Css;
	p.attr = 'ext:qtip="' + qTip + '" ext:qtitle="' + qTitle + '"';

	return value;

};