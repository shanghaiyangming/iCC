Ext.define('icc.view.idatabase.Collection.Accordion', {
	extend : 'Ext.panel.Panel',
	xtype : 'idatabaseCollectionAccordion',
	region : 'west',
	layout : 'accordion',
	width : 300,
	resizable : true,
	collapsible : false,
	defaults : {
		bodyPadding : 0
	},
	pluginItems : [],
	initComponent : function() {
		var items = [ {
			xtype : 'idatabaseCollectionGrid',
			project_id : this.project_id,
			plugin : false,
			plugin_id : ''
		} ];

		items = Ext.Array.merge(items, this.pluginItems);
		Ext.apply(this, {
			items : items
		});

		this.callParent();
	}
});