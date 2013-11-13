Ext.define('icc.view.Main', {
    extend: 'Ext.container.Container',
    requires:[
        'Ext.tab.Panel',
        'Ext.layout.container.Border',
        'icc.view.idatabase.Project'
    ],
    
    xtype: 'app-main',

    layout: {
        type: 'border'
    },

    items: [{
    	xtype : 'idatabaseProject'
    },{
        region: 'center',
        xtype: 'tabpanel',
        items:[]
    }]
});