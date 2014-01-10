Ext.define('icc.view.idatabase.Collection.Dashboard', {
	extend : 'Ext.tab.Panel',
	alias : 'widget.idatabaseCollectionDashboard',
	frame : true,
	region : 'center',
	resizeTabs : false,
	minTabwidth : 100,
	tabwidth : 100,
	enableTabScroll : true,
	layout : {
		type : 'table',
		columns : 3
	},
	defaults : {
		frame : false,
		height : 300
	},
	items:[],
	initComponent: function() {
		Ext.apply(this,{
			items : this.items
		});
		this.callParent(arguments);
	}
});