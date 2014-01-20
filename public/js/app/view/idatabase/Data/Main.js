Ext.define('icc.view.idatabase.Data.Main', {
	extend : 'Ext.panel.Panel',
	requires : [],
	alias : 'widget.idatabaseDataMain',
	closable : true,
	layout : {
		type : 'border'
	},
	initComponent : function() {
		var plugins = [ Ext.create('Ext.grid.plugin.CellEditing', {
			clicksToEdit : 2
		}) ];

		if (this.isRowExpander) {
			plugins.push(Ext.create('Ext.grid.plugin.RowExpander', {
				rowBodyTpl : new Ext.XTemplate(this.rowBodyTpl),
				expandOnEnter : false,
				expandOnDblClick : false
			}));
		}

		if (this.isTree) {
			plugins.push({
				ptype : 'bufferedrenderer'
			});
			Ext.apply(this, {
				items : [ {
					xtype : 'idatabaseDataTreeGrid',
					__PROJECT_ID__ : this.__PROJECT_ID__,
					collection_id : this.collection_id,
					columns : this.gridColumns,
					store : this.gridStore,
					addOrEditFields : this.addOrEditFields,
					selType: 'checkboxmodel',
					plugins : plugins
				} ]
			});
		} else {
			Ext.apply(this, {
				items : [ {
					xtype : 'idatabaseDataGrid',
					__PROJECT_ID__ : this.__PROJECT_ID__,
					collection_id : this.collection_id,
					columns : this.gridColumns,
					store : this.gridStore,
					addOrEditFields : this.addOrEditFields,
					selType: 'checkboxmodel',
					plugins : plugins
				}, {
					xtype : 'idatabaseDataSearch',
					__PROJECT_ID__ : this.__PROJECT_ID__,
					collection_id : this.collection_id,
					searchFields : this.searchFields
				} ]
			});
		}
		this.callParent(arguments);
	}
});
