ModFormDataManager.LayoutPanel=function(config) {
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
				title:_('formdatamanager_layout_tab1')
				,items:[{
					html:'<p>'+( (ModFormDataManager.config.formid == "template" ) ? _('formdatamanager_layout_templatedesc') : _('formdatamanager_layout_desc') )+ModFormDataManager.config.formname+']</p>'
					,border:false
					,bodyCssClass:'panel-desc'
				}, {
					xtype:'mod-formdatamanager-layoutgrid'
					,preventRender:true
					,cls:'main-wrapper'
				}]
			}]
		}]
	});
	ModFormDataManager.LayoutPanel.superclass.constructor.call(this,config);
};
Ext.extend(ModFormDataManager.LayoutPanel,MODx.Panel);
Ext.reg('mod-formdatamanager-layoutpanel',ModFormDataManager.LayoutPanel);

