Ext.define('icc.view.idatabase.Data.Field.boolfield', {
	extend : 'Ext.form.FieldContainer',
	alias : 'widget.boolfield',
	fieldLabel : '是否选择boolean',
	name : 'booleanName',
	defaultType : 'radiofield',
	defaults : {
		flex : 1
	},
	layout : 'hbox',
	initComponent : function() {
		Ext.apply(this, {
			items : [ {
				boxLabel : '是',
				name : this.name,
				inputValue : true
			}, {
				boxLabel : '否',
				name : this.name,
				inputValue : false
			} ]
		});
		this.callParent();
	}
});