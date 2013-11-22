Ext.define('icc.view.idatabase.Collection.Main', {
	extend : 'Ext.panel.Panel',
	requires : [ 'Ext.layout.container.Border',
			'icc.view.idatabase.Collection.Grid',
			'icc.view.idatabase.Collection.TabPanel',
			'icc.view.idatabase.Collection.Accordion'],
	alias : 'widget.idatabaseCollectionMain',
	closable : true,
	layout : {
		type : 'border'
	},
	initComponent : function() {
		var me = this;
		Ext.apply(me, {
			items : [ {
				xtype : 'idatabaseCollectionAccordion',
				project_id : me.project_id
			}, {
				xtype : 'idatabaseCollectionTabPanel',
				project_id : me.project_id
			} ]
		});
		me.callParent(arguments);
	}
});