var ModFormDataManager = function(config) {
	config=config || {};
	ModFormDataManager.superclass.constructor.call(this,config);
};
Ext.extend(ModFormDataManager,Ext.Component,{
	page:{},window:{},grid:{},tree:{},panel:{},combo:{},config:{}
});
Ext.reg('mod-formdatamanager',ModFormDataManager);

var ModFormDataManager = new ModFormDataManager();

/* Helper */
ModFormDataManager.grid.btnRenderer = function (list) {
    var btnTemplate = new Ext.XTemplate('<tpl for=".">' +
        '<tpl if="items">'+
        '<tpl for="items">' +
        '<div id="{id}" class="x-btn-text button controlBtn {className}" style="{buttonStyle}">{fieldLabel}</div>' +
        '</tpl>' +
        '</tpl>' +
        '</tpl>', {
        compiled: true
    });

    return btnTemplate.apply(list);
}