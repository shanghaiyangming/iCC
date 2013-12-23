Ext.define('icc.store.idatabase.Index', {
	extend: 'Ext.data.Store',
	requires : ['icc.model.idatabase.Index'],
	autoLoad: true,
	model : 'icc.model.idatabase.Index',
	proxy : {
		type : 'ajax',
		url : '/idatabase/index/index',
		extraParams : {
			
		},
		reader : {
			type : 'json',
			root : 'result',
			totalProperty : 'total'
		}
	}
});