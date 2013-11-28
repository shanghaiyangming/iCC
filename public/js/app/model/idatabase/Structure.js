Ext.define('icc.model.idatabase.Structure', {
	extend : 'icc.model.common.Model',
	fields : [{
		name : 'collection_id',
		type : 'string'
	},{
		name : 'field',
		type : 'string'
	},{
		name : 'label',
		type : 'string'
	},{
		name : 'type',
		type : 'string'
	},{
		name : 'searchable',
		type : 'bool'
	},{
		name : 'main',
		type : 'bool'
	},{
		name : 'required',
		type : 'bool'
	},{
		name : 'rshForm',
		type : 'string'
	},{
		name : 'rshType',
		type : 'string'
	},{
		name : 'rshKey',
		type : 'string'
	},{
		name : 'rshValue',
		type : 'string'
	},{
		name : 'showImage',
		type : 'bool'
	},{
		name : 'orderBy',
		type : 'int'
	}]
});