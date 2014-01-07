Ext.define('icc.store.idatabase.Static', {
	extend: 'Ext.data.Store',
	requires : ['icc.model.idatabase.Static'],
	autoLoad: true,
	model : 'icc.model.idatabase.Static',
	proxy : {
		type : 'ajax',
		url : '/idatabase/static/index',
		extraParams : {
			
		},
		reader : {
			type : 'json',
			root : 'result',
			totalProperty : 'total'
		}
	}
});