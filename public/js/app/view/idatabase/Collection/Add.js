Ext.define('icc.view.idatabase.Collection.Add', {
	extend : 'icc.common.Window',
	alias : 'widget.idatabaseCollectionAdd',
	title : '添加数据集合',
	initComponent : function() {
		console.info(this);
		this.items = [ {
			xtype : 'iform',
			url : '/idatabase/collection/add',
			items : [ {
				xtype : 'hiddenfield',
				name : 'project_id',
				value : this.project_id,
				vtype : 'alphanum'
			}, {
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
				xtype: 'textareafield',
				name : 'desc',
				fieldLabel : '功能描述',
				allowBlank : false
			} ]
		} ];

		this.callParent();
	}

});