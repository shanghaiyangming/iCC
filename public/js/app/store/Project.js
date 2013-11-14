Ext.define('icc.store.Project', {
	extend: 'Ext.data.Store',
	storeId:'Project',
	autoLoad: true,
	model : 'icc.model.Project',
	proxy : {
		type : 'ajax',
		url : '/idatabase/project/index',
		reader : {
			type : 'json',
			root : 'result',
			totalProperty : 'total'
		}
	}
});