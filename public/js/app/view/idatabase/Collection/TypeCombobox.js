Ext.define('icc.view.idatabase.Collection.TypeCombobox', {
	extend : 'icc.view.common.Combobox',
	alias : 'widget.idatabaseProjectCombobox',
	name : 'type',
	fieldLabel : '针对用户类型',
	store : 'CollectionType',
	valueField : 'type',
	displayField : 'name'
});