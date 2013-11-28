Ext.define('icc.view.idatabase.Plugin.Add', {
	extend : 'icc.common.Window',
	alias : 'widget.idatabasePluginAdd',
	title : '添加插件',
	initComponent : function() {
		console.info(this);
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
					xtype : 'idatabasePluginCombobox'
				} ]
			} ]
		});

		this.callParent();
	}

});	