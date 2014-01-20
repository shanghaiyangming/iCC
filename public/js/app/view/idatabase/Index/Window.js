Ext.define('icc.view.idatabase.Index.Window', {
	extend : 'icc.common.Window',
	alias : 'widget.idatabaseIndexWindow',
	title : '索引管理',
	initComponent : function() {
		Ext.apply(this, {
			items : [ {
				xtype : 'idatabaseIndexGrid',
				__PROJECT_ID__ : this.__PROJECT_ID__,
				collection_id : this.collection_id,
				plugin : this.plugin,
				plugin_id : this.plugin_id,
				plugin_collection_id : this.plugin_collection_id
			} ]
		});

		this.callParent();
	}

});