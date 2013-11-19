Ext.define('icc.view.Main', {
    extend: 'Ext.container.Container',
    requires:[
        'Ext.tab.Panel',
        'Ext.layout.container.Border',
        'icc.view.idatabase.Project.Grid'
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