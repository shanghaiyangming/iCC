Ext.define('icc.model.idatabase.Key', {
	extend : 'icc.model.common.Model',
	fields : [{
            name : 'project_id',
            type : 'string'
	}, {
            name : 'name',
            type : 'string'
	}, {
            name : 'desc',
            type : 'string'
	},{
            name : 'key',
            type : 'string'
        }, {
            name : 'expire',
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
	} , {
            name : 'active',
            type : 'boolean'
	} ],
	changeName: function() {
        var __COLLECTION_ID__ = this.get('collection_id'),
            __PROJECT_ID__ = this.get('project_id');

        if(__COLLECTION_ID__!==undefined) {
        	this.set('__COLLECTION_ID__', __COLLECTION_ID__);
        }
        if(__PROJECT_ID__!==undefined) {
        	this.set('__PROJECT_ID__', __PROJECT_ID__);
        }
    }
});