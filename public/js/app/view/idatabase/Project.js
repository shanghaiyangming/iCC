Ext.define('icc.view.idatabase.Project', {
	extend : 'Ext.grid.Panel',
	alias : 'widget.idatabaseProject',
    models : ['Project'],
    stores : ['Project'],
    views : ['idatabase.Project','idatabase.Project.Add','idatabase.Project.Edit','idatabase.Project.TabPanel'],
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
		flex: 1
	},{
		text: '创建时间',  
		dataIndex: '__CREATE_TIME__',
		flex: 1
	}],
	initComponent: function() {
		var self = this;
		this.bbar = {
			xtype : 'paging',
			store : this.store
		};
		
		this.dockedItems =[{
			xtype : 'tbar',
			dock: 'top'
		},{
			xtype : 'tbar',
			dock: 'top'
		}];
		
		this.callParent();
	}
	
});