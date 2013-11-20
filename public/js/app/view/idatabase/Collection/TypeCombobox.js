Ext.define('icc.view.idatabase.Collection.TypeCombobox', {
	extend : 'icc.view.common.Combobox',
	alias : 'widget.idatabaseCollectionTypeCombobox',
	fieldLabel : '针对用户类型',
	name : 'type',
	store : 'idatabase.Collection.Type',
	valueField : 'type',
	displayField : 'name',
	queryMode : 'local',
	pageSize : 0,
	editable : false,
	typeAhead : false
});
