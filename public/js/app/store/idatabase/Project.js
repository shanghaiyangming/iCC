Ext.define('icc.store.idatabase.Project', {
	extend: 'Ext.data.Store',
	storeId:'idatabaseProject',
	autoLoad: true,
	model : 'icc.model.idatabase.Project',
	proxy : {
		type : 'ajax',
		url : '/idatabase/project/index',
		extraParams : {
			
		},
		reader : {
			type : 'json',
			root : 'result',
			totalProperty : 'total'
		}
	}
});