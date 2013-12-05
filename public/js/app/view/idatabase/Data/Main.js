Ext.define('icc.view.idatabase.Data.Main', {
	extend : 'Ext.panel.Panel',
	requires : [],
	alias : 'widget.idatabaseDataMain',
	closable : true,
	layout : {
		type : 'border'
	},
	initComponent : function() {
		Ext.apply(this, {
			items : [ {
				xtype : 'idatabaseDataGrid',
				project_id : this.project_id,
				collection_id : this.collection_id,
				columns : this.gridColumns,
				store : this.gridStore
			}, {
				xtype : 'idatabaseDataSearch',
				project_id : this.project_id,
				collection_id : this.collection_id,
				searchFields : this.searchFields
			} ]
		});
		this.callParent(arguments);
	}
});
