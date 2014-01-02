Ext.define('icc.view.idatabase.Project.Add', {
	extend : 'icc.common.Window',
	alias : 'widget.idatabaseProjectAdd',
	title : '添加项目',
	initComponent: function() {
		this.items = [ {
			xtype : 'iform',
			url : '/idatabase/project/add',
			items : [{
				name : 'name',
				fieldLabel : '项目名称',
				allowBlank : false
			},{
				name : 'sn',
				fieldLabel : '项目编号',
				allowBlank : false
			},{
				xtype : 'radiogroup',
				fieldLabel : '是否系统项目(仅超级管理员可见)',
				defaultType : 'radiofield',
				layout : 'hbox',
				items : [ {
					boxLabel : '是',
					name : 'isSystem',
					inputValue : true
				}, {
					boxLabel : '否',
					name : 'isSystem',
					inputValue : false,
					checked : true
				} ]
			}, {
				xtype: 'textareafield',
				name : 'desc',
				fieldLabel : '项目介绍',
				allowBlank : false
			}]
		}];
		
        this.callParent();
    }
	
});