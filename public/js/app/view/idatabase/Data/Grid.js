Ext.define('icc.view.idatabase.Data.Grid', {
	extend : 'Ext.grid.Panel',
	alias : 'widget.idatabaseDataGrid',
	title : '数据明细',
	region : 'center', 
	border : false,
	collapsible : false,
	split : true,
	closable : false,
	multiSelect : false,
	disableSelection : false,
	selType : 'rowmodel',
	plugins : [ Ext.create('Ext.grid.plugin.CellEditing', {
		clicksToEdit : 2
	}) ],
	columns : [ {
		text : '_id',
		dataIndex : '_id',
		flex : 1,
		hidden : true
	}, {
		xtype : 'datecolumn',
		text : '创建时间',
		dataIndex : '__CREATE_TIME__',
		flex : 1,
		format : 'Y-m-d H:i:s'
	} , {
		xtype : 'datecolumn',
		text : '最后修改时间',
		dataIndex : '__MODIFY_TIME__',
		flex : 1,
		format : 'Y-m-d H:i:s',
		hidden : true
	} ],
	initComponent : function() {
		Ext.apply(this, {
			dockedItems : [ {
				xtype : 'toolbar',
				dock : 'top',
				items : [ {
					text : '新增',
					iconCls : 'add',
					width : 60,
					action : 'add'
				}, '-', {
					text : '编辑',
					iconCls : 'edit',
					width : 60,
					action : 'edit'
				}, '-', {
					text : '保存',
					iconCls : 'save',
					width : 60,
					action : 'save'
				}, '-', {
					text : '删除',
					iconCls : 'delete',
					width : 60,
					tooltip : '删除',
					action : 'remove'
				} ]
			}],
			store : this.store,
			bbar : {
				xtype : 'paging',
				store : this.store
			}
		});

		this.callParent();
	}
});