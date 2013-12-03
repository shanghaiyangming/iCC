Ext.define('icc.view.idatabase.Structure.Add', {
	extend : 'icc.common.Window',
	alias : 'widget.idatabaseStructureAdd',
	title : '添加属性',
	initComponent : function() {
		Ext.apply(this, {
			items : [ {
				xtype : 'iform',
				url : '/idatabase/structure/add',
				fieldDefaults : {
					labelAlign : 'left',
					labelWidth : 150,
					anchor : '100%'
				},
				items : [ {
					xtype : 'hiddenfield',
					name : 'project_id',
					fieldLabel : '项目编号',
					allowBlank : false,
					value : this.project_id
				}, {
					xtype : 'hiddenfield',
					name : 'collection_id',
					fieldLabel : '集合编号',
					allowBlank : false,
					value : this.collection_id
				}, {
					name : 'field',
					fieldLabel : '属性名(英文数字)',
					allowBlank : false
				}, {
					name : 'label',
					fieldLabel : '属性描述',
					allowBlank : false
				}, {
					xtype : 'combobox',
					name : 'type',
					fieldLabel : '输入类型',
					allowBlank : false,
					store : 'idatabase.Structure.Type',
					valueField : 'val',
					displayField : 'name',
					editable : false
				}, {
					xtype : 'idatabaseStructureFilterCombobox',
					value : 516,
				}, {
					xtype : 'radiogroup',
					fieldLabel : '是否为检索条件',
					defaultType : 'radiofield',
					layout : 'hbox',
					items : [ {
						boxLabel : '是',
						name : 'searchable',
						inputValue : true,
						checked : true
					}, {
						boxLabel : '否',
						name : 'searchable',
						inputValue : false
					} ]
				}, {
					xtype : 'radiogroup',
					fieldLabel : '是否在列表页显示',
					defaultType : 'radiofield',
					layout : 'hbox',
					items : [ {
						boxLabel : '是',
						name : 'main',
						inputValue : true,
						checked : true
					}, {
						boxLabel : '否',
						name : 'main',
						inputValue : false
					} ]
				}, {
					xtype : 'radiogroup',
					fieldLabel : '是否必填',
					defaultType : 'radiofield',
					layout : 'hbox',
					items : [ {
						boxLabel : '是',
						name : 'required',
						inputValue : true
					}, {
						boxLabel : '否',
						name : 'required',
						inputValue : false,
						checked : true
					} ]
				}, {
					xtype : 'radiogroup',
					fieldLabel : '是否在表格中显示图片',
					defaultType : 'radiofield',
					layout : 'hbox',
					items : [ {
						boxLabel : '是',
						name : 'showImage',
						inputValue : true
					}, {
						boxLabel : '否',
						name : 'showImage',
						inputValue : false,
						checked : true
					} ]
				}, {
					xtype : 'numberfield',
					name : 'orderBy',
					fieldLabel : '排序',
					allowBlank : false,
					value : this.orderBy
				}, {
					xtype : 'fieldset',
					title : '关联设定（选填）',
					items : [ {
						xtype : 'idatabaseCollectionCombobox',
						project_id : this.project_id,
						fieldLabel : '关联集合列表',
						name : 'rshCollection'
					}, {
						xtype : 'radiogroup',
						fieldLabel : '关联显示方法',
						defaultType : 'radiofield',
						layout : 'hbox',
						items : [ {
							boxLabel : '下拉菜单',
							name : 'rshType',
							inputValue : 'combobox',
							checked : true
						}, {
							boxLabel : '单选框',
							name : 'rshType',
							inputValue : 'radio'
						}, {
							boxLabel : '复选框',
							name : 'rshType',
							inputValue : 'checkbox'
						} ]
					}, {
						xtype : 'radiogroup',
						fieldLabel : '关联表显示字段',
						defaultType : 'radiofield',
						layout : 'hbox',
						items : [ {
							boxLabel : '是',
							name : 'rshKey',
							inputValue : true
						}, {
							boxLabel : '否',
							name : 'rshKey',
							inputValue : false,
							checked : true
						} ]
					}, {
						xtype : 'radiogroup',
						fieldLabel : '关联表提交字段',
						defaultType : 'radiofield',
						layout : 'hbox',
						items : [ {
							boxLabel : '是',
							name : 'rshValue',
							inputValue : true
						}, {
							boxLabel : '否',
							name : 'rshValue',
							inputValue : false,
							checked : true
						} ]
					} ]
				}]
			} ]
		});

		this.callParent();
	}

});