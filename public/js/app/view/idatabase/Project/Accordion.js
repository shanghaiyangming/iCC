Ext.define('icc.view.idatabase.Project.Accordion', {
	extend : 'Ext.panel.Panel',
	xtype : 'idatabaseProjectAccordion',
	region : 'west',
	layout : 'accordion',
	width : 400,
	defaults : {
		bodyPadding : 0
	},
	initComponent : function() {
		var items = [{
			xtype : 'idatabaseProjectGrid'
		},{
			xtype : 'idatabaseAccountGrid',
			project_id : this.project_id
		}];

		Ext.apply(this, {
			items : items
		});
		
		this.callParent();
	}
});