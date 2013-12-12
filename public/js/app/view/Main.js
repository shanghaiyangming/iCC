Ext.define('icc.view.Main', {
    extend: 'Ext.container.Container',
    requires:[
        'Ext.form.field.HtmlEditor',
        'Ext.tab.Panel',
        'Ext.layout.container.Border',
        'icc.view.idatabase.Project.Grid',
        'icc.common.Form',
        'icc.common.Paging',
        'icc.common.Tbar',
        'icc.common.Window',
        'icc.common.SearchBar',
        'icc.common.Combobox'
    ],
    
    xtype: 'app-main',

    layout: {
        type: 'border'
    },

    items: [{
    	xtype : 'idatabaseProjectGrid'
    },{
        xtype : 'idatabaseProjectTabPanel'
    }]
});