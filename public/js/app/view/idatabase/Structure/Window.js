Ext.define('icc.view.idatabase.Structure.Window', {
	extend : 'icc.common.Window',
	alias : 'widget.idatabaseStructureWindow',
	title : '属性管理',
	initComponent : function() {
		Ext.apply(this, {
			items : [ {
				xtype : 'idatabaseStructureGrid',
				__PROJECT_ID__ : this.__PROJECT_ID__,
				collection_id : this.collection_id,
				plugin_id : this.plugin_id
			} ]
		});

		this.callParent();
	}

});