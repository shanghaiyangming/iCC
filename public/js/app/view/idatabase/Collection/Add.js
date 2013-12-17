Ext.define('icc.view.idatabase.Collection.Add', {
	extend : 'icc.common.Window',
	alias : 'widget.idatabaseCollectionAdd',
	title : '添加数据集合',
	initComponent : function() {
		Ext.apply(this, {
			items : [ {
				xtype : 'iform',
				url : '/idatabase/collection/add',
				items : [ {
					xtype : 'hiddenfield',
					name : 'project_id',
					value : this.project_id,
					vtype : 'alphanum'
				}, {
					xtype : 'hiddenfield',
					name : 'plugin',
					value : this.plugin,
					allowBlank : false
				}, {
					xtype : 'hiddenfield',
					name : 'plugin_id',
					value : this.plugin_id,
					vtype : 'alphanum',
					allowBlank : false
				}, {
					xtype : 'hiddenfield',
					name : 'plugin_collection_id',
					value : '',
					vtype : 'alphanum',
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
					xtype : 'radiogroup',
					fieldLabel : '是否树状集合',
					defaultType : 'radiofield',
					layout : 'hbox',
					items : [ {
						boxLabel : '是',
						name : 'isTree',
						inputValue : true,
						
					}, {
						boxLabel : '否',
						name : 'isTree',
						inputValue : false,
						checked : true
					} ]
				}, {
					xtype : 'textareafield',
					name : 'desc',
					fieldLabel : '功能描述',
					allowBlank : false
				}, {
					xtype : 'numberfield',
					name : 'orderBy',
					fieldLabel : '排列顺序',
					allowBlank : false,
					value : this.orderBy
				} ]
			} ]
		});

		this.callParent();
	}

});