Ext.define('icc.model.idatabase.Collection.Order', {
	extend : 'icc.model.common.Model',
	fields : [ {
		name : 'field',
		type : 'string'
	}, {
		name : 'order',
		type : 'int'
	}, {
		name : 'priority',
		type : 'int'
	}, {
		name : '__COLLECTION_ID__',
		type : 'string'
	} ]
});