ModFormDataManager.HomePanel=function(config) {
	config = config || {};
	Ext.apply(config,{
		border:false
		,baseCls:'modx-formpanel'
		,cls:'container'
		,items:[{
			html:'<h2>'+_('formdatamanager')+'</h2>'
			,border:false
			,cls:'modx-page-header'
		}, {
			xtype:'modx-tabs'
			,id:'fdmhometabs'
			,defaults:{border:false,autoHeight:true}
			,border:true
			,activeItem:0
			,hideMode:'offsets'
		}],
		listeners: {
            afterrender: this.setup
        }
	});
	ModFormDataManager.HomePanel.superclass.constructor.call(this,config);
};
Ext.extend(ModFormDataManager.HomePanel,MODx.Panel,{
    setup: function() {
		var tabs = Ext.getCmp('fdmhometabs');
		if (ModFormDataManager.config.hasformz) {
			tabs.add({
                title:_('formdatamanager_home_tab1')
				,items:[{
					html:'<p>'+_('formdatamanager_formz_desc')+'</p>'
					,border:false
					,bodyCssClass:'panel-desc'
				}, {
					xtype:'mod-formdatamanager-formzgrid'
					,preventRender:true
					,cls:'main-wrapper'
				}]
            });
		}
		if (ModFormDataManager.config.hasformit) {
			tabs.add({
				title:_('formdatamanager_home_tab2')
				,items:[{
					html:'<p>'+_('formdatamanager_formit_desc')+'</p>'
					,border:false
					,bodyCssClass:'panel-desc'
				}, {
					xtype:'mod-formdatamanager-formitgrid'
					,preventRender:true
					,cls:'main-wrapper'
				}]
            });				
		}
		// if no forms then show message
		if ( (!ModFormDataManager.config.hasformz) && (!ModFormDataManager.config.hasformit) ) {
			tabs.add({
				title:_('formdatamanager')
				,items:[{
					html:'<p>'+_('formdatamanager_noforms_desc')+'</p>'
					,border:false
					,bodyCssClass:'panel-desc'
				}]
            });	
		}
		tabs.setActiveTab( 0 );	
    }	
});
Ext.reg('mod-formdatamanager-homepanel',ModFormDataManager.HomePanel);