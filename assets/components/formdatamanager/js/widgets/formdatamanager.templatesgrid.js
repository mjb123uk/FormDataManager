ModFormDataManager.templatesgrid = function(config) {
	config=config || {};
	Ext.applyIf(config,{
		id:'mod-formdatamanager-templatesgrid'
		,url: ModFormDataManager.config.connector_url
		,baseParams:{
			action: 'gettemplateslist'
		}
		,fields:['id','name','selectionfield', 'hasdata', 'usedcount', 'tpleditdata']
		,paging:true
		,remoteSort:true
		,columns:[{
			header:_('id')
			,dataIndex:'id'
			,width:4
			,hidden: true
		}, {
			header:_('formdatamanager_templates.templatename')
			,dataIndex:'name'
			,width:80	
			,tooltip:_('formdatamanager_templatescol1_qtip')
		}, {
			header:_('formdatamanager_templates.selectfld')
			,dataIndex:'selectionfield'
			,width:60
		}, {
			header:_('formdatamanager_templates.usedcount')
			,dataIndex:'usedcount'
			,width:60
		}, {
			header: 'tpleditdata'
			,dataIndex:'tpleditdata'
			,hidden: true
		}, {
            header: '&#160;'
            ,renderer: function (v, md, rec) {
                var btns = '';
                var model = rec.data;
				if (model.hasdata == 'No') {
					btns += ModFormDataManager.grid.btnRenderer({
						items: [{
							id: 'setup-' + rec.id
							,fieldLabel: _('formdatamanager_templates_setup' )
							,className: 'setup'
						}]
					});
				}
				if (model.usedcount == 0) {
					btns += ModFormDataManager.grid.btnRenderer({
						items: [{
							id: 'remove-' + rec.id
							,fieldLabel: _('formdatamanager_templates_remove' )
							,className: 'remove'
						}]
					});
				}
				if (model.hasdata == 'Yes') {
					btns += ModFormDataManager.grid.btnRenderer({
						items: [{
							id: 'edit-' + rec.id
							,fieldLabel: _('formdatamanager_templates_edit' )
							,className: 'edit'
						}]
					});
					btns += ModFormDataManager.grid.btnRenderer({
						items: [{
							id: 'viewlayout-' + rec.id
							,fieldLabel: _('formdatamanager_templates_viewlayout')
							,className: 'viewlayout'
						}]
					});
				}
                return btns;
            }
        }]
        ,tbar: [{
            text: _('formdatamanager_templates_new')
            ,cls: 'primary-button'
            ,handler: this.newTemplate
            ,scope: this
        }]
	});
	ModFormDataManager.templatesgrid.superclass.constructor.call(this,config);
	
	// Attach click event on buttons
    this.on('click', this.onClick, this);	
};
Ext.extend(ModFormDataManager.templatesgrid,MODx.grid.Grid,{
	windows:{}
	,getMenu:function() {
		var m = [];
		var model = this.menu.record;
		if (model.hasdata == 'No') {
			m.push({
				text:_('formdatamanager_templates_setup')
				,handler:this.setupTemplate
			});
		}
		if (model.usedcount == 0) {
			m.push({
				text:_('formdatamanager_templates_remove')
				,handler:this.removeTemplate
			});
		}
		if (model.hasdata == 'Yes') {
			m.push({
				text:_('formdatamanager_templates_edit')
				,handler:this.editTemplate
			});			
			m.push({
				text:_('formdatamanager_templates_viewlayout')
				,handler:this.viewLayout
			});
		}
		this.addContextMenuItem(m);
	}
	,viewLayout:function(btn,e) {
		if (!this.menu.record || !this.menu.record.name) return false;
		var r = this.menu.record;
		MODx.loadPage('layout','namespace=formdatamanager&id=template&fnm='+r.name);
		return;
	}	
	,setupTemplate:function(btn,e) {
		if (!this.menu.record || !this.menu.record.name) return false;
		var r = this.menu.record;
		ModFormDataManager.config.rid = r.id;
		if (!window.fdmSetupWindow) {
			fdmSetupWindow = new MODx.window.SetupTemplate({
				listeners: {
					success: {
						fn: this.refresh
						,scope: this
					}
				}
			});
		}
		else {
			window.fdmSetupWindow.fp.getForm().reset();
		}			
        window.fdmSetupWindow.show(e.target);
	}
	,editTemplate:function(btn,e) {
		if (!this.menu.record || !this.menu.record.name) return false;
		var r = this.menu.record;
		ModFormDataManager.config.rid = r.id;
		ModFormDataManager.config.tpleditdata = JSON.parse(r.tpleditdata);
		if (!window.fdmEditWindow) {
			fdmEditWindow = new MODx.window.EditTemplate({
				listeners: {
					'success': {
						fn: function(r) { 
							this.refresh();
							this.editUpd();
						}, scope: this
					}
				}
			});
		}
		else {
			window.fdmEditWindow.fp.getForm().reset();
			window.fdmEditWindow.setValues({templatefields: ModFormDataManager.config.tpleditdata.fields, templatefldtypes: ModFormDataManager.config.tpleditdata.fldtypes, templatedefaults: ModFormDataManager.config.tpleditdata.defaults, templatemapdata: ModFormDataManager.config.tpleditdata.mapdata, templateselectfld: ModFormDataManager.config.tpleditdata.selectfld});	
		}			
        window.fdmEditWindow.show(e.target);
	}
	,editUpd: function() {
		MODx.Ajax.request({
			url: ModFormDataManager.config.connector_url
			,params: {
				action: 'templates/editupd'
				,id: ModFormDataManager.config.rid
			}
			,listeners: {
				success: function(response, opts) {
					//console.log('Edit updates successful');
				}
				,failure: function(response, opts) {
					//console.log('server-side failure with status code ' + response.status);
				}
			}
		});
	}
	,newTemplate:function(btn, e) {
        if (!window.fdmSetupWindow) {
			fdmCreateWindow = new MODx.window.CreateTemplate({
				listeners: {
					success: {
						fn: this.refresh
						,scope: this
					}
				}
			});
		}
		else {
			window.fdmCreateWindow.fp.getForm().reset();
		}	
        window.fdmCreateWindow.show(e.target);
    }
	,removeTemplate:function(btn,e) {
		if (!this.menu.record || !this.menu.record.name) return false;
		var r = this.menu.record;
		MODx.msg.confirm({
            url: ModFormDataManager.config.connector_url
            ,title: _('formdatamanager_template_remove')
            ,text: _('formdatamanager_template_remove_confirm')
            ,params: {
                action: 'templates/remove'
                ,id: r.id
            }
            ,listeners: {
                'success':{fn:function(r) {
                    MODx.msg.alert(_('success'),_('formdatamanager_template_removed'));
					//location.reload(true);
					MODx.loadPage('home','namespace=formdatamanager&tn=Template');
                },scope:this}
            }
        });
	}
	,onClick: function(e){
        var t = e.getTarget();
        var elm = t.className.split(' ')[2];
        if (elm == 'controlBtn') {
            var action = t.className.split(' ')[3];
            var record = this.getSelectionModel().getSelected();
            this.menu.record = record.data;
            switch (action) {
				case 'viewlayout':
                    this.viewLayout('', e);
                    break;
                case 'setup':
                    this.setupTemplate('', e);
                    break;
                case 'edit':
                    this.editTemplate('', e);
                    break;					
                case 'remove':
                    this.removeTemplate();
                    break;					
            }
        }
    }
});
Ext.reg('mod-formdatamanager-templatesgrid',ModFormDataManager.templatesgrid);

