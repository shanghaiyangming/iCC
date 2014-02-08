Ext.define('icc.store.idatabase.Statistic.Period', {
	extend: 'Ext.data.Store',
	fields: ["name", "value"],
	data: [{
		"name": '最近24小时',
		"value": '24hour'
	}, {
		"name": '最近48小时',
		"value": '48hour'
	}, , {
		"name": '最近72小时',
		"value": '72hour'
	}, {
		"name": '最近7天',
		"value": '7day'
	}, {
		"name": '最近30天',
		"value": '7day'
	}, {
		"name": '最近4周',
		"value": '4week'
	}, {
		"name": '最近8周',
		"value": '4week'
	}, {
		"name": '最近3月',
		"value": '3month'
	}]
});