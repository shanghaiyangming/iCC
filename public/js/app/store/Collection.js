Ext.define('icc.store.Collection', {
	extend: 'Ext.data.Store',
	storeId:'Collection',
	autoLoad: false,
	model : 'icc.model.Collection',
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