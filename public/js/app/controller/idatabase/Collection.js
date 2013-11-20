Ext.define('icc.controller.idatabase.Collection', {
	extend : 'icc.controller.common.GridController',
    models : ['idatabase.Project'],
    stores : ['idatabase.Project','idatabase.Collection.Type'],
    views : ['idatabase.Collection.Grid','idatabase.Collection.Add','idatabase.Collection.Edit','idatabase.Collection.TabPanel','idatabase.Collection.TypeCombobox'],
	controllerName : 'idatabaseCollection',
	actions : {
		add : '/idatabase/project/add',
		edit : '/idatabase/project/edit',
		remove : '/idatabase/project/remove',
		save : '/idatabase/project/save'
	},
	refs : [],
	initListeners : {
		
	}
});