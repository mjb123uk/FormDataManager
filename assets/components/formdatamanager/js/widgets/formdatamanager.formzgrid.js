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
			,width:100
			,tooltip:_('formdatamanager_col1_qtip')
		}, {
			header:_('formdatamanager_form.editedon')
			,dataIndex:'editedon'
			,width:40
		}, {
			header:_('formdatamanager_form.submissions')
			,dataIndex:'submissions'
			,align:'center'
			,width:30
		}, {
			header:_('formdatamanager_form.has_layout')
			,dataIndex:'has_layout'
			,width:20
		}, {
			header:_('formdatamanager_form.lastexport')
			,dataIndex:'lastexport'
			,width:60			
		}]
	});
	ModFormDataManager.formzgrid.superclass.constructor.call(this,config);
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
		MODx.loadPage('viewdata','namespace=formdatamanager&id='+r.id+'&fnm='+r.name);
	}
});
Ext.reg('mod-formdatamanager-formzgrid',ModFormDataManager.formzgrid);