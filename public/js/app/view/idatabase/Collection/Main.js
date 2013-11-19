Ext.define('icc.view.Collection.Main', {
    extend: 'Ext.container.Container',
    requires:[
        'Ext.layout.container.Border',
        'icc.view.idatabase.Collection.Grid',
        'icc.view.idatabase.Collection.TabPanel',
    ],
    
    xtype: 'idatabaseCollectionMain',

    layout: {
        type: 'border'
    },

    items: [{
    	xtype : 'idatabaseCollectionGrid'
    },{
        xtype : 'idatabaseCollectionTabPanel'
    }]
});