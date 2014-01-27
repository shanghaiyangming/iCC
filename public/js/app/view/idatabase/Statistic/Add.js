Ext.define('icc.view.idatabase.Statistic.Add', {
	extend : 'icc.common.Window',
	alias : 'widget.idatabaseStatisticAdd',
	title : '添加统计',
	initComponent : function() {
		Ext.apply(this, {
			items : [ {
				xtype : 'iform',
				url : '/idatabase/statistic/add',
				fieldDefaults : {
					labelAlign : 'left',
					labelWidth : 150,
					anchor : '100%'
				},
				items : [ {
					xtype : 'hiddenfield',
					name : '__PROJECT_ID__',
					fieldLabel : '项目编号',
					allowBlank : false,
					value : this.__PROJECT_ID__
				}, {
					xtype : 'hiddenfield',
					name : '__COLLECTION_ID__',
					fieldLabel : '集合编号',
					allowBlank : false,
					value : this.__COLLECTION_ID__
				}, {
					name : 'name',
					fieldLabel : '统计名称',
					allowBlank : false
				}, {
					xtype : 'numberfield',
					name : 'interval',
					fieldLabel : '执行间隔',
					minValue : 300,
					maxValue : 86400,
					value : 300
				}, {
					xtype : 'idatabaseStatisticComboboxSeries'
				}, {
					xtype : 'fieldset',
					title : '柱状图/线形图',
					collapsed : false,
					collapsible : true,
					items : [ {
						xtype : 'fieldset',
						title : 'Y轴设定(纵向)',
						collapsed : false,
						collapsible : true,
						items : [ {
							xtype : 'textfield',
							name : 'yAxisTitle',
							fieldLabel : 'Y轴名称',
							allowBlank : true
						}, {
							xtype : 'idatabaseStatisticComboboxMethod',
							name : 'yAxisType',
							fieldLabel : 'Y轴统计方法',
							allowBlank : true
						}, {
							xtype : 'idatabaseStructureFieldCombobox',
							name : 'yAxisField',
							fieldLabel : 'Y轴统计属性',
							allowBlank : true,
							__PROJECT_ID__ : this.__PROJECT_ID__,
							__COLLECTION_ID__ : this.__COLLECTION_ID__
						} ]
					}, {
						xtype : 'fieldset',
						title : 'X轴设定(横向)',
						collapsed : false,
						collapsible : true,
						items : [ {
							xtype : 'textfield',
							name : 'xAxisTitle',
							fieldLabel : 'X轴名称',
							allowBlank : true
						}, {
							xtype : 'idatabaseStatisticComboboxType',
							name : 'xAxisType',
							fieldLabel : 'X轴统计类型',
							allowBlank : true
						}, {
							xtype : 'idatabaseStructureFieldCombobox',
							name : 'xAxisField',
							fieldLabel : 'X轴统计属性',
							allowBlank : true,
							__PROJECT_ID__ : this.__PROJECT_ID__,
							__COLLECTION_ID__ : this.__COLLECTION_ID__
						} ]
					} ]
				}, {
					xtype : 'fieldset',
					title : '饼状图',
					collapsed : false,
					collapsible : true,
					items : [ {
						xtype : 'idatabaseStructureFieldCombobox',
						name : 'seriesField',
						fieldLabel : '统计属性',
						allowBlank : true,
						__PROJECT_ID__ : this.__PROJECT_ID__,
						__COLLECTION_ID__ : this.__COLLECTION_ID__
					} ]
				} ]
			} ]
		});

		this.callParent();
	}

});