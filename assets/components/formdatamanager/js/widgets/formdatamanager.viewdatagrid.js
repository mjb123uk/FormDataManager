ModFormDataManager.viewdatagrid = function(config) {
	config=config || {};
	Ext.applyIf(config,{
		id:'mod-formdatamanager-viewdatagrid'
		,url: ModFormDataManager.config.connector_url
		,baseParams:{
			action:'getviewdata'
			,formid: ModFormDataManager.config.formid
			,layoutid: ModFormDataManager.config.layoutid
		}
		,cm: viewdataColumnModel
		,fields: viewdataFields
		,paging:true
	});
	ModFormDataManager.viewdatagrid.superclass.constructor.call(this,config);
};
Ext.extend(ModFormDataManager.viewdatagrid,MODx.grid.Grid);
Ext.reg('mod-formdatamanager-viewdatagrid',ModFormDataManager.viewdatagrid);