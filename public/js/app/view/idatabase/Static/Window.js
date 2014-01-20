Ext.define('icc.view.idatabase.Static.Window', {
	extend : 'icc.common.Window',
	alias : 'widget.idatabaseStaticWindow',
	title : '统计管理',
	initComponent : function() {
		Ext.apply(this, {
			items : [ {
				xtype : 'idatabaseStaticGrid',
				__PROJECT_ID__ : this.__PROJECT_ID__,
				collection_id : this.collection_id
			} ]
		});

		this.callParent();
	}

});