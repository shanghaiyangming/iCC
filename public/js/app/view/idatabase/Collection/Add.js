Ext.define('icc.view.idatabase.Collection.Add', {
	extend : 'icc.common.Window',
	alias : 'widget.idatabaseCollectionAdd',
	title : '添加数据集合',
	requires : [],
	layout:'border',
	initComponent: function() {
		this.items = [ {
			xtype : 'form',
			url : '/idatabase/collection/add',
			items : [{
				name : 'name',
				fieldLabel : '集合别名',
				allowBlank : false,
				vtype : 'alphanum'
			},{
				name : 'name',
				fieldLabel : '集合名称',
				allowBlank : false
			},{
				name : '集合类型',
				fieldLabel : '功能描述',
				allowBlank : false
			}, {
				name : 'desc',
				fieldLabel : '项目介绍',
				allowBlank : false
			}]
		}];
		
        this.callParent();
    }
	
});