Ext.define('icc.controller.idatabase.Project', {
    extend : 'icc.controller.common.GridController',
    models : [],
    stores : [],
    views : ['idatabase.Project.Add','idatabase.Project.Edit','idatabase.Project.TabPanel'],
    controllerName : 'idatabaseProject',
    actions:{
    	add : '/idatabase/project/add',
    	edit : '/idatabase/project/edit',
    	remove : '/idatabase/project/remove',
    	save : '/idatabase/project/save'
    }
});