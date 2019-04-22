Ext.onReady(function(){
	MODx.load({xtype:'mod-formdatamanager-page-maptemplate'});
});

ModFormDataManager.page.MapTemplate=function(config) {
	config = config || {};
	Ext.applyIf(config,{
		formpanel:'mod-formdatamanager-maptemplatepanel'
		,components:[{
			xtype:'mod-formdatamanager-maptemplatepanel'
			,renderTo:'mod-extra-formdatamanager'}
		]
		,buttons:[{
			text:_('formdatamanager_exit')
			,id:'formdatamanager-exit'
			,cls:'primary-button'
			,handler:function(){
				MODx.loadPage('home','namespace=formdatamanager&tn='+ModFormDataManager.config.hometab);
				return;
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
	ModFormDataManager.page.MapTemplate.superclass.constructor.call(this,config);
};
Ext.extend(ModFormDataManager.page.MapTemplate,MODx.Component,{
    windows: {}
    ,saveLayout: function(btn,e) {
        var data = this.prepareLayout();
		var selfld = ModFormDataManager.config.selectionfield;
		var tplid = ModFormDataManager.config.template;
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
				,selectionfield: selfld
				,templateid: tplid
            }
            ,listeners: {
                'success':{fn:function(r) {
					ModFormDataManager.config.layoutid = r.object['id'];
					Ext.getCmp('mod-formdatamanager-maptemplategrid').refresh();
                    MODx.msg.alert(_('success'),_('formdatamanager_layout_saved'));
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
		var action = 'layouts/remove';
		// if a table just reset frmLayout rec rather than removing
		if (ModFormDataManager.config.hometab == "Table") action = 'layouts/reset';
        MODx.msg.confirm({
            url: ModFormDataManager.config.connector_url
            ,title: _('formdatamanager_layout_remove')
            ,text: _('formdatamanager_layout_remove_confirm')
            ,params: {
                action: action
                ,id: ModFormDataManager.config.layoutid
				,template: true
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
		
		// remove any dummyfields which are set to not included
		
		var s = Ext.getCmp('mod-formdatamanager-maptemplategrid').getStore();
		var sl = s.getCount();
		var rtpl = "";
		var wd = [];
		for (var i = 0; i < sl; i++) {
			var rtpl = s.data.items[i].data.tplfield;
			// if not a template field (i.e. Added dummy field) and include NO then skip
			if ( (!rtpl) && (s.data.items[i].data.include == 0) ) continue;
			wd.push(s.data.items[i].data);
		}
		
		var wd = Ext.util.JSON.encode(wd);
		//var wd = Ext.getCmp('mod-formdatamanager-maptemplategrid').encode();
		ld.data = wd;
		
        return Ext.util.JSON.encode(ld);
    }
});
Ext.reg('mod-formdatamanager-page-maptemplate',ModFormDataManager.page.MapTemplate);
