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
			}, {
				name : 'desc',
				fieldLabel : '项目介绍',
				allowBlank : false
			}]
		}];
        this.callParent();
    }
	
});