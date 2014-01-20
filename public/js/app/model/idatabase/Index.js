Ext.define('icc.model.idatabase.Index', {
	extend : 'icc.model.common.Model',
	fields : [{
		name : '__COLLECTION_ID__',
		type : 'string'
	}, {
		name : 'plugin_id',
		type : 'string'
	}, {
		name : 'plugin_collection_id',
		type : 'string'
	}, {
		name : 'keys',
		type : 'string'
	} ]
});