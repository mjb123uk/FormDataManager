ModFormDataManager.formitgrid = function(config) {
	config=config || {};
	
	this.sm = new Ext.grid.CheckboxSelectionModel();
	Ext.applyIf(config,{
		id:'mod-formdatamanager-formitgrid'
		,url: ModFormDataManager.config.connector_url
		,baseParams:{
			action: 'getformitlist'
			,activeFilter: 'Active'
		}
		,fields:['id','type','name','context_key','inactive','editedon','has_layout','layoutid','has_tpl','lastexport','selectionfield','templateid','has_submission','submissions']
		,paging:true
		,remoteSort:false
		,autosave:true
		,save_action: "layouts/gridupdate"
		,preventSaveRefresh: 0
		,sm: this.sm		
		,columns:[this.sm,{
			header:_('id')
			,dataIndex:'id'
			,width:4
			,hidden: true
		}, {
			header:_('formdatamanager_form.formname')
			,dataIndex:'name'
			,width:80
			,sortable: true
			,tooltip:_('formdatamanager_col1_qtip')
		}, {
			header:_('formdatamanager_form.context_key')
			,dataIndex:'context_key'
			,width:30
			,sortable: true
			,hidden: (ModFormDataManager.config.showformitcontext == 0)
		}, {
			header:_('formdatamanager_form.inactive')
			,dataIndex:'inactive'
			,width:30
			,editor: { xtype: 'modx-combo-boolean', renderer: 'boolean' }			
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
 
                btns = ModFormDataManager.grid.btnRenderer({
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
            text: _('bulk_actions')
            ,menu: [{
                text: _('formdatamanager_form.bulkexport')
                ,handler: this.bulkExport
                ,scope: this
            }]
		},'->',{
			xtype: 'modx-combo-activefilter'
            ,name: 'formitactivefilter'
 			,value: 'Active'
            ,width: 200
            ,listeners: {
                'select': {fn:this.filterActiveFilter,scope:this}
            }
        }]
	});
	ModFormDataManager.formitgrid.superclass.constructor.call(this,config);
	
	// Attach click event on buttons
    this.on('click', this.onClick, this);
};
Ext.extend(ModFormDataManager.formitgrid,MODx.grid.Grid,{
	windows:{}
	,getMenu:function() {
		var m = [];
		var model = this.menu.record;
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
			MODx.loadPage('maptemplate','namespace=formdatamanager&id=formit&fnm='+r.name+'&tpl='+r.templateid);
			return;
		}
		else {
			if (r.has_layout == "Yes") {
				MODx.loadPage('layout','namespace=formdatamanager&id=formit&fnm='+r.name);
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
		if (typeof(r.tpname) == "undefined") MODx.loadPage('layout','namespace=formdatamanager&id=formit&fnm='+ModFormDataManager.config.rname);
		else MODx.loadPage('maptemplate','namespace=formdatamanager&id=formit&fnm='+ModFormDataManager.config.rname+'&tpn='+r.tpname);
		return;
	}
	,bulkExport: function() {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;

        var w = MODx.msg.confirm({
            title: _('formdatamanager_form.bulkexport')
            ,text: _('formdatamanager_form.bulkexport_multiple_confirm')
            ,url: ModFormDataManager.config.connector_url
            ,params: {
                action: 'bulkexport'
                ,layouts: cs
				,ftype: 'formit'
            }
            ,listeners: {
                'success': {					
					fn: function (r) {
						var html = '';
						var data = {};
						var rcount = r.total;
						var filename = r.results[rcount-1];
						if (filename != '') {
							html = _('formdatamanager_form.bulkexport_downloadmsg')+'&nbsp;' + filename;
						}
						Ext.Msg.show({
							title: _('formdatamanager_form.bulkexport_success')
							,msg: html
							,value: filename+'.zip'
							,buttons: Ext.Msg.YESNO
							,fn: this.downloadExport
							,icon: Ext.MessageBox.QUESTION
						});
						this.getSelectionModel().clearSelections(true);
						this.refresh();
					},scope:this
				}
            }
        });
        return true;
    }
	,downloadExport:function(btn,filename) {
		if (btn == 'yes') {
			if (!Ext.fly('frmDummy')) {
				var frm = document.createElement('form');
				frm.id = 'frmDummy';
				frm.filename = filename;
				frm.className = 'x-hidden';
				document.body.appendChild(frm);
			}
			MODx.Ajax.request({
				url: ModFormDataManager.config.connector_url
				,params: {
					action: 'bulkexports/downloadbefile'
					,filename: filename
				}
				,form: Ext.fly('frmDummy')
				,isUpload: true
			});
		}
	}
	,filterActiveFilter: function(cb,nv,ov) {
        this.getStore().baseParams.activeFilter = Ext.isEmpty(nv) || Ext.isObject(nv) ? cb.getValue() : nv;
        this.getBottomToolbar().changePage(1);
        return true;
    }	
	,viewData:function(btn,e) {
		if (!this.menu.record || !this.menu.record.name) return false;
		var r = this.menu.record;
		MODx.loadPage('viewdata','namespace=formdatamanager&id=formit&fnm='+r.name+'&gh='+ModFormDataManager.config.gridheight);
		return;
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
            }
        }
    }
});
Ext.reg('mod-formdatamanager-formitgrid',ModFormDataManager.formitgrid);