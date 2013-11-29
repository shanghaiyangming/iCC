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
		type : 'boolean'
	},{
		name : 'main',
		type : 'boolean'
	},{
		name : 'required',
		type : 'boolean'
	},{
		name : 'rshCollection',
		type : 'string'
	},{
		name : 'rshType',
		type : 'string'
	},{
		name : 'rshKey',
		type : 'boolean'
	},{
		name : 'rshValue',
		type : 'boolean'
	},{
		name : 'showImage',
		type : 'boolean'
	},{
		name : 'orderBy',
		type : 'int'
	}]
});