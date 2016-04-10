ModFormDataManager.layoutgrid = function(config) {
	config=config || {};
	Ext.applyIf(config,{
		id:'mod-formdatamanager-layoutgrid'
		,url: ModFormDataManager.config.connector_url
		,baseParams:{
			action:'GetFldData'
			,formid: ModFormDataManager.config.formid
			,formname: ModFormDataManager.config.formname
		}
		,fields:['id','order','label','type','include','coltitle','default']
		,paging:true
		,primaryKey: 'order'
		,remoteSort:false
		,ddGroup:'ddGrid'+config.formid
		,enableDragDrop:true
		,columns:[{
			header:_('id')
			,dataIndex:'id'
			,sortable:false
			,hidden:true
			,width:5
		}, {
			header:_('order')
			,dataIndex:'order'
			,sortable:false
			,hidden:true
			,width:5
		}, {
			header:_('formdatamanager_fldgrid.label')
			,dataIndex:'label'
			,sortable:false
			,width:35
		}, {
			header:_('formdatamanager_fldgrid.type')
			,dataIndex:'type'
			,sortable:false
			,width:20
		}, {
			header:_('formdatamanager_fldgrid.include')
			,dataIndex:'include'
			,sortable:false
			,width:20
			,editor: { xtype: 'modx-combo-boolean', renderer: 'boolean' }
		}, {
			header:_('formdatamanager_fldgrid.coltitle')
			,dataIndex:'coltitle'
			,sortable:false
			,width:35
			,editor: { xtype: 'textfield' }
		}, {
			header:_('formdatamanager_fldgrid.default')
			,dataIndex:'default'
			,sortable:false
			,editor: { xtype: 'textfield' }
		}]
	});
	ModFormDataManager.layoutgrid.superclass.constructor.call(this,config);
	
	// Reorder by Drag and Drop
    this.on('render', this.dragAndDrop, this);
};
Ext.extend(ModFormDataManager.layoutgrid, MODx.grid.Grid, {
    getMenu: function (grid, rowIndex, e) {
        /*
		return [{
            text: _('formdatamanager.field.help')
            ,handler: this.updateField
        }];
		*/
    }
    ,dragAndDrop: function (grid) {
    	var that = this,
            ddrow = new Ext.dd.DropTarget(grid.container, {
    		ddGroup: 'ddGrid' + this.config.formid
    		,copy: false
    		,notifyDrop: function (dd, e, data) {
    			var ds = grid.store,
                    sm = grid.getSelectionModel(),
    				rows = sm.getSelections();

    			if (dd.getDragData(e)) {
    				var cindex = dd.getDragData(e).rowIndex;
    				for (var i = 0; i < rows.length; i++) {
                        rowData = ds.getById(rows[i].id);
    					if (!this.copy) {
                            ds.remove(ds.getById(rows[i].id));
                            ds.insert(cindex, rowData);
    					}
    				};
                }

                var d = ds.data.items,
                    data,
                    fieldOrder = [];

                for (var i = 0; i < d.length; i++) {
                    data = d[i].data;
                    data['order'] = i;

                    fieldOrder.push(data);
                };
				/*
                MODx.Ajax.request({
                    url: ModFormDataManager.config.connector_url
                    ,params: {
                        action: that.config.save_action + 'Order'
                        ,data: Ext.util.JSON.encode(fieldOrder)
                    }
                });
				*/
    		}
    	});
    }
});
Ext.reg('mod-formdatamanager-layoutgrid', ModFormDataManager.layoutgrid);
