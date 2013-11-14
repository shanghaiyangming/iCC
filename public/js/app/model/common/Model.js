Ext.define('icc.model.common.Model', {
	extend : 'Ext.data.Model',
	fields : [ {
		name : '_id',
		type : 'string',
		convert : function(value, record) {
			if (value) {
				return value['$id'];
			}
			return value;
		}
	}, {
		name : '__CREATE_TIME__',
		type : 'string',
		convert : function(value, record) {
			if (value == undefined || value == '') {
				return '';
			}
			var date = new Date();
			date.setTime(value.sec * 1000);
			return date;
		}
	}, {
		name : '__MODIFY_TIME__',
		type : 'string',
		convert : function(value, record) {
			if (value == undefined || value == '') {
				return '';
			}
			var date = new Date();
			date.setTime(value.sec * 1000);
			return date;
		}
	} ]
});