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
	Ext.require(['Ext.data.proxy.Ajax','Ext.form.field.ComboBox','Ext.form.field.VTypes'],function(){
		Ext.override('Ext.data.proxy.Ajax', { timeout:60000 });
		/*Ext.override('Ext.form.action.Submit',{waitTitle :'系统提示',waitMsg:'数据处理中，请稍后……'});*/
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