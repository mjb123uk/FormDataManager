Ext.onReady(function(){
	MODx.load({xtype:'mod-formdatamanager-page-viewdata'});
});

ModFormDataManager.page.Viewdata=function(config) {
	config = config || {};
	Ext.applyIf(config,{
		formpanel:'mod-formdatamanager-viewdatapanel'
		,components:[{
			xtype:'mod-formdatamanager-viewdatapanel'
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
			text:_('formdatamanager_export')
			,id:'formdatamanager-export'
			,handler: function (btn, e) {
                if (! this.exportDataWindow) {
                    this.exportDataWindow = MODx.load({
                        xtype: 'formdatamanager-window-export-data'
                        ,formid: ModFormDataManager.config.formid
						,formname: ModFormDataManager.config.formname
						,layoutid: ModFormDataManager.config.layoutid
						,lastexportto: ModFormDataManager.config.lastexportto
                    });
                }
                this.exportDataWindow.show(e.target);
				this.exportDataWindow.setup(e.target);
            }
			,scope: this
		}]
	});
	ModFormDataManager.page.Viewdata.superclass.constructor.call(this,config);
};
Ext.extend(ModFormDataManager.page.Viewdata,MODx.Component,{
    windows: {}
});
Ext.reg('mod-formdatamanager-page-viewdata',ModFormDataManager.page.Viewdata);

ModFormDataManager.window.ExportData = function (config) {
    config = config || {};
    config.id = Ext.id();

    Ext.applyIf(config, {
        title: _('formdatamanager_form.export')
        ,autoHeight: true
        ,closeAction: 'hide'
		,modal: true
        ,width: 540
        ,defaults: {
            border: false,
            autoHeight: true,
            bodyStyle: 'padding: 5px 8px 5px 5px;',
            layout: 'form',
            deferredRender: false,
            forceLayout: true
        }
        ,buttons: [{
            text: config.cancelBtnText || _('cancel')
            ,scope: this
            ,handler: function() { config.closeAction !== 'close' ? this.hide() : this.close(); }
        }, '-', {
            text: _('formdatamanager_export')
            ,cls: 'trigger-action primary-button'
			,scope: this
            ,handler: function (btn, e) {

				// hide export options window on export
				this.hide();
				
                 // Create dummy form to trick
                 // Ext Ajax request for force file download

                if (!Ext.fly('frmDummy')) {
                    var frm = document.createElement('form');
                    frm.id = 'frmDummy';
                    frm.formid = config.formid;
					frm.formname = config.formname;
					frm.layoutid = config.layoutid;
					frm.lastexportto = config.lastexportto;
                    frm.className = 'x-hidden';
                    document.body.appendChild(frm);
                }
                MODx.Ajax.request({
                    url: ModFormDataManager.config.connector_url
                    ,params: {
                        action: 'exportdata'
                        ,formid: config.formid
						,formname: config.formname
						,layoutid: config.layoutid
						,fldextra: ModFormDataManager.config.fldextra
						,lastexportto: config.lastexportto
                        ,startDate: Ext.getCmp('startDate').getValue()
                        ,endDate: Ext.getCmp('endDate').getValue()
                    }
                    ,form: Ext.fly('frmDummy')
                    ,isUpload: true
                    /*
					,listeners: {
                        'success': { fn: function(r) {
                            //	
                        }, scope: this }
                    }
					*/
                });
            }
        }]
        ,fields: [{
            title: _('formdatamanager_export.daterange')
			,id: 'fdmExOpts'
            ,layout: 'column'
            ,bodyCssClass: 'main-wrapper'
            ,autoHeight: true
            //,collapsible: true
            //,collapsed: true
            ,border: true
            ,hideMode: 'offsets'
            ,defaults: {
                layout: 'form'
                ,border: false
            }
            ,items: [{
                columnWidth: .5
                ,items: [{
                    //xtype: 'datefield'
					xtype: 'xdatetime'
                    ,fieldLabel: _('formdatamanager_export.start_date')
                    ,name: 'start_date'
                    ,id: 'startDate'
					,dateFormat: MODx.config.manager_date_format
					,timeFormat: MODx.config.manager_time_format
					,startDay: parseInt(MODx.config.manager_week_start)
					,offset_time: MODx.config.server_offset_time
					,allowBlank: true
                    ,hiddenFormat: 'Y-m-d H:i:s'
                    ,grow: false
                    ,anchor: '100%'
					,value: config.lastexportto
                }]
            }, {
                columnWidth: .5
                ,items: [{
                    //xtype: 'datefield'
					xtype: 'xdatetime'
                    ,fieldLabel: _('formdatamanager_export.end_date')
                    ,name: 'end_date'
                    ,id: 'endDate'
					,dateFormat: MODx.config.manager_date_format
					,timeFormat: MODx.config.manager_time_format
					,startDay: parseInt(MODx.config.manager_week_start)
					,offset_time: MODx.config.server_offset_time
					,allowBlank: true
                    ,hiddenFormat: 'Y-m-d H:i:s'
                    ,grow: false
                    ,anchor: '100%'
                }]
			}]
		},{
			html: "<p>"+_('formdatamanager_export.nodates1')+"</p><br><p>"+_('formdatamanager_export.nodates2')+"</p>"
			,id: 'fdmExOptsCT'
			,hidden: true
		}]
    });
    ModFormDataManager.window.ExportData.superclass.constructor.call(this, config);
};
Ext.extend(ModFormDataManager.window.ExportData, MODx.Window, {
	setup: function (w) {
		if (ModFormDataManager.config.hometab == "Table") {
			// check if export fields defined
			if (ModFormDataManager.config.fldextra == "") {
				// hide export date range	
				var ewo = Ext.getCmp("fdmExOpts");
				ewo.hide();
				var ewo = Ext.getCmp("fdmExOptsCT");
				ewo.show();
			}
		}
	}	
});
Ext.reg('formdatamanager-window-export-data', ModFormDataManager.window.ExportData);
