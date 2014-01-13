Ext.define('icc.model.idatabase.Structure', {
	extend : 'icc.model.common.Model',
	fields : [{
		name : 'collection_id',
		type : 'string'
	},{
		name : 'plugin_id',
		type : 'string'
	},{
		name : 'plugin_collection_id',
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
		name : 'filter',
		type : 'int'
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
		name : 'isFatherField',
		type : 'boolean'
	},{
		name : 'rshCollection',
		type : 'string'
	},{
		name : 'rshKey',
		type : 'boolean'
	},{
		name : 'rshValue',
		type : 'boolean'
	},{
		name : 'rshCollectionDisplayField',
		type : 'string'
	},{
		name : 'rshCollectionValueField',
		type : 'string'
	},{
		name : 'showImage',
		type : 'boolean'
	},{
		name : 'orderBy',
		type : 'int'
	},{
		name : 'isQuick',
		type : 'boolean'
	},{
		name : 'quickTargetCollection',
		type : 'string'
	},{
		name : 'quickSearchCondition',
		type : 'string'
	}]
});