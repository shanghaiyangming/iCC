Ext.define('icc.controller.idatabase.Collection', {
	extend : 'Ext.app.Controller',
	models : [ 'idatabase.Collection','idatabase.Structure' ],
	stores : [ 'idatabase.Collection', 'idatabase.Collection.Type','idatabase.Structure'],
	views : [ 'idatabase.Collection.Grid', 'idatabase.Collection.Add',
			'idatabase.Collection.Edit', 'idatabase.Collection.TabPanel',
			'idatabase.Collection.TypeCombobox' ],
	controllerName : 'idatabaseCollection',
	plugin : false,
	plugin_id : '',
	actions : {
		add : '/idatabase/collection/add',
		edit : '/idatabase/collection/edit',
		remove : '/idatabase/collection/remove',
		save : '/idatabase/collection/save'
	},
	refs : [ {
		ref : 'projectTabPanel',
		selector : 'idatabaseProjectTabPanel'
	} ],
	collectionTabPanel : function() {
		return this.getProjectTabPanel().getActiveTab().down('idatabaseCollectionTabPanel');
	},
	getExpandedAccordion: function() {
		return this.getProjectTabPanel().getActiveTab().down('idatabaseCollectionAccordion').child("[collapsed=false]");
	},
	init : function() {
		var me = this;
		var controllerName = me.controllerName;

		if (controllerName == '') {
			Ext.Msg.alert('成功提示', '请设定controllerName');
			return false;
		}

		me.addRef([ {
			ref : 'main',
			selector : me.controllerName + 'Main'
		}, {
			ref : 'list',
			selector : me.controllerName + 'Grid'
		}, {
			ref : 'add',
			selector : me.controllerName + 'Add'
		}, {
			ref : 'edit',
			selector : me.controllerName + 'Edit'
		} ]);

		var listeners = {};

		listeners[controllerName + 'Add button[action=submit]'] = {
			click : function(button) {
				var grid = me.getExpandedAccordion();
				var store = grid.store;
				var form = button.up('form').getForm();
				if (form.isValid()) {
					form.submit({
						waitTitle : '系统提示',
						waitMsg : '系统处理中，请稍后……',
						success : function(form, action) {
							Ext.Msg.alert('成功提示', action.result.msg);
							form.reset();
							store.load();
						},
						failure : function(form, action) {
							Ext.Msg.alert('失败提示', action.result.msg);
						}
					});
				} else {
					Ext.Msg.alert('失败提示', '表单验证失败，请确认你填写的表单符合要求');
				}
			}
		};

		listeners[controllerName + 'Edit button[action=submit]'] = {
			click : function(button) {
				var grid = me.getExpandedAccordion();
				var store = grid.store;
				var form = button.up('form').getForm();
				if (form.isValid()) {
					form.submit({
						waitTitle : '系统提示',
						waitMsg : '系统处理中，请稍后……',
						success : function(form, action) {
							Ext.Msg.alert('成功提示', action.result.msg);
							store.load();
						},
						failure : function(form, action) {
							Ext.Msg.alert('失败提示', action.result.msg);
						}
					});
				}
			}
		};

		listeners[controllerName + 'Grid button[action=add]'] = {
			click : function(button) {
				var grid = button.up('gridpanel');
				console.info(grid.plugin,grid.plugin_id);
				var win = Ext.widget(controllerName + 'Add', {
					project_id : grid.project_id,
					plugin : grid.plugin,
					plugin_id : grid.plugin_id,
					orderBy : grid.store.getTotalCount()
				});
				win.show();
			}
		};

		listeners[controllerName + 'Grid button[action=edit]'] = {
			click : function(button) {
				var grid = button.up('gridpanel');
				var selections = grid.getSelectionModel().getSelection();
				if (selections.length > 0) {
					var win = Ext.widget(controllerName + 'Edit', {
						project_id : grid.project_id,
						plugin : grid.plugin,
						plugin_id : grid.plugin_id
					});
					var form = win.down('form').getForm();
					form.loadRecord(selections[0]);
					win.show();
				} else {
					Ext.Msg.alert('提示信息', '请选择你要编辑的项');
				}
			}
		};

		listeners[controllerName + 'Grid button[action=save]'] = {
			click : function(button) {
				var grid = button.up('gridpanel');
				var store = grid.store;
				var records = grid.store.getUpdatedRecords();
				var recordsNumber = records.length;
				if (recordsNumber == 0) {
					Ext.Msg.alert('提示信息', '很遗憾，未发现任何被修改的信息需要保存');
				}
				var updateList = [];
				for ( var i = 0; i < recordsNumber; i++) {
					record = records[i];
					updateList.push(record.data);
				}

				Ext.Ajax.request({
					url : me.actions.save,
					params : {
						updateInfos : Ext.encode(updateList),
						project_id : grid.project_id
					},
					scope : me,
					success : function(response) {
						var text = response.responseText;
						var json = Ext.decode(text);
						Ext.Msg.alert('提示信息', json.msg);
						if (json.success) {
							store.load();
						}
					}
				});

			}
		};

		listeners[controllerName + 'Grid button[action=remove]'] = {
			click : function(button) {
				var grid = button.up('gridpanel');
				var selections = grid.getSelectionModel().getSelection();
				if (selections.length > 0) {
					Ext.Msg.confirm('提示信息', '请确认是否要删除您选择的信息?', function(btn) {
						if (btn == 'yes') {
							var _id = [];
							for ( var i = 0; i < selections.length; i++) {
								selection = selections[i];
								grid.store.remove(selection);
								_id.push(selection.get('_id'));
							}

							Ext.Ajax.request({
								url : me.actions.remove,
								params : {
									_id : Ext.encode(_id),
									project_id : grid.project_id,
									plugin : grid.plugin,
									plugin_id : grid.plugin_id
								},
								scope : me,
								success : function(response) {
									var text = response.responseText;
									var json = Ext.decode(text);
									Ext.Msg.alert('提示信息', json.msg);
									if (json.success) {
										grid.store.load();
									}
								}
							});
						}
					}, me);
				} else {
					Ext.Msg.alert('提示信息', '请选择您要删除的项');
				}
			}
		};
		
		listeners[controllerName + 'Grid'] = {
			selectionchange : function(selectionModel, selected, eOpts) {
				var grid = this.getExpandedAccordion();				
				if (selected.length > 1) {
					Ext.Msg.alert('提示信息', '请勿选择多项');
					return false;
				}

				var record = selected[0];
				if (record) {
					this.buildDataPanel(grid.project_id,record.get('_id'),record.get('name'),this.collectionTabPanel());
				}
			}
		};

		listeners[controllerName + 'Grid button[action=structure]'] = {
			click : function(button) {
				var grid = button.up('gridpanel');
				var selections = grid.getSelectionModel().getSelection();
				if (selections.length == 1) {
					var record = selections[0];
					var win = Ext.widget('idatabaseStructureWindow', {
						project_id : grid.project_id,
						collection_id : record.get('_id'),
						plugin : me.plugin,
						plugin_id : me.plugin_id
					});
					win.show();
				}
				else {
					Ext.Msg.alert('提示信息', '请选择一项您要编辑的集合');
				}
			}
		};
		
		listeners[controllerName + 'Grid button[action=orderBy]'] = {
			click : function(button) {
				var grid = button.up('gridpanel');
				var selections = grid.getSelectionModel().getSelection();
				if (selections.length == 1) {
					var record = selections[0];
					var win = Ext.widget('idatabaseCollectionOrderWindow', {
						project_id : grid.project_id,
						collection_id : record.get('_id')
					});
					win.show();
				}
				else {
					Ext.Msg.alert('提示信息', '请选择一项您要编辑的集合');
				}
			}
		};

		listeners[controllerName + 'Grid button[action=index]'] = {
			click : function(button) {
				var grid = button.up('gridpanel');
				var win = Ext.widget('idatabaseIndexWindow', {
					project_id : grid.project_id,
					plugin : me.plugin,
					plugin_id : me.plugin_id
				});
				win.show();
			}
		};

		listeners[controllerName + 'Grid button[action=static]'] = {
			click : function(button) {
				var grid = button.up('gridpanel');
				var win = Ext.widget('idatabaseStaticWindow', {
					project_id : grid.project_id,
					plugin : me.plugin,
					plugin_id : me.plugin_id
				});
				win.show();
			}
		};
		me.control(listeners);
	},
	reBuildDataPanel:function(collection_id) {
   		var tabpanel = this.collectionTabPanel();
		var project_id = tabpanel.project_id;
   		var panel = tabpanel.getComponent(collection_id);
   		var collection_name = panel.collection_name;
   		panel.close();
   		this.buildDataPanel(project_id,collection_id,collection_name,tabpanel);
   	},
   	buildDataPanel:function(project_id,collection_id,collection_name,tabpanel) {
   		var me = this;
		var panel = tabpanel.getComponent(collection_id);
		if (panel == null) {
			// model的fields动态创建
			var modelFields = [];
			var searchFields = [ {
				xtype : 'hiddenfield',
				name  : 'project_id',
				value : project_id,
				allowBlank : false
			}, {
				xtype : 'hiddenfield',
				name : 'collection_id',
				value : collection_id,
				allowBlank : false
			} ];
			
			var gridColumns =  [ {
				text : "_id",
				sortable : false,
				dataIndex : '_id',
				flex:1,
				editor : 'textfield',
				hidden:true
			}, {
				xtype: 'datecolumn',
				format : 'Y-m-d H:i:s',
				text : "创建时间",
				sortable : false,
				flex:1,
				dataIndex : '__CREATE_TIME__'
			}, {
				xtype: 'datecolumn',
				format : 'Y-m-d H:i:s',
				text : "修改时间",
				sortable : false,
				flex:1,
				dataIndex : '__MODIFY_TIME__',
				hidden:true
			}]; 
			
			var structureStore = Ext.create('icc.store.idatabase.Structure');
			structureStore['proxy']['extraParams']['project_id'] = project_id;
			structureStore['proxy']['extraParams']['collection_id'] = collection_id;
			
			structureStore.load(function(records, operation, success) {
				// 存储下拉菜单模式的列
				var gridComboboxColumns = [];
				var addOrEditFields = [];
				
				Ext.Array.forEach(records,function(record) {
					//创建添加和编辑的field表单开始
					var addOrEditField = {
						xtype      : record.get('type'),
						fieldLabel : record.get('label'),
						name       : record.get('field'),
						allowBlank : !record.get('required')
					};
					
					switch (record.get('type')) {
						case 'filefield':
//							addOrEditField['xtype'] = 'filefield';
//							addOrEditField['name'] = record.get('field');
//							addOrEditField['fieldLabel'] = record.get('label');
//							addOrEditField['labelWidth'] = 100;
//							addOrEditField['msgTarget']  = 'side';
//							addOrEditField['anchor'] = '100%';
//							addOrEditField['allowBlank'] = true;
//							addOrEditField['buttonText'] = '浏览本地文件';

							addOrEditField = {
								xtype: 'filefield',
								name: record.get('field'),
								fieldLabel: record.get('label'),
								labelWidth: 100,
								msgTarget: 'side',
								allowBlank: true,
								anchor: '100%',
								buttonText: '浏览本地文件'
							};
							break;
						case '2dfield':
							addOrEditField.title = record.get('label');
							addOrEditField.fieldName = record.get('field');
							break;
						case 'datefield':
							addOrEditField.format = 'Y-m-d H:i:s';
							break;
						case 'numberfield':
							addOrEditField.decimalPrecision = 8;
							break;
						case 'htmleditor':
							addOrEditField.height = 300;
							break; 
					};
					
					var rshCollection = record.get('rshCollection');
					if(rshCollection != '' && rshCollection.length == 24) {
						var rshCollectionModel = 'rshCollectionModel'+rshCollection;
						var convert = function(value) {
							if(Ext.isObject(value)) {
								if(value['$id']!=undefined) {
									return value['$id'];
								}
								else if(value['sec']!=undefined) {
									var date = new Date();
									date.setTime(value['sec'] * 1000);
									return date;
								}
							}
							else if(Ext.isArray(value)) {
								return value.join(',');
							}
							return value;
						};
						
						Ext.define(rshCollectionModel,{
							extend:'icc.model.common.Model',
				            fields: [
				                {
				                	name : record.get('rshCollectionDisplayField'),
									convert : convert
								}, {
									name : record.get('rshCollectionValueField'),
									convert : convert
								}
				            ]
				        });
						
						var comboboxStore = Ext.create('Ext.data.Store',{
							model: rshCollectionModel,
							autoLoad: false,
							pageSize: 20,
							proxy : {
								type : 'ajax',
								url : '/idatabase/data/index',
								extraParams : {
									project_id : project_id,
									collection_id : record.get('rshCollection')
								},
								reader : {
									type : 'json',
									root : 'result',
									totalProperty : 'total'
								}
							}
						});
						
						addOrEditField.xtype          = 'combobox';
						addOrEditField.name           = record.get('field');
						addOrEditField.fieldLabel     = record.get('label');
						addOrEditField.store          = comboboxStore;
						addOrEditField.queryMode      = 'remote';
						addOrEditField.forceSelection = true;
						addOrEditField.editable       = true;
						addOrEditField.minChars       = 1;
						addOrEditField.pageSize       = 20;
						addOrEditField.queryParam     = 'search';
						addOrEditField.typeAhead      = true;
						addOrEditField.valueField     = record.get('rshCollectionValueField');
						addOrEditField.displayField   = record.get('rshCollectionDisplayField');
					}
					addOrEditFields.push(addOrEditField);
					//创建添加和编辑的field表单结束
					
					// 创建model的fields开始
					var field = {
						name : record.get('field'),
						type : 'string'
					};
					switch (record.get('type')) {
						case '2dfield':
							field.type = 'string';
							field.convert = function(value, record) {
								if(Ext.isArray(value)) {
									return value.join(',');
								}
								return value;
							};
							break;
						case 'datefield':
							field.type = 'string';
							field.convert = function(value, record) {
								if(Ext.isObject(value) && value['sec']!=undefined) {
									var date = new Date();
									date.setTime(value.sec * 1000);
									return date;
								}
								else {
									return value;
								}
							};
							break;
						case 'numberfield':
							field.type = 'float';
							break;
					}
					modelFields.push(field);
					
					// 绘制grid的column信息
					if (record.get('main')) {
						var column = {
							text : record.get('label'),
							dataIndex : record.get('field'),
							flex : 1
						};
						switch (record.get('type')) {
							case '2dfield':
								column.align = 'center';
								break;
							case 'datefield':
								column.xtype = 'datecolumn';
								column.format = 'Y-m-d H:i:s';
								column.align = 'center';
								column.field = {
									xtype : 'datefield', 
									allowBlank : !record.get('required'),
									format : 'Y-m-d H:i:s'
								};
								break;
							case 'numberfield':
								column.format = '0,000.00';
								column.align = 'right';
								column.field = {
									xtype : 'numberfield', 
									allowBlank : !record.get('required')
								};
								break;
							case 'filefield':
								if(record.get('showImage')!=undefined && record.get('showImage')==true) {
									column.xtype = 'templatecolumn';
									column.tpl = '<a href="{'+record.get('field')+'}" target="_blank"><img src="{'+record.get('field')+'}?size=100x100" border="0" height="100" /></a>';
								}
								break;
							default:
								column.field = {
									xtype : 'textfield', 
									allowBlank : !record.get('required')
								};
								break;
						}
						
						// 存在关联集合数据，则直接采用combobox的方式进行显示
						if(rshCollection != '' && rshCollection.length == 24) {
							column.field = {
								xtype : 'combobox',
								typeAhead : true,
								store : comboboxStore,
								allowBlank : !record.get('required'),
								displayField : record.get('rshCollectionDisplayField'),
								valueField : record.get('rshCollectionValueField')
							};
							
							column.renderer = function(value) {
								var rec = comboboxStore.findRecord(record.get('rshCollectionValueField'), value, 0, false, false, true);
								if (rec != null) {
									return rec.get(record.get('rshCollectionDisplayField'));
								}
								return '';
							};
							
							gridComboboxColumns.push(column);
						}
						
						gridColumns.push(column);
					}
					// 创建model的fields结束
					
					// 创建条件检索form
					if (record.get('searchable') && record.get('type')!='filefield') {						
						
						var rshCollection = record.get('rshCollection');
						
						//$not操作
						var exclusive = {
							fieldLabel : '非',
							name : 'exclusive__' + record.get('field'),
							xtype : 'checkboxfield',
							width : 30
						};
						
						//开启精确匹配
						var exactMatch = {
							fieldLabel : '等于',
							name : 'exactMatch__' + record.get('field'),
							xtype : 'checkboxfield',
							width : 30	
						};
						
						if(rshCollection!='') {						
							var comboboxSearchStore = Ext.create('Ext.data.Store',{
								model: rshCollectionModel,
								autoLoad: false,
								pageSize: 20,
								proxy : {
									type : 'ajax',
									url : '/idatabase/data/index',
									extraParams : {
										project_id : project_id,
										collection_id : record.get('rshCollection')
									},
									reader : {
										type : 'json',
										root : 'result',
										totalProperty : 'total'
									}
								}
							});
							
							comboboxSearchStore.addListener('load', function() {
								var insertRecord = {};
								insertRecord[record.get('rshCollectionDisplayField')] = '无';
								insertRecord[record.get('rshCollectionValueField')]   = '';
								comboboxSearchStore.insert(0, Ext.create(rshCollectionModel, insertRecord));
							});
							
							searchFieldItem = {
								xtype : 'combobox',
								name : record.get('field'),
								fieldLabel : record.get('label'),
								typeAhead : true,
								store : comboboxSearchStore,
								displayField : record.get('rshCollectionDisplayField'),
								valueField : record.get('rshCollectionValueField')
							};
							
							searchField = {
								xtype : 'fieldset',
								layout : 'hbox',
								title : record.get('label'),
								fieldDefaults : {
									labelAlign : 'top',
									labelSeparator : ''
								},
								items : [exclusive, searchFieldItem]
							};
						}
						else if(record.get('type')=='datefield') {
							searchField = {
								xtype : 'fieldset',
								layout : 'hbox',
								title : record.get('label'),
								defaultType : 'datefield',
								fieldDefaults : {
									labelAlign : 'top',
									labelSeparator : '',
									format : 'Y-m-d H:i:s'
								},
								items : [ exclusive, {
									fieldLabel : '开始时间',
									name : record.get('field')+'[start]'
								}, {
									fieldLabel : '截止时间',
									name : record.get('field')+'[end]'
								}]
							};
						}
						else if(record.get('type')=='numberfield') {
							searchField = {
								xtype : 'fieldset',
								layout : 'hbox',
								title : record.get('label'),
								defaultType : 'numberfield',
								fieldDefaults : {
									labelAlign : 'top',
									labelSeparator : ''
								},
								items : [exclusive, {
									fieldLabel : '最小值(>=)',
									name : record.get('field')+'[start]'
								}, {
									fieldLabel : '最大值(<=)',
									name : record.get('field')+'[end]'
								} ]
							};
						}
						else if(record.get('type')=='2dfield') {
							searchField = {
								xtype : 'fieldset',
								layout : 'hbox',
								title : record.get('label'),
								defaultType : 'numberfield',
								fieldDefaults : {
									labelAlign : 'top',
									labelSeparator : ''
								},
								items : [{
									name : record.get('field')+'[lng]',
									fieldLabel : '经度'
								},{
									name : record.get('field')+'[lat]',
									fieldLabel : '维度'
								},{
									name : 'near__'+record.get('field'),
									fieldLabel : '附近范围(km)'
								}]
							};
						}
						else {
							searchField = {
								xtype : 'fieldset',
								layout : 'hbox',
								title : record.get('label'),
								defaultType : 'textfield',
								fieldDefaults : {
									labelAlign : 'top',
									labelSeparator : ''
								},
								items : [ exclusive,exactMatch, {
									name : record.get('field'),
									fieldLabel : record.get('label')
								}]
							};
						}
						
						searchFields.push(searchField);
					}
					// 创建条件检索form结束
				});
				
				// 创建数据的model
				var dataModelName = 'dataModel'+collection_id;
				var dataModel = Ext.define(dataModelName,{
					extend:'icc.model.common.Model',
					fields : modelFields
				});
				
				// 加载数据store
				var dataStore = Ext.create('Ext.data.Store',{
					model : dataModelName,
					autoLoad: false,
					pageSize: 20,
					proxy : {
						type : 'ajax',
						url : '/idatabase/data/index',
						extraParams:{
							project_id : project_id,
							collection_id : collection_id
						},
						reader : {
							type : 'json',
							root : 'result',
							totalProperty : 'total'
						}
					}
				});

				
				panel = Ext.widget('idatabaseDataMain', {
					id : collection_id,
					name : collection_name,
					title : collection_name,
					collection_id:collection_id,
					project_id:project_id,
					gridColumns : gridColumns,
					gridStore : dataStore,
					searchFields : searchFields,
					addOrEditFields : addOrEditFields
				});
				
				panel.on({
					beforerender : function(panel) {
						var grid = panel.down('grid');
						grid.store.on('load', function(store, records, success) {
							if (success) {
								var loop = gridComboboxColumns.length;
								if(loop>0) {
									Ext.Array.forEach(gridComboboxColumns,function(gridComboboxColumn){
										var ids = [];
										for ( var index = 0; index < records.length; index++) {
											ids.push(records[index].get(gridComboboxColumn.dataIndex));
										}
										var store = gridComboboxColumn.field.store;
										store.proxy.extraParams.idbComboboxSelectedValue = ids.join(',');
										store.load(function(){
											loop -= 1;
											if(loop==0) {
												grid.getView().refresh();
											}
										});
									});
								}
								else {
									grid.getView().refresh();
								}
							}
						});
						grid.store.load();
					}
				});
				
				tabpanel.add(panel);
				tabpanel.setActiveTab(collection_id);
			});
		}
		else {
			tabpanel.setActiveTab(collection_id);
		}
   	}
});