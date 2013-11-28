Ext.define('icc.view.idatabase.Collection.Combobox', {
	extend : 'icc.common.Combobox',
	alias : 'widget.idatabaseCollectionCombobox',
	fieldLabel : '集合列表',
	store : 'idatabase.Collection',
	valueField : '_id',
	displayField : 'name',
	queryMode : 'remote',
	pageSize : 20,
	editable : false,
	typeAhead : false,
	initComponent : function() {
		var store = Ext.create('icc.store.idatabase.Collection');
		store.proxy.extraParams['project_id'] = this.project_id;
		store.load();
		
		Ext.apply(this,{
			store : store
		});
		
		this.callParent();
	}
});
