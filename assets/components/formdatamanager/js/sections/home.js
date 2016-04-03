Ext.onReady(function() {
	MODx.load({xtype:'mod-formdatamanager-page-home'});
});

ModFormDataManager.page.Home=function(config) {
	config = config || {};
	Ext.applyIf(config,{
		formpanel:'mod-formdatamanager-homepanel'
		,components:[{
			xtype:'mod-formdatamanager-homepanel'
			,renderTo:'mod-extra-formdatamanager'
		}]
	});
	ModFormDataManager.page.Home.superclass.constructor.call(this,config);
};
Ext.extend(ModFormDataManager.page.Home,MODx.Component);
Ext.reg('mod-formdatamanager-page-home',ModFormDataManager.page.Home);