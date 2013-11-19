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
    initListeners : {},
    init : function() {
    	var self = this;
    	var controllerName = this.controllerName;
    	
    	if(controllerName=='') {
    		Ext.Msg.alert('成功提示', '请设定controllerName');
    		return false;
    	}
    	
    	this.addRef([{
            ref: 'list',
            selector: this.controllerName
        },{
            ref: 'add',
            selector: this.controllerName+'Add'
        },{
            ref: 'edit',
            selector: this.controllerName+'Edit'
        }]);

    	var listeners = {};
    	
    	listeners[controllerName+'Add button[action=submit]'] = {
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
    	};
    	
    	listeners[controllerName+'Edit button[action=submit]'] = {
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
    	};
    	
    	listeners[controllerName+' button[action=add]'] = {
			click : function() {
                var window = Ext.widget(controllerName+'Add');
                window.show();
            }
    	};
    	
    	listeners[controllerName+' button[action=edit]'] = {
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
    	};
    	
    	listeners[controllerName+' button[action=save]'] = {
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
    	};
    	
    	listeners[controllerName+' button[action=remove]'] = {
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
                                url : self.actions.remove,
                                params : {
                                	_id : Ext.encode(_id)
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
    	};
	
    	//在基础监听之外，添加自定义监听,如果同名的selector存在，那么追加事件
    	console.info(typeof(this.initListeners),this.initListeners);
    	if(typeof(this.initListeners)==='object') {
	    	for (attrName in this.initListeners) {
	    		if(listeners[attrName]==undefined || listeners[attrName]==null) {
	    			listeners[attrName] = this.initListeners[attrName];
	    		}
	    		else {
	    			for(event in this.initListeners[attrName]) {
	    				listeners[attrName][event] = this.initListeners[attrName][event];
	    			}
	    		}
	    	}
    	}
    	console.info(listeners);
        this.control(listeners);
    }
});