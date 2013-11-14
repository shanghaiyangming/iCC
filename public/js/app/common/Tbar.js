Ext.define('icc.common.Tbar',{
	extend: 'Ext.toolbar.Toolbar',
	alias : 'widget.tbar',
	items: [
		{
			text : '新增',
			width : 60,
			iconCls : 'add',
			action : 'add'
		}, '-', {
			text : '编辑',
			width : 60,
			iconCls : 'edit',
			action : 'edit'
		}, '-', {
			text : '保存',
			  iconCls : 'save',
			width : 60,
			tooltip : '保存',
			action : 'save'
		}, '->', {
			text : '删除',
			width : 60,
			iconCls : 'remove',
			tooltip : '删除',
			action : 'remove'
		}
	]
});