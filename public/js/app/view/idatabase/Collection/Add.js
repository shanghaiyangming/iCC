Ext.define('icc.view.idatabase.Collection.Add', {
	extend : 'icc.common.Window',
	alias : 'widget.idatabaseCollectionAdd',
	title : '添加数据集合',
	requires : [],
	layout : 'border',
	initComponent : function() {
		this.items = [ {
			xtype : 'form',
			url : '/idatabase/collection/add',
			items : [ {
				name : 'alias',
				fieldLabel : '集合别名(英文)',
				allowBlank : false,
				vtype : 'alphanum'
			}, {
				name : 'name',
				fieldLabel : '集合名称(中文)',
				allowBlank : false
			}, {
				xtype : 'idatabaseCollectionTypeCombobox'
			}, {
				name : 'desc',
				fieldLabel : '功能描述',
				allowBlank : false
			} ]
		} ];

		this.callParent();
	}

});