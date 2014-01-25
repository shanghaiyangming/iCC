Ext.define('icc.view.idatabase.Statistic.Series', {
	extend : 'icc.common.Combobox',
	alias : 'widget.idatabaseStatisticSeries',
	fieldLabel : '统计类型',
	name : 'type',
	store : 'idatabase.Statistic.Series',
	valueField : 'value',
	displayField : 'name',
	queryMode : 'remote',
	editable : false,
	typeAhead : false
});
