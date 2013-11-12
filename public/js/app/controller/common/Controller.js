Ext.define('icc.controller.common.Controller', {
    extend : 'Ext.app.Controller',
    models : [],
    stores : [],
    views : [],
    refs : [],
    controllerName : 'default',
    init : function() {
    	var self = this;
        this.control({
            ' button[action=reset]' : {
                click : function(button) {
                    var form = button.up('form').getForm();
                    form.reset();
                }
            },
            'chinapayProjectAdd button[action=submit]' : {
                click : function(button) {
                    var store	= this.getChinapayProjectStore();
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
                }
            },
            'chinapayProjectEdit button[action=reset]' : {
                click : function(button) {
                    var form = button.up('form').getForm();
                    form.reset();
                }
            },
            'chinapayProjectEdit button[action=submit]' : {
                click : function(button) {
                    var store	= this.getChinapayProjectStore();
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
            'chinapayProject button[action=add]' : {
                click : function() {
                    var window = Ext.widget('chinapayProjectAdd');
                    window.show();
                }
            },
            'chinapayProject button[action=edit]' : {
                click : function(button) {
                    var grid	= button.up('gridpanel');
                    var selections = grid.getSelectionModel().getSelection();
                    if (selections.length > 0) {
                        var row	= selections[0];

                        var window = Ext.widget('chinapayProjectEdit');
                        var form = window.down('form').getForm();
                        form.setValues({
                            _id          : row.get('_id'),
                            project_name : row.get('project_name'),
                            account_id   : row.get('account_id'),
                            callback     : row.get('callback'),
                            password     : row.get('password')
                        });
                        window.show();
                    }
                }
            },
            'chinapayProject button[action=save]' : {
                click : function(button) {
                    var records = this.getChinapayProjectStore().getUpdatedRecords();
                    var recordsNumber = records.length;
                    if(recordsNumber==0) {
                        Ext.Msg.alert('提示信息', '未发现信息修改');
                    }
                    var updateList = [];
                    for(var i=0;i<recordsNumber;i++) {
                        record = records[i];
                        updateList.push(record.data);
                    }

                    Ext.Ajax.request({
                        url: '/admin/chinapay-project/update',
                        params: {
                            jsonInfos: Ext.encode(updateList)
                        },
                        scope:this,
                        success: function(response){
                            var text = response.responseText;
                            var json = Ext.decode(text);
                            Ext.Msg.alert('提示信息', json.msg);
                            if (json.success) {
                                this.getChinapayProjectStore().load();
                            }
                        }
                    });

                }
            },
            'chinapayProject button[action=remove]' : {
                click : function(button) {
                    var grid	= button.up('gridpanel');
                    var selections = grid.getSelectionModel().getSelection();
                    if (selections.length > 0) {
                        Ext.Msg.confirm('提示信息','请确认是否要删除您选择的信息?',function(btn){
                            if (btn == 'yes') {
                                var id = [];
                                for(var i=0;i<selections.length;i++) {
                                    selection = selections[i];
                                    grid.store.remove(selection);
                                    id.push(selection.get('_id'));
                                }

                                Ext.Ajax.request({
                                    url : '/admin/chinapay-project/remove',
                                    params : {
                                        _id : Ext.encode(id)
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
                        Ext.Msg.alert('提示信息', '请选择您要删除的项目');
                    }
                }
            },
            'chinapayProject' : {
                selectionchange : function(selectionModel, records) {
                    if (records[0]) {
                        var record = records[0];
                        var id     = record.get('_id');
                        var panelId = 'ChinapayProjectDataGrid'+id;
                        var panel = this.getChinapayProjectTabPanel().getComponent(panelId);
                        if (panel == null) {
                            var title = '付款记录';
                            panel = Ext.widget('chinapayProjectDataGrid', {
                                id         : panelId,
                                project_id : id,
                                account_id : record.get('account_id'),
                                name       : record.get('project_name')+title,
                                title      : record.get('project_name')+title
                            });
                            this.getChinapayProjectTabPanel().add(panel);
                        }
                        this.getChinapayProjectTabPanel().setActiveTab(panelId);
                    }
                }
            }
        });
    }
});