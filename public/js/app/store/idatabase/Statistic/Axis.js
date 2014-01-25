Ext.define('icc.store.Statistic.Axis', {
	extend : 'Ext.data.Store',
	fields : [ "name", "value" ],
	data : [ {
		"name" : '数字范围',
		"value" : 'Numeric'
	}, {
		"name" : '类别划分',
		"value" : 'Category'
	} , {
		"name" : '时间范围',
		"value" : 'Time'
	} ]
});