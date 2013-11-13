Ext.define('icc.controller.common.GridController', {
    extend : 'Ext.app.Controller',
    models : [],
    stores : [],
    views : [],
    controllerName : '',
    actions:{
    	add : '',
    	edit : '',
    	remove : '',
    	save : ''
    },
    init : function() {
    	var self = this;
    	var controllerName = this.controllerName;
    	
    	if(controllerName=='') {
    		Ext.Msg.alert('成功提示', '请设定controllerName');
    		return false;
    	}
    	
    	this.refs = [{
            ref: 'list',
            selector: controllerName
        },{
            ref: 'add',
            selector: controllerName+'Add'
        },{
            ref: 'edit',
            selector: controllerName+'Edit'
        }];

        this.control({
            controllerName+'Add button[action=submit]' : {
                click : function(button) {
                    var store	= this.getList().store;
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
            },
            controllerName+'Edit button[action=submit]' : {
                click : function(button) {
                    var store	= this.getList().store;
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
            },
            controllerName+' button[action=add]' : {
                click : function() {
                    var window = Ext.widget(controllerName+'Add');
                    window.show();
                }
            },
            controllerName+' button[action=edit]' : {
                click : function(button) {
                    var grid	= button.up('gridpanel');
                    var selections = grid.getSelectionModel().getSelection();
                    if (selections.length > 0) {
                        var window = Ext.widget(controllerName+'Edit');
                        var form = window.down('form').getForm();
                        form.loadRecord(selections[0]);
                        window.show();
                    }
                    else {
                    	Ext.Msg.alert('提示信息', '请选择你要编辑的项');
                    }
                }
            },
            controllerName+' button[action=save]' : {
                click : function(button) {
                    var records = this.getList().store.getUpdatedRecords();
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
                        url: self.actions.save,
                        params: {
                            updateInfos: Ext.encode(updateList)
                        },
                        scope:this,
                        success: function(response){
                            var text = response.responseText;
                            var json = Ext.decode(text);
                            Ext.Msg.alert('提示信息', json.msg);
                            if (json.success) {
                                self.getList().store.load();
                            }
                        }
                    });

                }
            },
            controllerName+' button[action=remove]' : {
                click : function(button) {
                    var grid	= button.up('gridpanel');
                    var selections = grid.getSelectionModel().getSelection();
                    if (selections.length > 0) {
                        Ext.Msg.confirm('提示信息','请确认是否要删除您选择的信息?',function(btn){
                            if (btn == 'yes') {
                                var remove_id = [];
                                for(var i=0;i<selections.length;i++) {
                                    selection = selections[i];
                                    grid.store.remove(selection);
                                    remove_id.push(selection.get('_id'));
                                }

                                Ext.Ajax.request({
                                    url : self.actions.remove,
                                    params : {
                                    	remove_id : Ext.encode(remove_id)
                                    },
                                    scope:this,
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
                        },this);
                    }
                    else {
                        Ext.Msg.alert('提示信息', '请选择您要删除的项');
                    }
                }
            }
        });
    }
});