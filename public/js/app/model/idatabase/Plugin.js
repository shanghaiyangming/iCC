Ext.define('icc.model.idatabase.Plugin', {
	extend : 'icc.model.common.Model',
	fields : [ {
		name : '__PLUGIN_ID__',
		type : 'string'
	}, {
		name : 'name',
		type : 'string'
	}, {
		name : 'xtype',
		type : 'string'
	}, {
		name : 'desc',
		type : 'string'
	} ]
});