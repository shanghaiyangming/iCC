Ext.define('icc.view.idatabase.Collection.Main', {
	extend : 'Ext.panel.Panel',
	requires : [ 'Ext.layout.container.Border',
			'icc.view.idatabase.Collection.Grid',
			'icc.view.idatabase.Collection.TabPanel',
			'icc.view.idatabase.Collection.Accordion' ],
	alias : 'widget.idatabaseCollectionMain',
	closable : true,
	collapsible : true,
	layout : {
		type : 'border'
	},
	initComponent : function() {
		Ext.apply(this, {
			items : [ {
				xtype : 'idatabaseCollectionAccordion',
				project_id : this.project_id,
				name : this.name,
				title : this.title,
				pluginItems : this.pluginItems
			}, {
				xtype : 'idatabaseCollectionTabPanel',
				project_id : this.project_id
			} ]
		});
		this.callParent(arguments);
	}
});