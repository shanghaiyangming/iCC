Ext.define('icc.view.idatabase.Structure.Grid', {
	extend : 'Ext.grid.Panel',
	alias : 'widget.idatabaseStructureGrid',
	requires : [ 'icc.common.Paging' ],
	collapsible : false,
	closable : false,
	multiSelect : false,
	disableSelection : false,
	columns : [ {
		text : '属性名称',
		dataIndex : 'field',
		flex : 1
	}, {
		text : '属性描述',
		dataIndex : 'label',
		flex : 1
	}, {
		text : '输入类型',
		dataIndex : 'type',
		flex : 1
	}, {
		text : '是否检索',
		dataIndex : 'searchable',
		flex : 1
	}, {
		text : '是否列表',
		dataIndex : 'main',
		flex : 1
	}, {
		text : '是否必填',
		dataIndex : 'required',
		flex : 1
	}, {
		text : '显示图片',
		dataIndex : 'showImage',
		flex : 1
	}, {
		text : '排列顺序',
		dataIndex : 'orderBy',
		flex : 1
	}, {
		text : '关联结合',
		dataIndex : 'rshCollection',
		flex : 1,
		hidden : false,
		field : {
			xtype : 'idatabaseCollectionCombobox',
			project_id : this.project_id
		},
		renderer : function(value,metaData,record,rowIndex,colIndex,store,view,returnHtml) {
			var record = store.findRecord('_id', value);
			if (record != null) {
				return record.get('label');
			}
		}
	}, {
		text : '关联方式',
		dataIndex : 'rshType',
		flex : 1,
		hidden : true
	}, {
		text : '关联显示字段',
		dataIndex : 'rshKey',
		flex : 1,
		hidden : true
	}, {
		text : '关联提交字段',
		dataIndex : 'rshValue',
		flex : 1,
		hidden : true
	}, {
		xtype : 'datecolumn',
		text : '创建时间',
		dataIndex : '__CREATE_TIME__',
		flex : 1,
		format : 'Y-m-d',
		hidden : true
	} ],
	initComponent : function() {
		var me = this;
		var store = Ext.create('icc.store.idatabase.Structure');
		store['proxy']['extraParams']['project_id'] = me.project_id;
		store['proxy']['extraParams']['collection_id'] = me.collection_id;
		store.load();

		Ext.apply(me, {
			store : store,
			bbar : {
				xtype : 'paging',
				store : store
			},
			dockedItems : [ {
				xtype : 'toolbar',
				dock : 'top',
				items : [ {
					text : '操作',
					iconCls : 'menu',
					width : 100,
					menu : {
						xtype : 'menu',
						plain : true,
						items : [ {
							xtype : 'button',
							text : '新增',
							iconCls : 'add',
							action : 'add'
						}, {
							xtype : 'button',
							text : '编辑',
							iconCls : 'edit',
							action : 'edit'
						}, {
							xtype : 'button',
							text : '保存',
							iconCls : 'save',
							action : 'save'
						}, {
							xtype : 'button',
							text : '删除',
							iconCls : 'remove',
							action : 'remove'
						} ]
					}
				} ]
			} ]
		});

		me.callParent();
	}

});