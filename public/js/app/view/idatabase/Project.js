Ext.define('icc.view.idatabase.Project', {
	extend : 'Ext.grid.Panel',
	alias : 'widget.idatabaseProject',
	requires : [ 'icc.common.Paging'],
	title : '项目列表',
	region: 'west',
	width : 400,
	collapsible : true,
	closable : false,
	multiSelect : false,
	disableSelection : true,
	store : Ext.data.StoreManager.lookup('Project'),
	columns : [{
		text: '项目名称',  
		dataIndex: 'name',
		flex: 2
	},{
		xtype:'datecolumn',
		text: '创建时间', 
		dataIndex: '__CREATE_TIME__',
		flex: 1,
		format:'Y-m-d'
	}],
	initComponent: function() {
		var self = this;
		console.info(Ext.data.StoreManager.lookup('Project'));
		this.bbar = {
			xtype : 'paging',
			store : this.store
		};
		
		this.dockedItems =[{
			xtype : 'toolbar',
			dock: 'top',
			items : [ {
				text : '操作',
				iconCls : 'menu',
				width : 106,
				menu : {
					xtype : 'menu',
					plain: true,
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
			},'-',{
				text : '管理',
				width : 106,
				iconCls : 'menu',
				menu : {
					xtype : 'menu',
					plain: true,
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
						text : '插件管理',
						iconCls : 'plugin',
						action : 'plugin'
					}]
				}
			}]
		}];
		
		this.callParent();
	}
	
});