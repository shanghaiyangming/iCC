Ext.define('icc.common.Paging', {
	extend : 'Ext.toolbar.Paging',
	alias : 'widget.paging',
	displayInfo : true,
	displayMsg : '当前显示{0}至{1}项,共 {2}项',
	emptyMsg: '暂无数据',
	refreshText : '刷新',
	firstText: '首页',
	lastText: '末页',
	prevText: '上一页',
	nextText: '下一页'
});