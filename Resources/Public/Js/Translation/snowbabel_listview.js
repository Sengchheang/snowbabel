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
 * Extjs for the 'Snowbabel' extension.
 *
 * @author	Daniel Alder <dalder@snowflake.ch>
 * @package	TYPO3
 * @subpackage	tx_snowbabel
 */

Ext.ns('TYPO3.Snowbabel', 'TYPO3.Snowbabel.Generals', 'TYPO3.Snowbabel.ExtDirect');

TYPO3.Snowbabel.ListView = Ext.extend(Ext.Panel , {

	border: false,
	frame: true,

	initComponent:function() {

			// store
		var ListViewStore = new Ext.data.DirectStore({
			storeId: 'ListViewStore',
			directFn: TYPO3.Snowbabel.ExtDirect.getListView,
			paramsAsHash: true,
			autoSave: false,
			root: '',
			fields: [],
			baseParams: TYPO3.Snowbabel.Generals.ListViewBaseParams,
			listeners: ({
				'metachange': function(Store) {

						// Get Total Results From Request
					var TotalResults = Store.reader.jsonData.results;

					if(TotalResults == 0) {

							// Clear Listview
						this.removeAll();

							// Clear Pagebar -> TODO: remove "afterPageText"
						var PagerBar = this.getBottomToolbar();
						PagerBar.setDisabled(true);
					}
				}
			})
		});

		var ListViewGrid = new Ext.grid.EditorGridPanel ({
			id: 'ListViewGrid',
			clicksToEdit: 1,
			store: ListViewStore,
			enableHdMenu: false,
			loadMask: true,
			border: false,
			columns:[],
			listeners: ({

				'afteredit': function(e) {

					var ActionParams	= new Array();

					ActionParams['ActionKey']		= 'ListView';
					ActionParams['LabelValue']		= e.record.data[e.field];
					ActionParams['LabelName']		= e.record.data.LabelName;
					ActionParams['LabelPath']		= e.record.data.LabelPath;
					ActionParams['LabelLanguage']	= e.record.data[e.field + 'Language'];
					ActionParams['LabelLocation']	= e.record.data.LabelLocation;
					ActionParams['LabelExtension']	= e.record.data.LabelExtension;
					ActionParams['Record']	= e.record;

					TYPO3.Snowbabel.Generals.ActionController(ActionParams);

				}
			}),
			tbar: [
				TYPO3.lang.translation_listview_GridSearch,
				' ',
				new Ext.ux.form.SearchField({
					id: 'SearchField',
					store: ListViewStore,
					width: 320,
					paramName: 'SearchString',
					limit: TYPO3.Snowbabel.Generals.ListViewLimit
				}),
				'-',
					{   // TODO: Check
					id: 'SearchToggle',
					text: TYPO3.lang.translation_listview_GridSearchToggle,
					enableToggle: true,
					toggleHandler: function onSearchToggle(item, pressed) {
						ListViewStore.setBaseParam('SearchGlobal', pressed);
					},
					pressed: false,
					disabled: true
				}
			],
			viewConfig: {
				onDataChange: function(store){
					this.cm.setConfig(store.reader.jsonData.columns);
					this.syncFocusEl(0);
				},
				forceFit: true,
				emptyText: '<p id="noRecords">' + TYPO3.lang.translation_listview_GroupingViewEmptyText + '</p>'
			},
	        bbar: new Ext.PagingToolbar({
	            pageSize: TYPO3.Snowbabel.Generals.ListViewLimit,
	            store: ListViewStore,
	            displayInfo: true
	        })

		});

			//config
		var config = {
			itemId: 'snowbabel-list-view',
			items: ListViewGrid
		};

		Ext.apply(this, Ext.apply(this.initialConfig, config));

		TYPO3.Snowbabel.ListView.superclass.initComponent.apply(this, arguments);

	}
});

Ext.reg('TYPO3.Snowbabel.ListView', TYPO3.Snowbabel.ListView);