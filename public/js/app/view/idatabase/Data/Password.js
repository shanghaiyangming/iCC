Ext.define('icc.view.idatabase.Data.Password', {
	extend : 'icc.common.Window',
	alias : 'widget.idatabaseDataPassword',
	title : '身份确认',
	initComponent : function() {
		var items = [ {
			xtype : 'hiddenfield',
			name : 'project_id',
			value : this.project_id,
			allowBlank : false
		}, {
			xtype : 'hiddenfield',
			name : 'collection_id',
			value : this.collection_id,
			allowBlank : false
		}, {
			name : 'password',
			fieldLabel : '登录密码',
			allowBlank : false
		} ];

		Ext.apply(this, {
			items : [ {
				xtype : 'iform',
				url : '/idatabase/data/drop',
				items : items
			} ]
		});

		this.callParent();
	}
});