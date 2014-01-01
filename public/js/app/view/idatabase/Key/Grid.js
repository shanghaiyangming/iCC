Ext.define('icc.view.idatabase.Key.Grid', {
	extend : 'Ext.grid.Panel',
	alias : 'widget.idatabaseKeyGrid',
	requires : [ 'icc.common.Paging' ],
	collapsible : false,
	closable : false,
	multiSelect : false,
	disableSelection : false,
	columns : [ {
		text : '密钥名称',
		dataIndex : 'name',
		flex : 1
	}, {
		text : '密钥描述',
		dataIndex : 'desc',
		flex : 1
	}, {
		xtype : 'datecolumn',
		text : '过期时间',
		dataIndex : 'expire',
		format : 'Y-m-d',
		flex : 1
	}, {
		xtype : 'booleancolumn',
		text : '有效性',
		dataIndex : 'active',
		flex : 1,
		field : {
			xtype : 'commonComboboxBoolean'
		}
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
		var store = Ext.create('icc.store.idatabase.Key');
		store['proxy']['extraParams']['project_id'] = me.project_id;
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
				}, '-', {
					text : '管理',
					iconCls : 'menu',
					width : 100,
					menu : {
						xtype : 'menu',
						plain : true,
						items : [ {
							xtype : 'button',
							text : '权限设置',
							iconCls : 'permission',
							action : 'permission'
						}]
					}
				} ]
			} ]
		});

		me.callParent();
	}

});