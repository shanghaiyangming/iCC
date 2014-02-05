Ext.define('icc.view.idatabase.Statistic.Chart', {
	extend: 'icc.common.Window',
	alias: 'widget.idatabaseStatisticChart',
	title: '统计结果',
	initComponent: function() {
		var statistics = this.__STATISTIC_INFO__;
		var me = this;

		var statistics_id = statistics.get('_id');
		var type = {
			sum: '求和',
			avg: '均值',
			count: '计数',
			max: '最大值',
			min: '最小值',
			unique: '唯一值',
			median: '中位数',
			variance: '方差',
			standard: '标准差'
		};

		var store = new Ext.data.Store({
			fields: [{
				name: '_id',
				type: 'auto',
				convert: function(value, record) {
					if (Ext.isArray(value)) {
						return value.join('.');
					}
					return value;
				}
			}, {
				name: 'value',
				type: 'auto'
			}],
			autoLoad: false,
			proxy: {
				type: 'ajax',
				url: '/idatabase/data/statistics',
				timeout: 300000,
				extraParams: {
					_id: statistics_id
				},
				reader: {
					type: 'json',
					root: 'result',
					totalProperty: 'total'
				}
			}
		});

		var items = {
			xtype: 'chart',
			style: 'background:#fff',
			store: store,
			title: statistics.get('name'),
			axes: [{
				type: 'Numeric',
				minimum: 0,
				position: 'left',
				fields: ['value'],
				title: statistics.get('yAxisTitle'),
				minorTickSteps: 1,
				grid: {
					odd: {
						opacity: 1,
						fill: '#ddd',
						stroke: '#bbb',
						'stroke-width': 0.5
					}
				}
			}, {
				type: 'Category',
				position: 'bottom',
				fields: ['_id'],
				title: statistics.get('xAxis')
			}],
			series: [{
				type: statistics.get('seriesType'),
				axis: 'left',
				highlight: false,
				xField: '_id',
				yField: 'value',
				tips: {
					trackMouse: true,
					width: 300,
					height: 30,
					renderer: function(storeItem, item) {
						this.setTitle(storeItem.get('_id') + '的' + type[statistics.get('yAxisType')] + '为' + storeItem.get('value'));
					}
				}
			}]
		};

		Ext.apply(this, {
			items: items
		});

		this.callParent(arguments);
	},
	listeners: {
		afterrender: function(win) {
			var mask = new Ext.LoadMask(win, {
				autoShow: true,
				msg: "统计中...",
				useMsg: true
			});
/*
			var chart = win.down('chart');
			chart.store.load(function() {
				mask.hide();
			});
			*/

		}
	}
});