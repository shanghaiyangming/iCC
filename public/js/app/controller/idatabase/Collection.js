Ext.define('icc.controller.idatabase.Collection', {
	extend : 'icc.controller.common.GridController',
    models : ['Project'],
    stores : ['Project'],
    views : ['idatabase.Collection','idatabase.Collection.Add','idatabase.Collection.Edit','idatabase.Collection.TabPanel'],
	controllerName : 'idatabaseProject',
	actions : {
		add : '/idatabase/project/add',
		edit : '/idatabase/project/edit',
		remove : '/idatabase/project/remove',
		save : '/idatabase/project/save'
	},
	listeners : {
		'idatabaseProject' : {
			selectionchange : function(self,selected,eOpts) {
				self.addRef([{
		            ref: 'tabPanel',
		            selector: 'idatabaseCollectionTabPanel'
		        }]);
				
				if(selected.length > 1) {
					Ext.Msg.alert('提示信息', '请勿选择多项');
					return false;
				}
	
				var record = selected[0];
				if(record) {
					var id = record.get('_id');
					var name = record.get('name');
					var panel = self.getTabPanel().getComponent(id);
					if (panel == null) {
						panel = Ext.widget('idatabaseCollection', {
							id : id,
							title : name
						});
						self.getTabPanel().add(panel);
					}
					self.getTabPanel().setActiveTab(id);
				}
			}
		}
	}
});