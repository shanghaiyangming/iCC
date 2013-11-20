Ext.define('icc.store.idatabase.Collection', {
	extend: 'Ext.data.Store',
	storeId:'idatabaseCollection',
	autoLoad: false,
	model : 'icc.model.idatabase.Collection',
	proxy : {
		type : 'ajax',
		url : '/idatabase/collection/index',
		extraParams : {
			
		},
		reader : {
			type : 'json',
			root : 'result',
			totalProperty : 'total'
		}
	}
});