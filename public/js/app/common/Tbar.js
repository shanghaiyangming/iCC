Ext.define('icc.common.Tbar', {
	extend : 'Ext.toolbar.Toolbar',
	alias : 'widget.tbar',
	items : [ {
		text : '操作',
		iconCls : 'menu',
		width : 106,
		menu : {
			xtype : 'menu',
			plain: true,
			items : [ {
				xtype : 'button',
				text : '新增',
				iconCls : 'add',
				action : 'add'
			}, {
				xtype : 'button',
				text : '编辑',
				iconCls : 'edit',
				action : 'edit'
			}, {
				xtype : 'button',
				text : '保存',
				iconCls : 'save',
				action : 'save'
			}, {
				xtype : 'button',
				text : '删除',
				iconCls : 'remove',
				action : 'remove'
			} ]
		}
	},'-',{
		text : '管理',
		width : 106,
		iconCls : 'menu',
		menu : {
			xtype : 'menu',
			plain: true,
			items : [ {
				xtype : 'button',
				text : '用户管理',
				iconCls : 'user',
				action : 'user'
			}, {
				xtype : 'button',
				text : '密钥管理',
				iconCls : 'key',
				action : 'key'
			}, {
				xtype : 'button',
				text : '插件管理',
				iconCls : 'plugin',
				action : 'plugin'
			}]
		}
	}]
});