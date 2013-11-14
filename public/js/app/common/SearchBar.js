Ext.define('icc.common.SearchBar',{
	extend: 'Ext.toolbar.Toolbar',
	alias : 'widget.tbar',
	items: [
		{
			xtype : 'textfield',
			hideLabel : false,
			fieldLabel:'项目名称',
			labelWidth:60,
			id : 'Search',
			name:'search',
			width : 200
		},{
			xtype : 'button',
			text : '搜索',
			iconCls : 'search',
			hideLabel : true,
			handler : function() {
				var search = Ext.getCmp('Search').value;
				self.store.proxy.extraParams = {
					search:search
				};
				self.store.load();
			}
		}
	]
});