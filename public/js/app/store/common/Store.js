Ext.define('icc.store.common.Store', {
	extend: 'Ext.data.Store',
	autoLoad: false,
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