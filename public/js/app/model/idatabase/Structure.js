Ext.define('icc.model.idatabase.Structure', {
	extend: 'icc.model.common.Model',
	fields: [{
		name: 'collection_id',
		type: 'string'
	}, {
		name: 'plugin_id',
		type: 'string'
	}, {
		name: 'plugin_collection_id',
		type: 'string'
	}, {
		name: 'field',
		type: 'string'
	}, {
		name: 'label',
		type: 'string'
	}, {
		name: 'type',
		type: 'string'
	}, {
		name: 'filter',
		type: 'int'
	}, {
		name: 'searchable',
		type: 'boolean'
	}, {
		name: 'main',
		type: 'boolean'
	}, {
		name: 'required',
		type: 'boolean'
	}, {
		name: 'isFatherField',
		type: 'boolean'
	}, {
		name: 'rshCollection',
		type: 'string'
	}, {
		name: 'isBoxSelect',
		type: 'boolean'
	}, {
		name: 'isLinkageMenu',
		type: 'boolean'
	}, {
		name: 'linkageClearValueField',
		type: 'string'
	}, {
		name: 'linkageSetValueField',
		type: 'string'
	}, {
		name: 'rshSearchCondition',
		type: 'string',
		convert: function(value) {
			if (Ext.isObject(value) || Ext.isArray(value)) {
				return Ext.JSON.encode(value);
			} else {
				return value;
			}
		}
	}, {
		name: 'rshKey',
		type: 'boolean'
	}, {
		name: 'rshValue',
		type: 'boolean'
	}, {
		name: 'rshCollectionDisplayField',
		type: 'string'
	}, {
		name: 'rshCollectionValueField',
		type: 'string'
	}, {
		name: 'rshCollectionFatherField',
		type: 'string'
	}, {
		name: 'showImage',
		type: 'boolean'
	}, {
		name: 'orderBy',
		type: 'int'
	}, {
		name: 'isQuick',
		type: 'boolean'
	}, {
		name: 'quickTargetCollection',
		type: 'string'
	}, {
		name: 'cdnUrl',
		type: 'string'
	}, {
		name: 'xTemplate',
		type: 'string'
	}, {
		name : 'isPluginStructure',
		type : 'boolean'
	}]
});