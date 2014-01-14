if (typeof console == "undefined" || typeof console.log == "undefined") {
	var console = {
		log: function() {
			return false;
		},
		info: function() {
			return false;
		}
	};
}

Ext.Loader.setConfig({
	enabled: true
});
Ext.onReady(function() {
	Ext.require(['Ext.data.proxy.Ajax', 'Ext.form.field.ComboBox', 'Ext.form.field.VTypes','Ext.grid.plugin.RowExpander'], function() {
		Ext.override('Ext.data.proxy.Ajax', {
			timeout: 60000
		});
		Ext.override('Ext.form.action.Submit', {
			waitTitle: '系统提示',
			waitMsg: '数据处理中，请稍后……'
		});
		Ext.form.field.ComboBox.override({
			setValue: function(v) {
				var me = this;
				if (!this.store.isLoaded && this.queryMode == 'remote') {
					console.info(typeof(v));
					console.info(v);
					if (typeof(v) === 'string' || typeof(v) === 'number') {
						if (this.store.proxy.type == 'ajax') {
							console.log(v);
							var decode = Ext.Json.decode(v,true);
							if (decode==null) {
								this.store.proxy.extraParams.idbComboboxSelectedValue = v;
							}
							else {
								if (Ext.isArray(decode)) {
									this.store.proxy.extraParams.idbComboboxSelectedValue = decode.join(',');
								}
							}
						}
						try {
							this.store.addListener('load', function() {
								try {
									this.store.isLoaded = true;
									this.setValue(v);
								} catch (e) {
									console.info(e);
								}

								try {
									var insertRecord = {};
									insertRecord[this.displayField] = '无';
									insertRecord[this.valueField] = '';
									var r = Ext.create(this.store.model, insertRecord);
									this.store.insert(0, r);
								} catch (e) {
									console.info(e);
								}

							}, this);

							this.store.load();
						} catch (e) {
							console.info(e);
						}
					} else {
						this.callOverridden(arguments);
					}
				} else {
					this.callOverridden(arguments);
				}
			}
		});

		Ext.Ajax.on('requestcomplete', function(ajax, response) {
			var result = response.responseText;
			if (result.charAt(0) == '{') {
				var json = Ext.decode(result);
				if (json.access == 'deny') {
					Ext.Msg.alert('提示信息', json.msg);
				}
			}
		});

		Ext.Ajax.on('requestexception', function(ajax, response, options, eOpts) {
			Ext.Msg.alert('提示信息', '网络连接异常，请检查您的网络状况是否正常');
		});
/*
		Ext.override(Ext.grid.plugin.RowExpander, {
			getRowBodyFeatureData: function(record, idx, rowValues) {
				var me = this
				me.self.prototype.setupRowData.apply(me, arguments);

				if (!me.grid.ownerLockable) {
					rowValues.rowBodyColspan = rowValues.rowBodyColspan;
				}
				rowValues.rowBody = me.getRowBodyContents(record);
				rowValues.rowBodyCls = me.recordsExpanded[record.internalId] ? '' : me.rowBodyHiddenCls;
			}
		});
*/
	});

});