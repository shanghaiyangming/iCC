Ext.define('icc.view.idatabase.Project.Grid', {
	extend : 'Ext.grid.Panel',
	alias : 'widget.idatabaseProjectGrid',
	requires : [ 'icc.common.Paging','icc.store.idatabase.Project' ],
	title : '项目管理',
	region : 'west',
	width : 360,
	collapsible : true,
	closable : false,
	multiSelect : false,
	disableSelection : false,
	sortableColumns : false,
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
		var store = Ext.data.StoreManager.lookup('idatabase.Project');
		console.info(store);
		Ext.apply(this, {
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
							text : '用户管理',
							iconCls : 'user',
							action : 'user'
						}, {
							xtype : 'button',
							text : '密钥管理',
							iconCls : 'key',
							action : 'key'
						}, {
							xtype : 'button',
							text : '仪表盘管理',
							iconCls : 'dashboard',
							action : 'dashboard'
						}, {
							xtype : 'button',
							text : '插件管理',
							iconCls : 'plugin',
							action : 'plugin'
						} ]
					}
				}, '-', {
					xtype : 'searchBar',
					store : store
				} ]
			} ]
		});

		this.callParent(arguments);
	}
});