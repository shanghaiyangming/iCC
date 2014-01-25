Ext.define('icc.model.idatabase.Statistic', {
	extend: 'icc.model.common.Model',
	fields: [{
		name: 'name',
		type: 'string'
	}, {
		name: 'type',
		type: 'string'
	}, {
		name: 'axes',
		type: 'object'
	}, {
		name: 'series',
		type: 'object'
	}, {
		name: 'interval',
		type: 'int'
	}, {
		name: 'lastExecuteTime',
		type: 'string',
		convert: function(value, record) {
			if (Ext.isObject(value) && value['sec'] != undefined) {
				var date = new Date();
				date.setTime(value.sec * 1000);
				return date;
			} else {
				return value;
			}
		}
	}, {
		name: 'resultExpireTime',
		type: 'object',
		convert: function(value, record) {
			if (Ext.isObject(value) && value['sec'] != undefined) {
				var date = new Date();
				date.setTime(value.sec * 1000);
				return date;
			} else {
				return value;
			}
		}
	}, {
		name: 'isRunning',
		type: 'boolean'
	}]
});