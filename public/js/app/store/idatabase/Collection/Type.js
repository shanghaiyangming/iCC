Ext.define('icc.store.idatabase.Collection.Type', {
	extend : 'Ext.data.Store',
	fields : [ "name", "type" ],
	storeId : 'idatabaseCollectionType',
	data : [ {
		"name" : '专家模式',
		"type" : 'professional'
	}, {
		"name" : '专家模式',
		"type" : 'common'
	} ]
});