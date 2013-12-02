Ext.define('icc.view.idatabase.Data.Edit', {
	extend : 'icc.common.Window',
	alias : 'widget.idatabaseDataEdit',
	title : '编辑数据',
	initComponent : function() {
		Ext.apply(this, {
			items : [ {
				xtype : 'iform',
				url : '/idatabase/data/edit',
				items : []
			} ]
		});

		this.callParent();
	}

});