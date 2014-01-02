Ext.define('icc.view.idatabase.Project.Edit', {
	extend : 'icc.common.Window',
	alias : 'widget.idatabaseProjectEdit',
	title : '编辑项目',
	initComponent: function() {
		var project_id = this.project_id;
		this.items = [ {
			xtype : 'iform',
			url : '/idatabase/project/edit',
			items : [{
				xtype : 'hiddenfield',
				name : '_id',
				allowBlank : false
			},{
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