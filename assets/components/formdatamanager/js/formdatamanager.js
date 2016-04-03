var ModFormDataManager = function(config) {
	config=config || {};
	ModFormDataManager.superclass.constructor.call(this,config);
};
Ext.extend(ModFormDataManager,Ext.Component,{
	page:{},window:{},grid:{},tree:{},panel:{},combo:{},config:{}
});
Ext.reg('mod-formdatamanager',ModFormDataManager);
var ModFormDataManager=new ModFormDataManager();