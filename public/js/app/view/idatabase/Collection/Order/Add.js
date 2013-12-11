Ext.define('icc.view.idatabase.Collection.Order.Add', {
	extend : 'icc.common.Window',
	alias : 'widget.idatabaseCollectionOrderAdd',
	title : '添加集合排序',
	initComponent : function() {
		Ext.apply(this, {
			items : [ {
				xtype : 'iform',
				url : '/idatabase/order/add',
				items : [ {
					xtype : 'hiddenfield',
					name : 'project_id',
					value : this.project_id,
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
					project_id : this.project_id,
					collection_id : this.collection_id
				}, {
					xtype : 'numberfield',
					fieldLabel : '排序',
					name : 'order',
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