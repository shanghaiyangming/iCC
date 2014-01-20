Ext.define('icc.view.idatabase.Key.Edit', {
	extend : 'icc.common.Window',
	alias : 'widget.idatabaseKeyEdit',
	title : '编辑密钥',
	initComponent : function() {
		this.items = [ {
			xtype : 'iform',
			url : '/idatabase/key/edit',
			items : [ {
				xtype : 'hiddenfield',
				name : '_id',
				fieldLabel : '密钥编号',
				allowBlank : false
			}, {
				xtype : 'hiddenfield',
				name : '__PROJECT_ID__',
				fieldLabel : '项目编号',
				allowBlank : false,
				value : this.__PROJECT_ID__
			}, {
				name : 'name',
				fieldLabel : '密钥名称',
				allowBlank : false
			}, {
				name : 'desc',
				fieldLabel : '密钥描述',
				allowBlank : false
			}, {
				name : 'key',
				fieldLabel : '密钥',
				allowBlank : false
			}, {
				xtype : 'datefield',
				name : 'expire',
				fieldLabel : '过期时间',
				allowBlank : false,
				format : 'Y-m-d H:i:s'
			}, {
				xtype : 'radiogroup',
				fieldLabel : '有效性',
				defaultType : 'radiofield',
				layout : 'hbox',
				items : [ {
					boxLabel : '是',
					name : 'active',
					inputValue : true,
					checked : true
				}, {
					boxLabel : '否',
					name : 'active',
					inputValue : false
				} ]
			} ]
		} ];

		this.callParent();
	}

});