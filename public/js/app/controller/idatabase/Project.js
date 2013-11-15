Ext.define('icc.controller.idatabase.Project', {
	extend : 'icc.controller.common.GridController',
    models : ['Project'],
    stores : ['Project'],
    views : ['idatabase.Project','idatabase.Project.Add','idatabase.Project.Edit','idatabase.Project.TabPanel'],
	controllerName : 'idatabaseProject',
	actions : {
		add : '/idatabase/project/add',
		edit : '/idatabase/project/edit',
		remove : '/idatabase/project/remove',
		save : '/idatabase/project/save'
	},
	listeners : {
		'idatabaseProject' : {
			selectionchange : function( this, selected, eOpts) {
				this.addRef([{
		            ref: 'tabPanel',
		            selector: 'idatabaseProjectTabPanel'
		        }]);
				
				if(selected.length > 1) {
					Ext.Msg.alert('提示信息', '请勿选择多项');
					return false;
				}
	
				var record = selected[0];
				if(record) {
					var id = record.get('_id');
					var name = record.get('name')
					var panel = this.getTabPanel().getComponent(id);
					if (panel == null) {
						panel = Ext.widget('idatabaseCollection', {
							id : id,
							title : name
						});
						this.getTabPanel().add(panel);
					}
					this.getTabPanel().setActiveTab(id);
				}
			}
		}
	}
});