/**
 * Generates the Template Create window.
 *
 * @class MODx.window.Template
 * @extends MODx.Window
 * @param {Object} config An object of options.
 * @xtype modx-window-formdatamanager-template-create
 */
MODx.window.CreateTemplate = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        title: _('formdatamanager_templates_new')
        ,url: ModFormDataManager.config.connector_url
        ,action: 'templates/create'
        ,fields: [{
            xtype: 'textfield'
            ,fieldLabel: _('formdatamanager_templates.templatename')
            ,name: 'templatename'
            ,anchor: '100%'
            ,allowBlank: false
        }
		/*
		,{
            xtype: 'textarea'
            ,fieldLabel: _('description')
            ,name: 'description'
            ,anchor: '100%'
            ,grow: true
        }
		*/
		]
        ,keys: []
    });
    MODx.window.CreateTemplate.superclass.constructor.call(this,config);
};
Ext.extend(MODx.window.CreateTemplate,MODx.Window);
Ext.reg('modx-window-formdatamanager-template-create',MODx.window.CreateTemplate);

/**
 * Generates the Template Setup window.
 *
 * @class MODx.window.Template
 * @extends MODx.Window
 * @param {Object} config An object of options.
 * @xtype modx-window-formdatamanager-template-setup
 */
MODx.window.SetupTemplate = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        title: _('formdatamanager_template_setup')
        ,url: ModFormDataManager.config.connector_url
        ,action: 'templates/setup'
        ,fields: [{
            xtype: 'hidden'
            ,name: 'id'
			,value: ModFormDataManager.config.rid
        },{
            xtype: 'textarea'
            ,fieldLabel: _('formdatamanager_template_templatefields')
            ,name: 'templatefields'
            ,anchor: '100%'
            ,allowBlank: false
			,grow: true
        },{
            xtype: 'textarea'
            ,fieldLabel: _('formdatamanager_template_templatefldtypes')
            ,name: 'templatefldtypes'
            ,anchor: '100%'
            ,grow: true
        },{
            xtype: 'textarea'
            ,fieldLabel: _('formdatamanager_template_templatedefaults')
            ,name: 'templatedefaults'
            ,anchor: '100%'
            ,grow: true
        },{
            xtype: 'textarea'
            ,fieldLabel: _('formdatamanager_templates.mapdata')
            ,name: 'templatemapdata'
            ,anchor: '100%'
            ,grow: true
        },{
            xtype: 'textfield'
            ,fieldLabel: _('formdatamanager_templates.selectfld')
            ,name: 'templateselectfld'
            ,anchor: '100%'
        }]
        ,keys: []
    });
    MODx.window.SetupTemplate.superclass.constructor.call(this,config);
};
Ext.extend(MODx.window.SetupTemplate,MODx.Window);
Ext.reg('modx-window-formdatamanager-template-setup',MODx.window.SetupTemplate);

