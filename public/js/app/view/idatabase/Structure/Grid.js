Ext.define('icc.view.idatabase.Structure.Grid', {
	extend : 'Ext.grid.Panel',
	alias : 'widget.idatabaseStructureGrid',
	requires : [ 'icc.common.Paging' ],
	collapsible : false,
	closable : false,
	multiSelect : false,
	disableSelection : false,
	columns : [ {
		text : '项目名称',
		dataIndex : 'name',
		flex : 2
	}, {
		xtype : 'datecolumn',
		text : '创建时间',
		dataIndex : '__CREATE_TIME__',
		flex : 1,
		format : 'Y-m-d'
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