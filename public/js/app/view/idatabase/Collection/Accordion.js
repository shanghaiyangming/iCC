Ext.define('icc.view.idatabase.Collection.Accordion', {
	extend : 'Ext.panel.Panel',
	xtype : 'idatabaseCollectionAccordion',
	region : 'west',
	layout : 'accordion',
	width : 400,
	collapsible : true,
	defaults : {
		bodyPadding : 0
	},
	initComponent : function() {
		var items = [ {
			xtype : 'idatabaseCollectionGrid',
			project_id : this.project_id,
			plugin : false,
			plugin_id : ''
		}, {
			xtype : 'idatabaseCollectionGrid',
			project_id : this.project_id,
			plugin : true,
			plugin_id : ''
		} ];

		Ext.apply(this, {
			items : items
		});

		this.callParent();
	}
});