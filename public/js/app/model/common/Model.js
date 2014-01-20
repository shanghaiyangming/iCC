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
			if(Ext.isObject(value) && value['sec']!=undefined) {
			    var date = new Date();
			    date.setTime(value.sec * 1000);
			    return date;
			}
			else {
			    return value;
			}
		}
	}, {
		name : '__MODIFY_TIME__',
		type : 'string',
		convert : function(value, record) {
			if(Ext.isObject(value) && value['sec']!=undefined) {
			    var date = new Date();
			    date.setTime(value.sec * 1000);
			    return date;
			}
			else {
			    return value;
			}
		}
	} ],
	changeName: function() {
        var __COLLECTION_ID__ = this.get('collection_id'),
            __PROJECT_ID__ = this.get('project_id');

        if(__COLLECTION_ID__!==undefined && __COLLECTION_ID__!==null) {
        	this.set('__COLLECTION_ID__', __COLLECTION_ID__);
        }
        if(__PROJECT_ID__!==undefined && __PROJECT_ID__!==null) {
        	this.set('__PROJECT_ID__', __PROJECT_ID__);
        }
    }
});