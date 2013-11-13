Ext.define('icc.view.idatabase.Project.Add', {
	extend : 'icc.common.Window',
	alias : 'widget.idatabaseProjectAdd',
	title : '添加项目',
	requires : [],
	layout:'border',
	project_id : '',
	initComponent: function() {
		var project_id = this.project_id;
		this.items = [ {
			xtype : 'form',
			items : [{
				xtype : 'hiddenfield',
				name : 'projectId',
				value: project_id
			},{
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