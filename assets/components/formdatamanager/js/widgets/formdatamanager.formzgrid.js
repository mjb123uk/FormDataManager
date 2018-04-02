ModFormDataManager.formzgrid = function(config) {
	config=config || {};
	Ext.applyIf(config,{
		id:'mod-formdatamanager-formzgrid'
			,url: ModFormDataManager.config.connector_url
		,baseParams:{
			action: 'getformzlist'
		}
		,fields:['id','name','editedon','has_layout','lastexport','has_submission','submissions']
		,paging:true
		,remoteSort:true
		,columns:[{
			header:_('id')
			,dataIndex:'id'
			,width:4
		}, {
			header:_('formdatamanager_form.formname')
			,dataIndex:'name'
			,width:80
			,tooltip:_('formdatamanager_col1_qtip')
		}, {
			header:_('formdatamanager_form.editedon')
			,dataIndex:'editedon'
			,width:40
		}, {
			header:_('formdatamanager_form.submissions')
			,dataIndex:'submissions'
			,align:'center'
			,width:35
		}, {
			header:_('formdatamanager_form.has_layout')
			,dataIndex:'has_layout'
			,width:30
		}, {
			header:_('formdatamanager_form.lastexport')
			,dataIndex:'lastexport'
			,width:50			
		}, {
            header: '&#160;'
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
				if (model.has_submission) {
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
	});
	ModFormDataManager.formzgrid.superclass.constructor.call(this,config);
	
	// Attach click event on buttons
    this.on('click', this.onClick, this);
};
Ext.extend(ModFormDataManager.formzgrid,MODx.grid.Grid,{
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
		MODx.loadPage('layout','namespace=formdatamanager&id='+r.id+'&fnm='+r.name);
	}
	,viewData:function(btn,e) {
		if (!this.menu.record || !this.menu.record.id) return false;
		var r = this.menu.record;
		MODx.loadPage('viewdata','namespace=formdatamanager&id='+r.id+'&fnm='+r.name+'&gh='+ModFormDataManager.config.gridheight);
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
Ext.reg('mod-formdatamanager-formzgrid',ModFormDataManager.formzgrid);