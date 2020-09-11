ModFormDataManager.bulkexportgrid = function(config) {
	config=config || {};

	Ext.applyIf(config,{
		id:'mod-formdatamanager-bulkexportgrid'
		,url: ModFormDataManager.config.connector_url
		,baseParams:{
			action: 'getbulkexportlist'
		}
		,fields:['id','filename','filetype','filesize','createdon']
		,paging:true
		,remoteSort:false
		,columns:[{
			header:_('id')
			,dataIndex:'id'
			,width:4
			,hidden: true
		}, {
			header:_('formdatamanager_form.befilename')
			,dataIndex:'filename'
			,width:80
			,sortable: true
			,tooltip:_('formdatamanager_bulkexportcol1_qtip')
		}, {
			header:_('formdatamanager_form.befiletype')
			,dataIndex:'filetype'
			,width:30
		}, {
			header:_('formdatamanager_form.befilesize')
			,dataIndex:'filesize'
			,width:30
		}, {
			header:_('formdatamanager_form.becreatedon')
			,dataIndex:'createdon'
			,width:50
			,sortable: true			
		}, {
            header: '&#160;'
			,width:100
            ,renderer: function (v, md, rec) {
                var btns = '';
                var model = rec.data;
 
                btns = ModFormDataManager.grid.btnRenderer({
                    items: [{
                        id: 'dlexport-' + rec.id
                        ,fieldLabel: _('formdatamanager_download_export')
                        ,className: 'dlexport'
                    }]
                });
				btns += ModFormDataManager.grid.btnRenderer({
                    items: [{
                        id: 'removeexport-' + rec.id
                        ,fieldLabel: _('formdatamanager_remove_export')
                        ,className: 'remove'
                    }]
                });
                return btns;
            }
        }]
	});
	ModFormDataManager.bulkexportgrid.superclass.constructor.call(this,config);
	
	// Attach click event on buttons
    this.on('click', this.onClick, this);
};
Ext.extend(ModFormDataManager.bulkexportgrid,MODx.grid.Grid,{
	windows:{}
	,getMenu:function() {
		var m = [];
		var model = this.menu.record;
		m.push({
			text:_('formdatamanager_download_export')
			,handler:this.downloadExport
		});
		m.push({
			text:_('formdatamanager_remove_export')
			,handler:this.removeExport
		});		
		this.addContextMenuItem(m);
	}
	,downloadExport:function(btn,e) {
		if (!this.menu.record || !this.menu.record.id) return false;
		var r = this.menu.record;
		//ModFormDataManager.config.filename = r.filename;
		
		if (!Ext.fly('frmDummy')) {
			var frm = document.createElement('form');
			frm.id = 'frmDummy';
			frm.filename = r.id;
			frm.className = 'x-hidden';
			document.body.appendChild(frm);
		}
		MODx.Ajax.request({
			url: ModFormDataManager.config.connector_url
			,params: {
				action: 'bulkexports/downloadbefile'
				,filename: r.id
			}
			,form: Ext.fly('frmDummy')
			,isUpload: true
		});
		
	}
	,removeExport:function(btn,e) {
		if (!this.menu.record || !this.menu.record.id) return false;
		var r = this.menu.record;
		MODx.msg.confirm({
            url: ModFormDataManager.config.connector_url
            ,title: _('formdatamanager_remove_export')
            ,text: _('formdatamanager_remove_export_confirm')
            ,params: {
                action: 'bulkexports/remove'
                ,filename: r.id
            }
            ,listeners: {
                'success':{fn:function(r) {
                    MODx.msg.alert(_('success'),_('formdatamanager_bulkexport_removed'));
					//location.reload(true);
					MODx.loadPage('home','namespace=formdatamanager&tn=Export');
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
                case 'dlexport':
                    this.downloadExport('', e);
                    break;
				case 'remove':
                    this.removeExport('', e);
                    break;
            }
        }
    }
});
Ext.reg('mod-formdatamanager-bulkexportgrid',ModFormDataManager.bulkexportgrid);