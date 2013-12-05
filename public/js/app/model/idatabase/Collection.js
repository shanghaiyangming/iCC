Ext.define('icc.model.idatabase.Collection', {
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
	},{
		name : 'orderBy',
		type : 'int'
	}]
});