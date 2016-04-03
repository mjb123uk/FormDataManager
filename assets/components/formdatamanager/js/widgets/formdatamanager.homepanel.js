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
			,defaults:{border:false,autoHeight:true}
			,border:true
			,activeItem:0
			,hideMode:'offsets'
			,items:[{
				title:_('formdatamanager_home_tab1')
				,items:[{
					html:'<p>'+_('formdatamanager_home_desc')+'</p>'
					,border:false
					,bodyCssClass:'panel-desc'
				}, {
					xtype:'mod-formdatamanager-homegrid'
					,preventRender:true
					,cls:'main-wrapper'
				}]
			}]
		}]
	});
	ModFormDataManager.HomePanel.superclass.constructor.call(this,config);
};
Ext.extend(ModFormDataManager.HomePanel,MODx.Panel);
Ext.reg('mod-formdatamanager-homepanel',ModFormDataManager.HomePanel);