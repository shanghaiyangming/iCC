Ext.define('icc.view.idatabase.Structure.Window', {
	extend : 'icc.common.Window',
	alias : 'widget.idatabaseStructureWindow',
	title : '属性管理',
	initComponent : function() {
		Ext.apply(this, {
			items : [ {
				xtype : 'idatabaseStructureGrid',
				project_id : this.project_id,
				collection_id : this.collection_id
			} ]
		});

		this.callParent();
	}

});