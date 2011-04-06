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

Ext.ns('TYPO3.Snowbabel', 'TYPO3.Snowbabel.ExtDirect');

TYPO3.Snowbabel.ExtensionMenu = Ext.extend(Ext.Panel , {
	border: false,
	margin: {
		top: 0,
		right: 0,
		left: 0
	},

	initComponent:function() {

		var ExtensionMenuStore = new Ext.data.DirectStore({
			directFn: TYPO3.Snowbabel.ExtDirect.getExtensionMenu,
			paramsAsHash: true,
			root: '',
			sortInfo: {
				field: 'ExtensionTitle',
				direction: 'ASC'
			},
			fields: [
				'ExtensionKey',
				'ExtensionTitle',
				'ExtensionDescription',
				'ExtensionCategory',
				'ExtensionIcon',
				'ExtensionCss'
			]
		});

			// template
		var ExtensionMenuTpl = new Ext.XTemplate(
			'<ul id="ExtensionMenu" class="snowbabel-menu">',
			'<tpl for=".">',
				'<li ext:qtip="<b>' + TYPO3.lang.translation_extensionmenu_QtipKey + ':</b> {ExtensionKey}<br><b>' + TYPO3.lang.translation_extensionmenu_QtipDescription + ':</b> {ExtensionDescription}<br><b>' + TYPO3.lang.translation_extensionmenu_QtipCategory + ':</b> {ExtensionCategory}" class="snowbabel-menu-item {ExtensionCss}" style="background-image: url({ExtensionIcon});">',
					'{ExtensionTitle}',
				'</li>',
			'</tpl>',
			'</ul>',
			'<div class="x-clear"></div>'
		);

			// dataview
		var ExtensionMenuView = new Ext.DataView({
			id: 'snowbabel-extension-menu-view',
			autoScroll: true,
			singleSelect: true,
			overClass:'snowbabel-menu-item-over',
			selectedClass: 'snowbabel-menu-item-selected',
			itemSelector:'li.snowbabel-menu-item',
			emptyText: '###detailView.NoRecordsAvailable###',
			store: ExtensionMenuStore,
			tpl: ExtensionMenuTpl,
			loadingText: TYPO3.lang.translation_extensionmenu_LoadingText,
			listeners: ({
				'click': function(dataView, index, node, e) {

					var record = dataView.getRecord(node);

						// set params for view
					LoadParams = new Array();
					LoadParams['ExtensionKey'] = record.data.ExtensionKey;
					LoadParams['ActionKey'] = '';
                    LoadParams['LanguageKey'] = '';
					LoadParams['SearchGlobal'] = false;
					LoadParams['SearchString'] = '';

						// load view
					TYPO3.Snowbabel.Generals.LoadListView(LoadParams);
				},

				'contextmenu' : function(dataView, index, node, e) {

					var record = dataView.getRecord(node);

					LoadParams = new Array();
					LoadParams['ExtensionKey'] = record.data.ExtensionKey;
					LoadParams['SearchGlobal'] = false;
					LoadParams['SearchString'] = '';

					if(!this.menu){
						this.menu = new Ext.menu.Menu({
						  items: [{
							id: 'sync',
							iconCls:'sync-icon',
							text: TYPO3.lang.translation_extensionmenu_contextMenuSync,
							scope: this,
							handler: function() {
												Ext.Msg.confirm(TYPO3.lang.translation_extensionmenu_msgTitleSync, TYPO3.lang.translation_extensionmenu_msgTextSync, function(btn){
													if (btn == 'yes') {
															// load view
														LoadParams['ActionKey'] = 'ExtensionSync';
														TYPO3.Snowbabel.Generals.LoadListView(LoadParams);
													}
												});
							}
						},{
							id: 'saveToOriginal',
							iconCls:'saveToOriginal-icon',
							text: TYPO3.lang.translation_extensionmenu_contextMenuSaveToOriginal,
							scope: this,
							handler: function() {
												Ext.Msg.confirm(TYPO3.lang.translation_extensionmenu_msgTitleSaveToOriginal, TYPO3.lang.translation_extensionmenu_msgTextSaveToOriginal, function(btn){
													if (btn == 'yes') {
															// load view
														LoadParams['ActionKey'] = 'ExtensionToOriginal';
														TYPO3.Snowbabel.Generals.LoadListView(LoadParams);
													}
												});
							}
						},{
							id: 'backupAll',
							iconCls:'backup-icon',
										text: TYPO3.lang.translation_extensionmenu_contextMenuBackupAll,
										scope: this,
										handler: function() {
											Ext.Msg.confirm(TYPO3.lang.translation_extensionmenu_msgTitleBackupAll, TYPO3.lang.translation_extensionmenu_msgTextBackupAll, function(btn){
												if (btn == 'yes') {
															// load view
														LoadParams['ActionKey'] = 'ExtensionBackupAll';
														TYPO3.Snowbabel.Generals.LoadListView(LoadParams);
												}
											});
										}
						},{
							id: 'recoverAll',
							iconCls:'recover-icon',
										text: TYPO3.lang.translation_extensionmenu_contextMenuRecoverAll,
										scope: this,
										handler: function() {
											Ext.Msg.confirm(TYPO3.lang.translation_extensionmenu_msgTitleRecoverAll, TYPO3.lang.translation_extensionmenu_msgTextRecoverAll, function(btn){
												if (btn == 'yes') {
															// load view
														LoadParams['ActionKey'] = 'ExtensionRestoreAll';
														TYPO3.Snowbabel.Generals.LoadListView(LoadParams);
												}
											});
										}
						}]
					  });
					}

					e.stopEvent();
					this.menu.showAt(e.getXY());
			}


			})
		});

			//config
		var config = {
			items: ExtensionMenuView
		};

		Ext.apply(this, Ext.apply(this.initialConfig, config));

			//load store
		ExtensionMenuStore.load();

		TYPO3.Snowbabel.ExtensionMenu.superclass.initComponent.apply(this, arguments);
	}

});

Ext.reg('TYPO3.Snowbabel.ExtensionMenu', TYPO3.Snowbabel.ExtensionMenu);