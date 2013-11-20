Ext.define('icc.controller.idatabase.Project', {
	extend : 'Ext.app.Controller',
    models : ['idatabase.Project','idatabase.Collection'],
    stores : ['idatabase.Project','idatabase.Collection','idatabase.Collection.Type'],
    views : ['idatabase.Project.Grid','idatabase.Project.Add','idatabase.Project.Edit','idatabase.Project.TabPanel','idatabase.Collection.Main'],
	controllerName : 'idatabaseProject',
	actions : {
		add : '/idatabase/project/add',
		edit : '/idatabase/project/edit',
		remove : '/idatabase/project/remove',
		save : '/idatabase/project/save'
	},
	refs : [{
        ref: 'tabPanel',
        selector: 'idatabaseProjectTabPanel'
    }],
	init : function() {
    	var me = this;
    	var controllerName = me.controllerName;
    	
    	if(controllerName=='') {
    		Ext.Msg.alert('成功提示', '请设定controllerName');
    		return false;
    	}
    	
    	me.addRef([{
            ref: 'list',
            selector: me.controllerName+'Grid'
        },{
            ref: 'add',
            selector: me.controllerName+'Add'
        },{
            ref: 'edit',
            selector: me.controllerName+'Edit'
        }]);

    	var listeners = {};
    	
    	listeners[controllerName+'Add button[action=submit]'] = {
			click : function(button) {
                var store	= me.getList().store;
                var form = button.up('form').getForm();
                if (form.isValid()) {
                    form.submit({
                        success : function(form, action) {
                            Ext.Msg.alert('成功提示', action.result.msg);
                            form.reset();
                            store.load();
                        },
                        failure : function(form, action) {
                            Ext.Msg.alert('失败提示', action.result.msg);
                        }
                    });
                }
                else {
                	Ext.Msg.alert('失败提示', '表单验证失败，请确认你填写的表单符合要求');
                }
            }	
    	};
    	
    	listeners[controllerName+'Edit button[action=submit]'] = {
			click : function(button) {
                var store	= me.getList().store;
                var form = button.up('form').getForm();
                if (form.isValid()) {
                    form.submit({
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
    	
    	listeners[controllerName+'Grid button[action=add]'] = {
			click : function() {
                var win = Ext.widget(controllerName+'Add');
                win.show();
            }
    	};
    	
    	listeners[controllerName+'Grid button[action=edit]'] = {
			click : function(button) {
                var grid	= button.up('gridpanel');
                var selections = grid.getSelectionModel().getSelection();
                if (selections.length > 0) {
                    var win = Ext.widget(controllerName+'Edit');
                    var form = win.down('form').getForm();
                    form.loadRecord(selections[0]);
                    win.show();
                }
                else {
                	Ext.Msg.alert('提示信息', '请选择你要编辑的项');
                }
            }
    	};
    	
    	listeners[controllerName+'Grid button[action=save]'] = {
			click : function(button) {
                var records = me.getList().store.getUpdatedRecords();
                var recordsNumber = records.length;
                if(recordsNumber==0) {
                    Ext.Msg.alert('提示信息', '很遗憾，未发现任何被修改的信息需要保存');
                }
                var updateList = [];
                for(var i=0;i<recordsNumber;i++) {
                    record = records[i];
                    updateList.push(record.data);
                }

                Ext.Ajax.request({
                    url: me.actions.save,
                    params: {
                        updateInfos: Ext.encode(updateList)
                    },
                    scope:me,
                    success: function(response){
                        var text = response.responseText;
                        var json = Ext.decode(text);
                        Ext.Msg.alert('提示信息', json.msg);
                        if (json.success) {
                            me.getList().store.load();
                        }
                    }
                });

            }
    	};
    	
    	listeners[controllerName+'Grid button[action=remove]'] = {
			click : function(button) {
                var grid	= button.up('gridpanel');
                var selections = grid.getSelectionModel().getSelection();
                if (selections.length > 0) {
                    Ext.Msg.confirm('提示信息','请确认是否要删除您选择的信息?',function(btn){
                        if (btn == 'yes') {
                            var _id = [];
                            for(var i=0;i<selections.length;i++) {
                                selection = selections[i];
                                grid.store.remove(selection);
                                _id.push(selection.get('_id'));
                            }

                            Ext.Ajax.request({
                                url : me.actions.remove,
                                params : {
                                	_id : Ext.encode(_id)
                                },
                                scope:me,
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
                    },me);
                }
                else {
                    Ext.Msg.alert('提示信息', '请选择您要删除的项');
                }
            }
    	};
	
    	listeners['idatabaseProjectGrid'] = {
			selectionchange : function(selectionModel,selected,eOpts) {

				if(selected.length > 1) {
					Ext.Msg.alert('提示信息', '请勿选择多项');
					return false;
				}
	
				var record = selected[0];
				if(record) {
					var id = record.get('_id');
					var name = record.get('name');
					var panel = this.getTabPanel().getComponent(id);
					if (panel == null) {

						panel = Ext.widget('idatabaseCollectionMain', {
							id : id,
							title : name,
							project_id : id
						});
						this.getTabPanel().add(panel);
					}
					this.getTabPanel().setActiveTab(id);
				}
			}
		};
    	
    	me.control(listeners);
    }
});