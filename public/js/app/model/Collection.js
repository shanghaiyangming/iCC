Ext.define('icc.model.Collection', {
	extend : 'icc.model.common.Model',
	fields : [{
		name : 'name',
		type : 'string'
	},{
		name : 'alias',
		type : 'string'
	},{
		name : 'type',
		type : 'string'
	},{
		name : 'desc',
		type : 'string'
	}]
});