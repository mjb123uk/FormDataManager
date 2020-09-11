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
			tabs.add({
				title:_('formdatamanager_home_tab3')
				,items:[{
					html:'<p>'+_('formdatamanager_tables_desc')+'</p>'
					,border:false
					,bodyCssClass:'panel-desc'
				}, {
					xtype:'mod-formdatamanager-tablesgrid'
					,preventRender:true
					,cls:'main-wrapper'
				}]
            });
			tabs.add({
				title:_('formdatamanager_home_tab4')
				,items:[{
					html:'<p>'+_('formdatamanager_templates_desc')+'</p>'
					,border:false
					,bodyCssClass:'panel-desc'
				}, {
					xtype:'mod-formdatamanager-templatesgrid'
					,preventRender:true
					,cls:'main-wrapper'
				}]
            });	
			tabs.add({
				title:_('formdatamanager_home_tab5')
				,items:[{
					html:'<p>'+_('formdatamanager_bulkexport_desc')+'</p>'
					,border:false
					,bodyCssClass:'panel-desc'
				}, {
					xtype:'mod-formdatamanager-bulkexportgrid'
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
		var ht = 0;
		var htn = ModFormDataManager.config.hometab;
		if (htn == "FormIt") {
			if (ModFormDataManager.config.hasformz) ht += 1;
		}
		if (htn == "Table") {
			if (ModFormDataManager.config.hasformz) ht += 1;
			if (ModFormDataManager.config.hasformit) ht += 1;
		}
		if (htn == "Template") {
			ht += 1;
			if (ModFormDataManager.config.hasformz) ht += 1;
			if (ModFormDataManager.config.hasformit) ht += 1;
		}
		if (htn == "Export") {
			ht += 2;
			if (ModFormDataManager.config.hasformz) ht += 1;
			if (ModFormDataManager.config.hasformit) ht += 1;
		}
		var docheight = document.body.clientHeight;
		var gridheight = 140;

		ModFormDataManager.config.gridheight = (docheight-gridheight)-100;
		tabs.setActiveTab( ht );
    }	
});
Ext.reg('mod-formdatamanager-homepanel',ModFormDataManager.HomePanel);