/*
ModFormDataManager.viewdatagrid = function(config) {
	config=config || {};
	Ext.applyIf(config,{
		id:'mod-formdatamanager-viewdatagrid'
		,url: ModFormDataManager.config.connector_url
		,baseParams:{
			action:'getviewdata'
			,formid: ModFormDataManager.config.formid
			,formname: ModFormDataManager.config.formname
			,layoutid: ModFormDataManager.config.layoutid
		}
		,cm: viewdataColumnModel
		,fields: viewdataFields
		,paging: true
	});
	ModFormDataManager.viewdatagrid.superclass.constructor.call(this,config);
};
Ext.extend(ModFormDataManager.viewdatagrid,MODx.grid.Grid);
Ext.reg('mod-formdatamanager-viewdatagrid',ModFormDataManager.viewdatagrid);
*/


Ext.onReady(function () {

	var store = new Ext.data.JsonStore({
		url: ModFormDataManager.config.connector_url
		,baseParams:{
			action:'getviewdata'
			,formid: ModFormDataManager.config.formid
			,formname: ModFormDataManager.config.formname
			,layoutid: ModFormDataManager.config.layoutid
			,selectionfield: ModFormDataManager.config.selectionfield
			,template: ModFormDataManager.config.template
		}
		,fields: viewdataFields
		,root: 'results'
		,totalProperty: 'total'
		,remoteSort: false
		,storeId: Ext.id()
		,autoDestroy: true
	});
	
	store.load();
	
	var paging = new Ext.PagingToolbar({
        pageSize:10,
        store: store,
        displayInfo: true,
        displayMsg: 'Displaying {0} - {1} of {2}',
        emptyMsg: "No topics to display"
    });
	
	ModFormDataManager.Grid = Ext.extend(Ext.grid.GridPanel, {
	    initComponent: function() {
	        var config = {
				id:'mod-formdatamanager-viewdatagrid'
	            ,store: store
				,cm: viewdataColumnModel
				,bbar:paging
	            ,viewConfig: {
	                //forceFit: true
	            }
	            //,height: 500
	            ,height: fdmgh
	            ,stripeRows: true
	        };
	
	        // apply config
	        Ext.apply(this, Ext.apply(this.initialConfig, config));
	
	        // call parent
	        ModFormDataManager.Grid.superclass.initComponent.apply(this, arguments);
	    }
	});
	
	Ext.reg('mod-formdatamanager-viewdatagrid', ModFormDataManager.Grid);
    
});
