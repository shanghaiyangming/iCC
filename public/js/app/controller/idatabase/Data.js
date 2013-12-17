Ext.define('icc.controller.idatabase.Data', {
	extend : 'Ext.app.Controller',
	models : [],
	stores : [],
	views : [ 'idatabase.Data.Main', 'idatabase.Data.Grid',
			'idatabase.Data.Search', 'idatabase.Data.Add',
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
				'idatabaseDataGrid');
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

		listeners[controllerName + 'Grid button[action=add]'] = {
			click : function(button) {
				var grid = button.up('gridpanel');
				var win = Ext.widget(controllerName + 'Add', {
					project_id : grid.project_id,
					collection_id : grid.collection_id,
					addOrEditFields : grid.addOrEditFields
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
						collection_id : grid.collection_id,
						addOrEditFields : grid.addOrEditFields
					});
					var form = win.down('form').getForm();
					form.loadRecord(selections[0]);
					Ext.Array.forEach(grid.addOrEditFields,function(item,index){
						
					});
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
				console.info(records,records.length);
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

		me.control(listeners);
	}
});