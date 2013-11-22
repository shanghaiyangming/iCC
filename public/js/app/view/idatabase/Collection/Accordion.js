Ext.define('icc.view.idatabase.Collection.Accordion', {
	extend : 'Ext.panel.Panel',
	xtype : 'idatabaseCollectionAccordion',
	region : 'west',
	layout : 'accordion',
	width : 400,
	defaults : {
		bodyPadding : 0
	},
	initComponent : function() {
		var items = [{
			xtype : 'idatabaseCollectionGrid',
			project_id : this.project_id
		},{
			xtype : 'idatabaseCollectionGrid',
			project_id : this.project_id
		}];

		Ext.apply(this, {
			items : items
		});
		
		this.callParent();
	}
});