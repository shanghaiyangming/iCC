Ext.define('icc.view.idatabase.Statistic.Add', {
	extend: 'icc.common.Window',
	alias: 'widget.idatabaseStatisticAdd',
	title: '添加统计',
	initComponent: function() {
		Ext.apply(this, {
			items: [{
				xtype: 'iform',
				url: '/idatabase/statistic/add',
				fieldDefaults: {
					labelAlign: 'left',
					labelWidth: 150,
					anchor: '100%'
				},
				items: [{
					xtype: 'hiddenfield',
					name: '__PROJECT_ID__',
					fieldLabel: '项目编号',
					allowBlank: false,
					value: this.__PROJECT_ID__
				}, {
					xtype: 'hiddenfield',
					name: '__COLLECTION_ID__',
					fieldLabel: '集合编号',
					allowBlank: false,
					value: this.__COLLECTION_ID__
				}, {
					name: 'name',
					fieldLabel: '统计名称',
					allowBlank: false
				}, {
					xtype: 'numberfield',
					name: 'interval',
					fieldLabel: '执行间隔',
					minValue: 300,
					maxValue : 86400,
					value : 300
				}, {
					name: 'type',
					fieldLabel: '统计类型',
					allowBlank: false
				}]
			}]
		});

		this.callParent();
	}

});