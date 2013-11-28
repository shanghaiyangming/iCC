Ext.define('icc.view.idatabase.Plugin.Window', {
	extend : 'icc.common.Window',
	alias : 'widget.idatabasePluginWindow',
	title : '项目插件管理',
	initComponent : function() {
		Ext.apply(this, {
			items : [ {
				xtype : 'idatabasePluginGrid',
				project_id : this.project_id
			} ]
		});

		this.callParent();
	}

});