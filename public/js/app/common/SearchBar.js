Ext.define('icc.common.SearchBar', {
	extend : 'Ext.toolbar.Toolbar',
	alias : 'widget.searchBar',
	initComponent : function() {
		Ext.apply(this,{
			items : [ {
				xtype : 'searchfield',
				hideLabel : true,
				fieldLabel : '搜索',
				labelWidth : 60,
				name : 'search',
				width : 100,
				store : this.store
			}]
		});
		this.callParent();
	}
});