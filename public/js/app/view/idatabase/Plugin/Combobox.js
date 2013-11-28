Ext.define('icc.view.idatabase.Plugin.Combobox', {
	extend : 'icc.common.Combobox',
	alias : 'widget.idatabasePluginCombobox',
	fieldLabel : '系统插件',
	name : 'plugin_id',
	store : 'idatabase.Plugin.System',
	valueField : '_id',
	displayField : 'name',
	queryMode : 'remote',
	pageSize : 0,
	editable : false,
	typeAhead : false
});
