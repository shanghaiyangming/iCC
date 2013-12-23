Ext.define('icc.view.idatabase.Index.Add', {
	extend : 'icc.common.Window',
	alias : 'widget.idatabaseIndexAdd',
	title : '添加索引',
	initComponent : function() {
		Ext.apply(this, {
			items : [ {
				xtype : 'iform',
				url : '/idatabase/index/add',
				fieldDefaults : {
					labelAlign : 'left',
					labelWidth : 150,
					anchor : '100%'
				},
				items : [ {
					xtype : 'hiddenfield',
					name : 'project_id',
					fieldLabel : '项目编号',
					allowBlank : false,
					value : this.project_id
				}, {
					xtype : 'hiddenfield',
					name : 'collection_id',
					fieldLabel : '集合编号',
					allowBlank : false,
					value : this.collection_id
				}, {
					xtype : 'textareafield',
					name : 'keys',
					fieldLabel : '索引条件',
					allowBlank : false
				}]
			} ]
		});

		this.callParent();
	}

});