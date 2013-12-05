Ext.define('icc.view.idatabase.Data.Edit', {
	extend : 'icc.common.Window',
	alias : 'widget.idatabaseDataEdit',
	title : '编辑数据',
	initComponent : function() {
		Ext.apply(this, {
			items : [ {
				xtype : 'iform',
				url : '/idatabase/data/add',
				items : [ {
					xtype : 'hiddenfield',
					name : 'project_id',
					value : this.project_id,
					vtype : 'alphanum'
				}, {
					xtype : 'hiddenfield',
					name : 'collection_id',
					value : this.collection_id,
					allowBlank : false
				} ]
			} ]
		});

		this.callParent();
	}

});