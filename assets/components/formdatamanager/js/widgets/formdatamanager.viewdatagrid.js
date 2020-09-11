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
	
	/*
	var paging = new Ext.PagingToolbar({
        pageSize:20,
        store: store,
        displayInfo: true,
        displayMsg: 'Displaying {0} - {1} of {2}',
        emptyMsg: "No records to display"
    });
	*/
	
	ModFormDataManager.Grid = Ext.extend(Ext.grid.GridPanel, {
	    initComponent: function() {
	        var config = {
				id:'mod-formdatamanager-viewdatagrid'
	            ,store: store
				,cm: viewdataColumnModel
				,paging: true
				,showPerPage: true
				//,bbar:paging
	            ,viewConfig: {
	                //forceFit: true
	            }
	            //,height: 500
	            ,height: fdmgh
	            ,stripeRows: true
	        };
			if (config.paging) {
				var pgItms = config.showPerPage ? [_('per_page')+':',{
					xtype: 'textfield'
					,cls: 'x-tbar-page-size'
					,value: config.pageSize || (parseInt(MODx.config.default_per_page) || 20)
					,listeners: {
						'change': {fn:this.onChangePerPage,scope:this}
						,'render': {fn: function(cmp) {
							new Ext.KeyMap(cmp.getEl(), {
								key: Ext.EventObject.ENTER
								,fn: this.blur
								,scope: cmp
							});
						},scope:this}
					}
				}] : [];
				if (config.pagingItems) {
					for (var i=0;i<config.pagingItems.length;i++) {
						pgItms.push(config.pagingItems[i]);
					}
				}
				Ext.applyIf(config,{
					bbar: new Ext.PagingToolbar({
						pageSize: config.pageSize || (parseInt(MODx.config.default_per_page) || 20)
						,store: store
						,displayInfo: true
						,items: pgItms
					})
				});
			}
	
	        // apply config
	        Ext.apply(this, Ext.apply(this.initialConfig, config));
	
	        // call parent
	        ModFormDataManager.Grid.superclass.initComponent.apply(this, arguments);
	    }
		,onChangePerPage: function(tf,nv) {
			if (Ext.isEmpty(nv)) return false;
			nv = parseInt(nv);
			this.getBottomToolbar().pageSize = nv;
			store.load({params:{
				start:0
				,limit: nv
			}});
		}	
	});
	
	Ext.reg('mod-formdatamanager-viewdatagrid', ModFormDataManager.Grid);
    
});
