Ext.define('icc.view.idatabase.Collection.Edit', {
	extend : 'icc.common.Window',
	alias : 'widget.idatabaseCollectionEdit',
	title : '编辑项目',
	initComponent : function() {
		Ext.apply(this, {
			items : [ {
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
					allowBlank : false,
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
					xtype : 'textareafield',
					name : 'desc',
					fieldLabel : '功能描述',
					allowBlank : false
				}, {
					xtype : 'numberfield',
					name : 'orderBy',
					fieldLabel : '排列顺序',
					allowBlank : false
				}, {
					xtype : 'fieldset',
					title : '高级设定',
					collapsed : true,
					collapsible : true,
					items : [ {
						xtype : 'radiogroup',
						fieldLabel : '是否专家集合',
						defaultType : 'radiofield',
						layout : 'hbox',
						items : [ {
							boxLabel : '是',
							name : 'isProfessional',
							inputValue : true
						}, {
							boxLabel : '否',
							name : 'isProfessional',
							inputValue : false,
							checked : true
						} ]
					}, {
						xtype : 'radiogroup',
						fieldLabel : '是否树状集合',
						defaultType : 'radiofield',
						layout : 'hbox',
						items : [ {
							boxLabel : '是',
							name : 'isTree',
							inputValue : true
						}, {
							boxLabel : '否',
							name : 'isTree',
							inputValue : false,
							checked : true
						} ]
					}, {
						xtype : 'fieldset',
						title : '行展开模式设定（选填）',
						collapsed : true,
						collapsible : true,
						items : [ {
							xtype : 'radiogroup',
							fieldLabel : '是否行展开显示',
							defaultType : 'radiofield',
							layout : 'hbox',
							items : [ {
								boxLabel : '是',
								name : 'isRowExpander',
								inputValue : true
							}, {
								boxLabel : '否',
								name : 'isRowExpander',
								inputValue : false,
								checked : true
							} ]
						}, {
							xtype : 'textareafield',
							name : 'rowExpanderTpl',
							fieldLabel : '行展开模板',
							allowBlank : true
						} ]
					} ]
				} ]
			} ]
		});
		this.callParent();
	}

});