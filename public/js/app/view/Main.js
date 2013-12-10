Ext.define('icc.view.Main', {
    extend: 'Ext.container.Container',
    requires:[
        'Ext.tab.Panel',
        'Ext.layout.container.Border',
        'icc.view.idatabase.Project.Grid',
        'icc.common.Form',
        'icc.common.Paging',
        'icc.common.Tbar',
        'icc.common.Window',
        'icc.common.SearchBar',
        'icc.common.Combobox',
        'icc.ux.form.HtmlEditor.imageUpload',
        'icc.ux.form.HtmlEditor.ImageCropDialog',
        'icc.ux.form.HtmlEditor.ImageDialog'
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