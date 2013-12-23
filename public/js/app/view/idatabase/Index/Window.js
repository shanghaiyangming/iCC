Ext.define('icc.view.idatabase.Index.Window', {
	extend : 'icc.common.Window',
	alias : 'widget.idatabaseIndexWindow',
	title : '索引管理',
	initComponent : function() {
		Ext.apply(this, {
			items : [ {
				xtype : 'idatabaseIndexGrid',
				project_id : this.project_id,
				collection_id : this.collection_id
			} ]
		});

		this.callParent();
	}

});