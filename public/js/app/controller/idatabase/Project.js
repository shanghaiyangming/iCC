Ext.define('icc.controller.idatabase.Project', {
	extend : 'icc.controller.common.GridController',
    models : ['Project','Collection'],
    stores : ['Project','Collection','Collection.Type'],
    views : ['idatabase.Project','idatabase.Project.Add','idatabase.Project.Edit','idatabase.Project.TabPanel','idatabase.Collection'],
	controllerName : 'idatabaseProject',
	actions : {
		add : '/idatabase/project/add',
		edit : '/idatabase/project/edit',
		remove : '/idatabase/project/remove',
		save : '/idatabase/project/save'
	},
	refs : [{
        ref: 'tabPanel',
        selector: 'idatabaseProjectTabPanel'
    }],
	initListeners : {
		'idatabaseProject' : {
			selectionchange : function(selectionModel,selected,eOpts) {

				if(selected.length > 1) {
					Ext.Msg.alert('提示信息', '请勿选择多项');
					return false;
				}
	
				var record = selected[0];
				if(record) {
					var id = record.get('_id');
					var name = record.get('name');
					var panel = this.getTabPanel().getComponent(id);
					if (panel == null) {
						panel = Ext.widget('idatabaseCollectionMain', {
							id : id,
							title : name,
							project_id : id
						});
						this.getTabPanel().add(panel);
					}
					this.getTabPanel().setActiveTab(id);
				}
			}
		}
	}
});