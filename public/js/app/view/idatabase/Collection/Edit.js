Ext.define('icc.view.idatabase.Collection.Edit', {
	extend : 'icc.common.Window',
	alias : 'widget.idatabaseCollectionEdit',
	title : '编辑项目',
	initComponent : function() {
		this.items = [ {
			xtype : 'iform',
			url : '/idatabase/collection/edit',
			items : [ {
				xtype : 'hiddenfield',
				name : 'project_id',
				value : this.project_id,
				vtype : 'alphanum'
			}, {
				xtype : 'hiddenfield',
				name : '_id',
				allowBlank : false
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
				xtype : 'textareafield',
				name : 'desc',
				fieldLabel : '功能描述',
				allowBlank : false
			} ]
		} ];
		this.callParent();
	}

});