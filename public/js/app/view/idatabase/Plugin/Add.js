Ext.define('icc.view.idatabase.Plugin.Add', {
	extend : 'icc.common.Window',
	alias : 'widget.idatabasePluginAdd',
	title : '添加插件',
	initComponent : function() {
		Ext.apply(this, {
			items : [ {
				xtype : 'iform',
				url : '/idatabase/plugin/add',
				items : [ {
					xtype : 'hiddenfield',
					name : 'project_id',
					fieldLabel : '插件编号',
					allowBlank : false,
					value : this.project_id
				}, {
					xtype : 'idatabasePluginCombobox',
					allowBlank : false
				}, {
					xtype : 'idatabaseProjectCombobox',
					fieldLabel : '共享来源项目',
					name : 'source_project_id',
					allowBlank : true
				} ]
			} ]
		});

		this.callParent();
	}

});