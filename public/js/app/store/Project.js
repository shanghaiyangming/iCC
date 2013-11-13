Ext.define('icc.store.Project', {
	extend: 'Ext.data.Store',
	autoLoad: true,
	model : 'icc.model.Project',
	proxy : {
		type : 'ajax',
		url : '/admin/menu/allrootmenu',
		reader : {
			type : 'json',
			root : 'result',
			totalProperty : 'total'
		}
	}
});