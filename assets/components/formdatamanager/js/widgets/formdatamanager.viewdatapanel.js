ModFormDataManager.ViewdataPanel=function(config) {
	config=config || {};
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
			,defaults:{
				border:false
				,autoHeight:true
			}
			,border:true
			,activeItem:0
			,hideMode:'offsets'
			,items:[{
				title:_('formdatamanager_viewdata_tab1')
				,items:[{
					html:'<p>'+_('formdatamanager_viewdata_desc')+ModFormDataManager.config.formname+']</p>'
					,border:false
					,bodyCssClass:'panel-desc'
				}, {
					xtype:'mod-formdatamanager-viewdatagrid'
					,cls:'main-wrapper'
					/*
					,preventRender:true		
					*/
				}]
			}]
		}]
	});
	ModFormDataManager.ViewdataPanel.superclass.constructor.call(this,config);
};
Ext.extend(ModFormDataManager.ViewdataPanel,MODx.Panel);
Ext.reg('mod-formdatamanager-viewdatapanel',ModFormDataManager.ViewdataPanel);