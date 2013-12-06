Ext.define('icc.view.idatabase.Collection.Order.Window', {
	extend : 'icc.common.Window',
	alias : 'widget.idatabaseCollectionOrderWindow',
	title : '排序管理',
	initComponent : function() {
		Ext.apply(this, {
			items : [ {
				xtype : 'idatabaseCollectionOrderGrid',
				project_id : this.project_id,
				collection_id : this.collection_id
			} ]
		});

		this.callParent();
	}

});