ModFormDataManager.tablesgrid = function(config) {
	config=config || {};
	Ext.applyIf(config,{
		id:'mod-formdatamanager-tablesgrid'
		,url: ModFormDataManager.config.connector_url
		,baseParams:{
			action: 'gettableslist'
		}
		,fields:['id','name','editedon','has_layout','has_tpl','lastexport','templateid','has_submission','submissions']
		,paging:true
		,remoteSort:true
		,columns:[{
			header:_('id')
			,dataIndex:'id'
			,width:4
			,hidden: true
		}, {
			header:_('formdatamanager_tables.tablename')
			,dataIndex:'name'
			,width:80
			,tooltip:_('formdatamanager_tablescol1_qtip')
		}, {
			header:_('formdatamanager_form.editedon')
			,dataIndex:'editedon'
			,width:40
			,hidden: true
		}, {
			header:_('formdatamanager_form.submissions')
			,dataIndex:'submissions'
			,align:'center'
			,width:35
		}, {
			header:_('formdatamanager_form.has_layout')
			,dataIndex:'has_layout'
			,width:27
		}, {
			header:_('formdatamanager_form.has_tpl')
			,dataIndex:'has_tpl'
			,width:30
		}, {
			header:_('formdatamanager_form.lastexport')
			,dataIndex:'lastexport'
			,width:50			
		}, {
            header: '&#160;'
			,width:100
            ,renderer: function (v, md, rec) {
                var btns = '';
                var model = rec.data;
				
 				if (model.has_layout == 'No') {
                    btns += ModFormDataManager.grid.btnRenderer({
                        items: [{
                            id: 'remove-' + rec.id
                            ,fieldLabel: _('formdatamanager_tables_remove' )
                            ,className: 'remove'
                        }]
                    });
                }
                btns += ModFormDataManager.grid.btnRenderer({
                    items: [{
                        id: 'deflayout-' + rec.id
                        ,fieldLabel: _('formdatamanager_define_layout')
                        ,className: 'deflayout'
                    }]
                });
				if ( (model.has_layout == 'Yes') && (model.has_submission) ) {
                    btns += ModFormDataManager.grid.btnRenderer({
                        items: [{
                            id: 'listexport-' + rec.id
                            ,fieldLabel: _('formdatamanager_form.has_submissions' )
                            ,className: 'listexport'
                        }]
                    });
                }
                return btns;
            }
        }]
        ,tbar: [{
            text: _('formdatamanager_tables_new')
            ,cls: 'primary-button'
            ,handler: this.newFormTable
            ,scope: this
        }]
	});
	ModFormDataManager.tablesgrid.superclass.constructor.call(this,config);
	
	// Attach click event on buttons
    this.on('click', this.onClick, this);	
};
Ext.extend(ModFormDataManager.tablesgrid,MODx.grid.Grid,{
	windows:{}
	,getMenu:function() {
		var m = [];
		var model = this.menu.record;
		if (model.has_layout == 'No') {
			m.push({
				text:_('formdatamanager_tables_remove')
				,handler:this.removeTable
			});
		}
		m.push({
			text:_('formdatamanager_define_layout')
			,handler:this.defLayout
		});
		if ( (model.has_layout == 'Yes') && (model.has_submission) ) {
			m.push({
				text:_('formdatamanager_form.has_submissions')
				,handler:this.viewData
			});
		}
		this.addContextMenuItem(m);
	}
	,defLayout:function(btn,e) {
		if (!this.menu.record || !this.menu.record.id) return false;
		var r = this.menu.record;
		if (r.templateid > 0) {
			MODx.loadPage('maptemplate','namespace=formdatamanager&id=table&fnm='+r.name+'&tpl='+r.templateid);
			return;
		}
		else {
			if (r.has_layout == "Yes") {
				MODx.loadPage('layout','namespace=formdatamanager&id=table&fnm='+r.name);
				return;
			}
		}
		ModFormDataManager.config.rid = r.id;
		ModFormDataManager.config.rname = r.name;
		if (!window.fdmTemplateWindow) {
			fdmTemplateWindow = new MODx.window.SelectTemplate({
				listeners: {
					'success': { fn: function(r) { this.defLayoutOrTemplate(r); }, scope: this }
				}
			});
		}
		else {
			window.fdmTemplateWindow.fp.getForm().reset();
		}
        window.fdmTemplateWindow.show(e.target);		
	}
	,defLayoutOrTemplate:function(r) {
		if (typeof(r.tpname) == "undefined") MODx.loadPage('layout','namespace=formdatamanager&id=table&fnm='+ModFormDataManager.config.rname);
		else MODx.loadPage('maptemplate','namespace=formdatamanager&id=table&fnm='+ModFormDataManager.config.rname+'&tpn='+r.tpname);
		return;
	}	
	,viewData:function(btn,e) {
		if (!this.menu.record || !this.menu.record.name) return false;
		var r = this.menu.record;
		MODx.loadPage('viewdata','namespace=formdatamanager&id=table&fnm='+r.name+'&gh='+ModFormDataManager.config.gridheight);
		return;
	}
	,newFormTable:function(btn, e) {
		var ctbls="";
		var allrecs = Ext.getCmp('mod-formdatamanager-tablesgrid').getStore().getRange();
		Ext.each(allrecs, function (item) {
			if (ctbls != "") ctbls = ctbls+"~";
			ctbls = ctbls+item.data.name;                 
		});
		ModFormDataManager.config.tbldata = ctbls;
		if (!window.fdmCreateFormWindow) {
			fdmCreateFormWindow = new MODx.window.CreateFormTable({
				listeners: {
					success: {
						fn: this.refresh
						,scope: this
					}
				}
			});
		}
		else {
			window.fdmCreateFormWindow.fp.getForm().reset();
		}
        window.fdmCreateFormWindow.show(e.target);
    }
	,removeTable:function(btn,e) {
		if (!this.menu.record || !this.menu.record.name) return false;
		var r = this.menu.record;
		MODx.msg.confirm({
            url: ModFormDataManager.config.connector_url
            ,title: _('formdatamanager_table_remove')
            ,text: _('formdatamanager_table_remove_confirm')
            ,params: {
                action: 'tables/remove'
                ,id: r.id
            }
            ,listeners: {
                'success':{fn:function(r) {
                    MODx.msg.alert(_('success'),_('formdatamanager_table_removed'));
					//location.reload(true);
					MODx.loadPage('home','namespace=formdatamanager&tn=Table');
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
                case 'deflayout':
                    this.defLayout('', e);
                    break;
                case 'listexport':
                    this.viewData();
                    break;
                case 'remove':
                    this.removeTable();
                    break;					
            }
        }
    }
});
Ext.reg('mod-formdatamanager-tablesgrid',ModFormDataManager.tablesgrid);

MODx.combo.DbTables = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        name: 'dbtables'
        ,hiddenName: 'dbtables'
        ,displayField: 'name'
        ,valueField: 'name'
        ,fields: ['name']
        ,typeAhead: true
        ,minChars: 1
        ,editable: true
        ,allowBlank: true
        // ,pageSize: 20
        ,url: ModFormDataManager.config.connector_url
        ,baseParams: {
            action: 'tables/gettables'
			,tbldata: ModFormDataManager.config.tbldata
        }
    });
    MODx.combo.DbTables.superclass.constructor.call(this,config);
};
Ext.extend(MODx.combo.DbTables,MODx.combo.ComboBox);
Ext.reg('modx-combo-tables',MODx.combo.DbTables);

/**
 * Generates the FormTable window.
 *
 * @class MODx.window.FormTable
 * @extends MODx.Window
 * @param {Object} config An object of options.
 * @xtype modx-window-formdatamanager-table-create
 */
MODx.window.CreateFormTable = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        title: _('formdatamanager_tables_new')
        ,url: ModFormDataManager.config.connector_url
        ,action: 'tables/create'
        ,fields: [{
            xtype: 'modx-combo-tables'
            ,fieldLabel: _('formdatamanager_tables.tablename')
            ,name: 'tablename'
            ,anchor: '100%'
            ,allowBlank: false
        }]
        ,keys: []
    });
    MODx.window.CreateFormTable.superclass.constructor.call(this,config);
};
Ext.extend(MODx.window.CreateFormTable,MODx.Window);
Ext.reg('modx-window-formdatamanager-table-create',MODx.window.CreateFormTable);
