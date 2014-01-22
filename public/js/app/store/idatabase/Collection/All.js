Ext.define('icc.store.idatabase.Collection.All', {
	extend: 'Ext.data.Store',
	autoLoad: false,
	model : 'icc.model.idatabase.Collection',
	proxy : {
		type : 'ajax',
		url : '/idatabase/collection/all',
		extraParams : {
			
		},
		reader : {
			type : 'json',
			root : 'result',
			totalProperty : 'total'
		}
	}
});