Ext.define('icc.view.common.Combobox', {
	extend : 'Ext.form.field.ComboBox',
	alias : 'widget.iccViewCommonCombobox',
	name : '',
	fieldLabel : '',
	store : '',
	queryMode : 'remote',
	forceSelection : true,
	editable : true,
	pageSize : 10,
	queryParam : 'search',
	typeAhead : true,
	valueField : '_id',
	displayField : '',
	allowBlank : true
});