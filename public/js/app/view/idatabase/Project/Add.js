Ext.define('icc.view.idatabase.Project.Add', {
	extend : 'icc.common.Window',
	alias : 'widget.idatabaseProjectAdd',
	title : '添加项目',
	requires : [],
	layout:'border',
	initComponent: function() {
		this.items = [ {
			xtype : 'form',
			url : '/idatabase/project/add',
			items : [{
				name : 'name',
				fieldLabel : '项目名称',
				allowBlank : false
			},{
				name : 'sn',
				fieldLabel : '项目编号',
				allowBlank : false
			}, {
				name : 'desc',
				fieldLabel : '项目介绍',
				allowBlank : false
			}]
		}];
		
        this.callParent();
    }
	
});