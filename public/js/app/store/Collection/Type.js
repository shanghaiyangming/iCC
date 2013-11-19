Ext.define('icc.store.Collection.Type', {
	extend : 'Ext.data.Store',
	fields : [ "name", "type" ],
	storeId : 'CollectionType',
	data : [ {
		"name" : '专家模式',
		"type" : 'professional'
	}, {
		"name" : '专家模式',
		"type" : 'common'
	} ]
});