Ext.define('icc.store.idatabase.Statistic', {
	extend: 'Ext.data.Store',
	requires: ['icc.model.idatabase.Statistic'],
	autoLoad: true,
	model: 'icc.model.idatabase.Static',
	proxy: {
		type: 'ajax',
		url: '/idatabase/statistic/index',
		extraParams: {

		},
		reader: {
			type: 'json',
			root: 'result',
			totalProperty: 'total'
		}
	}
});