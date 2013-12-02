Ext.define('icc.view.idatabase.Data.Add', {
	extend : 'icc.common.Window',
	alias : 'widget.idatabaseDataAdd',
	title : '添加数据',
	initComponent : function() {
		Ext.apply(this, {
			items : [ {
				xtype : 'iform',
				url : '/idatabase/data/add',
				items : []
			} ]
		});

		this.callParent();
	}

});