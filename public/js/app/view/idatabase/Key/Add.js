Ext.define('icc.view.idatabase.Key.Add', {
	extend : 'icc.common.Window',
	alias : 'widget.idatabaseKeyAdd',
	title : '添加密钥',
	initComponent : function() {
		Ext.apply(this, {
			items : [ {
				xtype : 'iform',
				url : '/idatabase/key/add',
				items : [ {
					xtype : 'hiddenfield',
					name : 'project_id',
					fieldLabel : '项目编号',
					allowBlank : false,
					value : this.project_id
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
			} ]
		});

		this.callParent();
	}

});