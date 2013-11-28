Ext.define('icc.view.idatabase.Collection.Combobox', {
	extend : 'icc.common.Combobox',
	alias : 'widget.idatabaseCollectionCombobox',
	fieldLabel : '集合列表',
	name : 'type',
	store : 'idatabase.Collection.Type',
	valueField : 'type',
	displayField : 'name',
	queryMode : 'local',
	pageSize : 0,
	editable : false,
	typeAhead : false
});
