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
	renderDashboard: false,
	initComponent: function() {
		Ext.apply(this, {
			items: this.items
		});
		this.callParent(arguments);
	},
	listeners: {
		afterrender: function(panel) {
			if (!panel.renderDashboard) {
				Ext.Ajax.request({
					url: '/idatabase/dashboard/index',
					params: {
						__PROJECT_ID__: panel.__PROJECT_ID__
					},
					success: function(response) {
						var result = Ext.JSON.decode(response.responseText);
						if (Ext.isArray(result)) {
							Ext.Array.forEach(result, function(items, index, allTtems) {
								if (Ext.isArray(items['__DATAS__'])) {

									var store = Ext.create('Ext.data.Store', {
										fields: ["_id", "value"],
										data: items['__DATAS__']
									});

									var chart = Ext.create('Ext.chart.Chart', {
										animate: true,
										store: store,
										shadow: true,
										legend: {
											position: 'right'
										},
										insetPadding: 60,
										theme: 'Base:gradients',
										series: [{
											type: 'pie',
											field: 'value',
											showInLegend: true,
											donut: true,
											tips: {
												trackMouse: true,
												width: 140,
												height: 28,
												renderer: function(storeItem, item) {
													var total = 0;
													store.each(function(rec) {
														total += rec.get('value');
													});
													this.setTitle(storeItem.get('_id') + ': ' + Math.round(storeItem.get('value') / total * 100, 2) + '%');
												}
											},
											highlight: {
												segment: {
													margin: 20
												}
											},
											label: {
												field: '_id',
												display: 'rotate',
												contrast: true,
												font: '18px Arial'
											}
										}]
									});
									panel.add(panel, chart);
								}
							});
							//panel.renderDashboard = true;
							//panel.doLayout();
							
						}
					}
				});
			}
		}
	}
});