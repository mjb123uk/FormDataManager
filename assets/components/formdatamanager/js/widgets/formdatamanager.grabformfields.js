Ext.extend(MyForm, Ext.FormPanel, {
   
	// for each form field, calls the visitorMethod with the field
		forEachField: function(visitorMethod){
		this.getForm().items.each(visitorMethod);
	}

	// lock all form fields
	,lock: function(){
		this.forEachField(function(field){
			field.disable();
		});
	}

	// unlock all form fields
	,unlock: function(){
		this.forEachField(function(field){
			field.enable();
		});
	}

});