/**
 * Generates the Template Edit window.
 *
 * @class MODx.window.Template
 * @extends MODx.Window
 * @param {Object} config An object of options.
 * @xtype modx-window-formdatamanager-template-edit
 */
MODx.window.EditTemplate = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        title: _('formdatamanager_template_edit')
        ,url: ModFormDataManager.config.connector_url
        ,action: 'templates/edit'
        ,fields: [{
            xtype: 'hidden'
            ,name: 'id'
			,value: ModFormDataManager.config.rid
        },{
            xtype: 'textarea'
            ,fieldLabel: _('formdatamanager_template_templatefields')
            ,name: 'templatefields'
            ,anchor: '100%'
            ,allowBlank: false
			,value: ModFormDataManager.config.tpleditdata.fields
			,grow: true
        },{
            xtype: 'textarea'
            ,fieldLabel: _('formdatamanager_template_templatefldtypes')
            ,name: 'templatefldtypes'
            ,anchor: '100%'
			,value: ModFormDataManager.config.tpleditdata.fldtypes
            ,grow: true
        },{
            xtype: 'textarea'
            ,fieldLabel: _('formdatamanager_template_templatedefaults')
            ,name: 'templatedefaults'
            ,anchor: '100%'
			,value: ModFormDataManager.config.tpleditdata.defaults			
            ,grow: true
        },{
            xtype: 'textarea'
            ,fieldLabel: _('formdatamanager_templates.mapdata')
            ,name: 'templatemapdata'
            ,anchor: '100%'			
			,value: ModFormDataManager.config.tpleditdata.mapdata			
			,grow: true
        },{
            xtype: 'textfield'
            ,fieldLabel: _('formdatamanager_templates.selectfld')
            ,name: 'templateselectfld'
			,value: ModFormDataManager.config.tpleditdata.selectfld			
            ,anchor: '100%'
        }]
        ,keys: []
    });
    MODx.window.EditTemplate.superclass.constructor.call(this,config);
};
Ext.extend(MODx.window.EditTemplate,MODx.Window);
Ext.reg('modx-window-formdatamanager-template-edit',MODx.window.EditTemplate);

