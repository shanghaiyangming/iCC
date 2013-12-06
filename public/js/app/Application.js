Ext.define('icc.Application', {
    name: 'icc',

    extend: 'Ext.app.Application',

    views: [
        'common.Combobox.Boolean'
    ],

    controllers: [
        // TODO: add controllers here
        'idatabase.Project',
        'idatabase.Collection',
        'idatabase.Collection.Order',
        'idatabase.Structure',
        'idatabase.Plugin',
        'idatabase.Plugin.System',
        'idatabase.Data'
    ],

    stores: [
        // TODO: add stores here
        'common.Boolean'
    ]
});
