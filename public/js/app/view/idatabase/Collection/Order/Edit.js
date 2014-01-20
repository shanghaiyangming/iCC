Ext.define('icc.view.idatabase.Collection.Order.Edit', {
	extend : 'icc.common.Window',
	alias : 'widget.idatabaseCollectionOrderEdit',
	title : '编辑集合排序',
	initComponent : function() {
		Ext.apply(this, {
			items : [ {
				xtype : 'iform',
				url : '/idatabase/order/edit',
				items : [ {
					xtype : 'hiddenfield',
					name : '__PROJECT_ID__',
					value : this.__PROJECT_ID__,
					allowBlank : false
				}, {
					xtype : 'hiddenfield',
					name : 'collection_id',
					value : this.collection_id,
					allowBlank : false
				}, {
					xtype : 'idatabaseStructureFieldCombobox',
					name : 'field',
					fieldLabel : '字段名',
					allowBlank : false,
					__PROJECT_ID__ : this.__PROJECT_ID__,
					collection_id : this.collection_id
				}, {
					xtype : 'numberfield',
					name : 'order',
					fieldLabel : '排序',
					minValue : -1,
					maxValue : 1,
					allowBlank : false
				}, {
					xtype : 'numberfield',
					name : 'priority',
					fieldLabel : '优先级',
					allowBlank : false
				} ]
			} ]
		});

		this.callParent();
	}

});