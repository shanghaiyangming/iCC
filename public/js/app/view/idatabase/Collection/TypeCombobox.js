Ext.define('icc.view.idatabase.Collection.TypeCombobox', {
	extend : 'icc.common.Combobox',
	alias : 'widget.idatabaseCollectionTypeCombobox',
	fieldLabel : '针对用户类型',
	name : 'type',
	store : 'idatabase.Collection.Type',
	valueField : 'val',
	displayField : 'name',
	queryMode : 'local',
	pageSize : 0,
	editable : false,
	typeAhead : false
});
