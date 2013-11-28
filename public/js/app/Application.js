Ext.define('icc.Application', {
    name: 'icc',

    extend: 'Ext.app.Application',

    views: [
            
    ],

    controllers: [
        // TODO: add controllers here
        'idatabase.Project',
        'idatabase.Collection',
        'idatabase.Structure',
        'idatabase.Plugin',
        'idatabase.Plugin.System'
    ],

    stores: [
        // TODO: add stores here
    ]
});
