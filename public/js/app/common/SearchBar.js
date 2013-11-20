Ext.define('icc.common.SearchBar', {
	extend : 'Ext.toolbar.Toolbar',
	alias : 'widget.searchBar',
	initComponent : function() {
		var me = this;
		var searchRandom = Ext.Number.randomInt(10000000, 99999999);
		var searchUnique = 'Search' + searchRandom;
		Ext.apply(me,{
			items : [ {
				xtype : 'textfield',
				hideLabel : false,
				fieldLabel : '检索内容',
				labelWidth : 60,
				id : searchUnique,
				name : 'search',
				width : 200
			}, {
				xtype : 'button',
				text : '搜索',
				iconCls : 'search',
				hideLabel : true,
				handler : function() {
					me.store.proxy['extraParams']['search'] = Ext.getCmp(searchUnique).value;
					me.store.load();
				}
			} ]
		});
		me.callParent();
	}
});