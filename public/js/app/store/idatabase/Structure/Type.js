Ext.define('icc.store.idatabase.Structure.Type', {
	extend : 'Ext.data.Store',
	fields : [ "name", "value" ],
	data : [ {
		"name" : '单行文字输入框',
		"value" : 'textfield'
	}, {
		"name" : '多行文本输入框',
		"value" : 'textareafield'
	}, {
		"name" : '数字输入框',
		"value" : 'numberfield'
	}, {
		"name" : '富文本编辑器',
		"value" : 'htmleditor'
	}, {
		"name" : '日期控件',
		"value" : 'datefield'
	}, {
		"name" : '文件上传控件',
		"value" : 'filefield'
	}, {
		"name" : '二维坐标输入框(地球经纬度)',
		"value" : '2dfield'
	} ]
});