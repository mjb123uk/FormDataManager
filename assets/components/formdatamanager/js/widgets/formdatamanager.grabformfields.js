Ext.extend(MyForm, Ext.FormPanel, {
	dfa = {id => '', type => '', props => []},
	
	// for each form field, calls the visitorMethod with the field
	forEachField: function(visitorMethod){
		this.getForm().items.each(visitorMethod);
	}
	
	// get details of all form fields
	,getFD: function(){
		var fs = [];
		var fc = 0;
		this.forEachField(function(field){
			var fd = this.dfa;
			fd['id'] = field.id;
			fd['type'] = field.xtype;
			fd['props'] = field.attributes;
			fds[fc] = fd;
			fc += 1;
		});
		return fds;
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