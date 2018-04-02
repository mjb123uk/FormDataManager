Ext.onReady(function(){
	MODx.load({xtype:'mod-formdatamanager-page-layout'});
});

ModFormDataManager.page.Layout=function(config) {
	config = config || {};
	Ext.applyIf(config,{
		formpanel:'mod-formdatamanager-layoutpanel'
		,components:[{
			xtype:'mod-formdatamanager-layoutpanel'
			,renderTo:'mod-extra-formdatamanager'}
		]
		,buttons:[{
			text:_('formdatamanager_exit')
			,id:'formdatamanager-exit'
			,cls:'primary-button'
			,handler:function(){
				MODx.loadPage('home','namespace=formdatamanager&tn='+ModFormDataManager.config.hometab);
			}
		},{
			text:_('formdatamanager_save')
			,id:'formdatamanager-save'
			,handler: this.saveLayout
			,scope: this
		},{
			text:_('formdatamanager_remove')
			,id:'formdatamanager-remove'
			,handler: this.removeLayout
			,scope: this
		}]
	});
	ModFormDataManager.page.Layout.superclass.constructor.call(this,config);
};
Ext.extend(ModFormDataManager.page.Layout,MODx.Component,{
    windows: {}
    ,saveLayout: function(btn,e) {
        var data = this.prepareLayout();
		var exdata = Ext.get("ext-gen95").getValue();
		// test if new or update
		var action = 'layouts/update';
		var wid = ModFormDataManager.config.layoutid
		if (wid == 0) {
			action = 'layouts/create';
			wid = ModFormDataManager.config.formid
		}

        MODx.Ajax.request({
            url: ModFormDataManager.config.connector_url
            ,params: {
                action: action
                ,id: wid
				,formname: ModFormDataManager.config.formname
                ,data: data
				,exdata: exdata
            }
            ,listeners: {
                'success':{fn:function(r) {
					var nfid = r.object['id'];
                    MODx.msg.alert(_('success'),_('formdatamanager_layout_saved'));
					ModFormDataManager.config.layoutid = nfid;			
                },scope:this}
            }
        });
    }
    ,removeLayout: function(btn,e) {
		if (ModFormDataManager.config.layoutid == 0) {
			// no layout defined yet - so alert
			MODx.msg.alert(_('error'),_('formdatamanager_layout_remove_notreq'));
			return;
		}
        MODx.msg.confirm({
            url: ModFormDataManager.config.connector_url
            ,title: _('formdatamanager_layout_remove')
            ,text: _('formdatamanager_layout_remove_confirm')
            ,params: {
                action: 'layouts/remove'
                ,id: ModFormDataManager.config.layoutid
            }
            ,listeners: {
                'success':{fn:function(r) {
                    MODx.msg.alert(_('success'),_('formdatamanager_layout_removed'));
					location.reload(true);
                },scope:this}
            }
        });
    }
    ,prepareLayout: function() {
        var ld = {};
		ld.data = Ext.getCmp('mod-formdatamanager-layoutgrid').encode();
		
        return Ext.util.JSON.encode(ld);
    }
});
Ext.reg('mod-formdatamanager-page-layout',ModFormDataManager.page.Layout);
