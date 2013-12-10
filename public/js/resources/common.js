if (typeof console == "undefined" || typeof console.log == "undefined") {
	var console = {
		log : function() {
			return false;
		},
		info : function() {
			return false;
		}
	};
}

Ext.onReady(function(){
	Ext.require(['Ext.data.proxy.Ajax','Ext.form.field.ComboBox'],function(){
		Ext.override(Ext.data.proxy.Ajax, { timeout:600000 });

		//解决form.loadRecord(rec)中combobox不能自动加载选择的问题,只能处理字符串型，无法处理_id等对象型的数据
		Ext.form.field.ComboBox.override({
		    setValue: function(v) {
		        if(!this.store.isLoaded && this.queryMode == 'remote') {
		        	if(typeof(v)==='string'||typeof(v)==='number') {
	        			if(this.store.proxy.type=='ajax') {
	        				this.store.proxy.extraParams.idbComboboxSelectedValue = v;
	        			}
		        		try {
			                this.store.addListener('load', function() {
			                	try {
				                    this.store.isLoaded = true;
				                    this.setValue(v);
			                	}
			                	catch(e) {
			                		console.info(this,e);
			                	}
			               }, this);
			               this.store.load();
		        		}
		        		catch(e) { console.info(e);}
		        	}
		        	else {
		        		this.callOverridden(arguments);
		        	}
		        } 
		        else {
		            this.callOverridden(arguments);
		        }
		    }
		});
	});
	
});