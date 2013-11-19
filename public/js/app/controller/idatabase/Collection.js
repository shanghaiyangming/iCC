Ext.define('icc.controller.idatabase.Collection', {
	extend : 'icc.controller.common.GridController',
    models : ['Project'],
    stores : ['Project'],
    views : ['idatabase.Collection.Grid','idatabase.Collection.Add','idatabase.Collection.Edit','idatabase.Collection.TabPanel'],
	controllerName : 'idatabaseProject',
	actions : {
		add : '/idatabase/project/add',
		edit : '/idatabase/project/edit',
		remove : '/idatabase/project/remove',
		save : '/idatabase/project/save'
	}
});