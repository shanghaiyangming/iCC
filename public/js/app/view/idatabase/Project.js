Ext.define('icc.view.idatabase.Project', {
	extend : 'Ext.grid.Panel',
	alias : 'widget.idatabaseProject',
    models : ['icc.model.Project'],
    stores : ['icc.store.Project'],
    views : ['icc.view.Project','icc.view.Project.Add','icc.view.Project.Edit','icc.view.Project.TabPanel'],
	requires : [ 'icc.common.Paging','icc.store.Project' ],
	title : '项目列表',
	region: 'west',
	width : 200,
	collapsible : true,
	closable : false,
	multiSelect : false,
	disableSelection : true,
	store : Ext.data.StoreManager.lookup('Project'),
	columns : [{
		text: '项目名称',  
		dataIndex: 'name',
		flex: 1
	},{
		text: '创建时间',  
		dataIndex: '__CREATE_TIME__',
		flex: 1
	}],
	initComponent: function() {
		var self = this;
//		this.bbar = {
//			xtype : 'paging',
//			store : this.store
//		}
		this.callParent();
	},
	
});