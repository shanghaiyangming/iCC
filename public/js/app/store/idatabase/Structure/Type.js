Ext.define('icc.store.idatabase.Structure.Type', {
	extend : 'Ext.data.Store',
	fields : [
		"name", "type"
	],
	data : [
		{
			"name" : '单行文字输入框',
			"type" : 'textfield'
		}, {
			"name" : '多行文本输入框',
			"type" : 'textareafield'
		}, {
			"name" : '数字输入框',
			"type" : 'numberfield'
		}, {
			"name" : '富文本编辑器',
			"type" : 'htmleditor'
		}, {
			"name" : '日期控件',
			"type" : 'datefield'
		}, {
			"name" : '文件上传控件',
			"type" : 'filefield'
		}, {
			"name" : '二维坐标输入框(地球经纬度)',
			"type" : '2dfield'
		}
	]
});