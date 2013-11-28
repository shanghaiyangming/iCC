Ext.define('icc.view.idatabase.Collection.Grid', {
	extend : 'Ext.grid.Panel',
	alias : 'widget.idatabaseCollectionGrid',
	requires : [ 'icc.common.Paging' ],
	title : '数据管理',
	collapsible : true,
	closable : false,
	multiSelect : false,
	disableSelection : false,
	columns : [ {
		text : '集合名称',
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

		var store = Ext.create('icc.store.idatabase.Collection');
		store.proxy.extraParams = {
			'project_id' : me.project_id
		};
		store.load();

		Ext.apply(me, {
			project_id : me.project_id,
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
					width : 100,
					iconCls : 'menu',
					menu : {
						xtype : 'menu',
						plain : true,
						items : [ {
							xtype : 'button',
							text : '属性管理',
							iconCls : 'structure',
							action : 'structure'
						}, {
							xtype : 'button',
							text : '索引管理',
							iconCls : 'index',
							action : 'index'
						}, {
							xtype : 'button',
							text : '统计管理',
							iconCls : 'static',
							action : 'static'
						}, {
							xtype : 'button',
							text : '快捷输入',
							iconCls : 'shortcut',
							action : 'shortcut'
						} ]
					}
				} ]
			} ]
		});

		me.callParent();
	}

});