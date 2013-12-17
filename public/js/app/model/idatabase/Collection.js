Ext.define('icc.model.idatabase.Collection', {
	extend : 'icc.model.common.Model',
	fields : [ {
		name : 'name',
		type : 'string'
	}, {
		name : 'alias',
		type : 'string'
	}, {
		name : 'type',
		type : 'string'
	}, {
		name : 'isTree',
		type : 'int'
	}, {
		name : 'desc',
		type : 'string'
	}, {
		name : 'orderBy',
		type : 'int'
	}, {
		name : 'plugin',
		type : 'boolean'
	}, {
		name : 'plugin_id',
		type : 'string'
	}, {
		name : 'plugin_collection_id',
		type : 'string'
	} ]
});