Ext.define('icc.controller.idatabase.Data', {
	extend : 'Ext.app.Controller',
	models : [],
	stores : [],
	views : [ 'idatabase.Data.Main', 'idatabase.Data.Grid',
			'idatabase.Data.Search', 'idatabase.Data.Add','idatabase.Data.Password',
			'idatabase.Data.Edit', 'idatabase.Data.Field.2dfield' ],
	controllerName : 'idatabaseData',
	plugin : false,
	plugin_id : '',
	actions : {
		add : '/idatabase/data/add',
		edit : '/idatabase/data/edit',
		remove : '/idatabase/data/remove',
		save : '/idatabase/data/save'
	},
	refs : [ {
		ref : 'projectTabPanel',
		selector : 'idatabaseProjectTabPanel'
	} ],
	activeDataGrid : function() {
		return this.getProjectTabPanel().getActiveTab().down(
				'idatabaseCollectionTabPanel').getActiveTab().down(
				'idatabaseDataGrid') ? this.getProjectTabPanel().getActiveTab().down(
				'idatabaseCollectionTabPanel').getActiveTab().down(
				'idatabaseDataGrid') : this.getProjectTabPanel().getActiveTab().down(
				'idatabaseCollectionTabPanel').getActiveTab().down(
				'idatabaseDataTreeGrid');
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
				var grid = me.activeDataGrid();
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
				var grid = me.activeDataGrid();
				var store = grid.store;
				var form = button.up('form').getForm();
				if (form.isValid()) {

					var htmleditors = button.up('form').query('htmleditor');
					if(Ext.isArray(htmleditors) && htmleditors.length>0) {
						Ext.Array.forEach(htmleditors,function(item,index,allitems){
							item.toggleSourceEdit(false);
						});
					}					
					
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

		listeners['idatabaseDataGrid button[action=add],idatabaseDataTreeGrid button[action=add]'] = {
			click : function(button) {
				var grid = button.up('gridpanel') ? button.up('gridpanel') : button.up('treepanel');
				var win = Ext.widget(controllerName + 'Add', {
					project_id : grid.project_id,
					collection_id : grid.collection_id,
					addOrEditFields : grid.addOrEditFields
				});
				win.show();
			}
		};

		listeners['idatabaseDataGrid button[action=edit],idatabaseDataTreeGrid button[action=edit]'] = {
			click : function(button) {
				var grid = button.up('gridpanel') ? button.up('gridpanel') : button.up('treepanel');
				var selections = grid.getSelectionModel().getSelection();
				if (selections.length > 0) {
					var win = Ext.widget(controllerName + 'Edit', {
						project_id : grid.project_id,
						collection_id : grid.collection_id,
						addOrEditFields : grid.addOrEditFields
					});
					var form = win.down('form').getForm();
					form.loadRecord(selections[0]);
					Ext.Array.forEach(grid.addOrEditFields,function(item,index){
						if(item.xtype=='2dfield') {
							var field = item.name;
							var tmp = selections[0].get(field).split(',');
							form.findField(field+'[lng]').setValue(tmp[0]);
							form.findField(field+'[lat]').setValue(tmp[1]);
							return true;
						}
					});
					win.show();
				} else {
					Ext.Msg.alert('提示信息', '请选择你要编辑的项');
				}
			}
		};

		listeners['idatabaseDataGrid button[action=save],idatabaseDataTreeGrid button[action=save]'] = {
			click : function(button) {
				var grid = button.up('gridpanel') ? button.up('gridpanel') : button.up('treepanel');
				var store = grid.store;
				var records = grid.store.getUpdatedRecords();
				var recordsNumber = records.length;
				if (recordsNumber == 0) {
					Ext.Msg.alert('提示信息', '很遗憾，未发现任何被修改的信息需要保存');
					return false;
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
						project_id : grid.project_id,
						collection_id : grid.collection_id
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

		listeners['idatabaseDataGrid button[action=remove],idatabaseDataTreeGrid button[action=remove]'] = {
			click : function(button) {
				var grid = button.up('gridpanel') ? button.up('gridpanel') : button.up('treepanel');
				var selections = grid.getSelectionModel().getSelection();
				if (selections.length > 0) {
					Ext.Msg.confirm('提示信息', '请确认是否要删除您选择的信息?', function(btn) {
						if (btn == 'yes') {
							var _id = [];
							for ( var i = 0; i < selections.length; i++) {
								selection = selections[i];
								_id.push(selection.get('_id'));
							}

							Ext.Ajax.request({
								url : me.actions.remove,
								params : {
									_id : Ext.encode(_id),
									project_id : grid.project_id,
									collection_id : grid.collection_id
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
		
		listeners['idatabaseDataGrid button[action=drop],idatabaseDataTreeGrid button[action=drop]'] = {
			click : function(button) {
				var grid = button.up('gridpanel') ? button.up('gridpanel') : button.up('treepanel');
				var selections = grid.getSelectionModel().getSelection();
				Ext.Msg.confirm('安全警告', '您当前执行的是清空操作，清空后数据将无法找回，请确认您是否要清空全部数据?', function(btn) {
					if (btn == 'yes') {
						var win = Ext.widget(controllerName + 'Password', {
							project_id : grid.project_id,
							collection_id : grid.collection_id,
							height: 240,
							width : 320
						});
						
						win.show();
					}
				}, me);
			}
		};
		
		listeners['idatabaseDataPassword button[action=submit]'] = {
			click : function(button) {
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
		
		listeners['idatabaseDataSearch button[action=search],button[action=excel]'] = {
			click : function(button) {
				var form = button.up('form').getForm();
				if (form.isValid()) {
					var extraParams = form.getValues(false,true);
					var store = me.activeDataGrid().store;
					form.getFields().each(function(items,index){
						if(items.xtype!='hiddenfield')
							delete store.proxy.extraParams[items.name];
					});			
					
					store.proxy.extraParams.action = button.action;
					store.proxy.extraParams = Ext.Object.merge(store.proxy.extraParams,extraParams);
					
					if(button.action=='excel') {
						Ext.Msg.confirm('系统提示', '导出数据有可能需要较长的时间，请点击“导出”按钮后，耐心等待，两次操作间隔需大于30秒！', function(btn) {
							if (btn == 'yes') {
								button.setDisabled(true);
								setTimeout(function(){
									button.setDisabled(false);
								}, 30000);
								window.location.href = '/idatabase/data/index?'+Ext.Object.toQueryString(store.proxy.extraParams);
							}
						}, me);	
					}
					else {
						button.setDisabled(true);
						setTimeout(function(){
							button.setDisabled(false);
						}, 3000);
					}
					store.load();
				}
			}
		}

		me.control(listeners);
	}
});