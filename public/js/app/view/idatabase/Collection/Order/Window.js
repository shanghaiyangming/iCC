Ext.define('icc.view.idatabase.Collection.Order.Window', {
	extend : 'icc.common.Window',
	alias : 'widget.idatabaseCollectionOrderWindow',
	title : '排序管理',
	initComponent : function() {
		Ext.apply(this, {
			items : [ {
				xtype : 'idatabaseCollectionOrderGrid',
				__PROJECT_ID__ : this.__PROJECT_ID__,
				collection_id : this.collection_id
			} ]
		});

		this.callParent();
	}

});