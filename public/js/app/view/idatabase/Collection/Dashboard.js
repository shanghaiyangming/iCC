Ext.define('icc.view.idatabase.Collection.Dashboard', {
	extend: 'Ext.panel.Panel',
	alias: 'widget.idatabaseCollectionDashboard',
	frame: false,
	border: false,
	resizeTabs: false,
	enableTabScroll: true,
	layout: {
		type: 'table',
		columns: 3
	},
	defaults: {
		frame: false,
		height: 300
	},
	items: [],
	initComponent: function() {
		Ext.apply(this, {
			items: this.items
		});
		this.callParent(arguments);
	},
	listeners : {
		afterrender : function(panel) {
			Ext.Ajax.request({
			    url: '/idatabase/dashboard/index',
			    params: {
			        id: 1
			    },
			    success: function(response){
			        var text = response.responseText;
			        var result = Ext.JSON.decode(text,true);
			        var component = '';
			        panel.add(panel,component);
			    }
			});
		}
	}
});