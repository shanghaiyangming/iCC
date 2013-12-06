Ext.define('icc.view.idatabase.Structure.FieldCombobox', {
	extend : 'icc.common.Combobox',
	alias : 'widget.idatabaseStructureFieldCombobox',
	fieldLabel : '字段列表',
	name : 'structure',
	store : 'idatabase.Structure',
	valueField : 'field',
	displayField : 'label',
	queryMode : 'remote',
	editable : false,
	typeAhead : false,
	initComponent : function() {
		var store = Ext.create('icc.store.idatabase.Structure');
		store.proxy.extraParams['project_id'] = this.project_id;
		store.proxy.extraParams['collection_id'] = this.collection_id;
		store.load();
		
		Ext.apply(this,{
			store : store
		});
		
		this.callParent();
	}
});
