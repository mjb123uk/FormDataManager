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
			,editor: { xtype: 'modx-combo-fieldtype', renderer: true}
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
		,tbar : new Ext.Toolbar({
			id : 'fdmlgtbar'
			,hidden : true
			,items: [{
				xtype: 'tbtext'
				,text: _('formdatamanager_tables.exfldname')+':'
				,style: 'font-size: 12px'
			}, {
				xtype: 'modx-combo-fields'
				,name: 'exfldname'
				,anchor: '100%'
				,allowBlank: true
			}]
		})
	});
	ModFormDataManager.layoutgrid.superclass.constructor.call(this,config);
	
	// Setup Toolbar & Reorder by Drag and Drop
	this.on('render', this.setUp, this);
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
	,setUp: function (grid) {
		// enable top tool bar if 'Custom Table'
		if (ModFormDataManager.config.hometab == "Table") {
			var tb = grid.getTopToolbar();
			tb.show();
		}
		this.dragAndDrop(grid);
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

ModFormDataManager.combo.FieldTypes = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        store: new Ext.data.ArrayStore({
            id: 0
            ,fields: ['id','type']
            ,data: [
                ['text','text']
                ,['textarea','textarea']
                ,['date','date']
                ,['number','number']
            ]
        })
        ,mode: 'local'
        ,displayField: 'type'
        ,valueField: 'id'
    });
    ModFormDataManager.combo.FieldTypes.superclass.constructor.call(this,config);
};
Ext.extend(ModFormDataManager.combo.FieldTypes,MODx.combo.ComboBox);
Ext.reg('modx-combo-fieldtype',ModFormDataManager.combo.FieldTypes);    

ModFormDataManager.combo.Fields = function(config) {
    config = config || {};
	if (ModFormDataManager.config.hometab == "Table") {
		Ext.applyIf(config,{
			id: 'fdmCtFields'
			,name: 'fdmCtFields'
			,hiddenName: 'fdmCtFields'
			,displayField: 'name'
			,valueField: 'name'
			,fields: ['name']
			//,typeAhead: true
			//,minChars: 1
			//,editable: true
			,allowBlank: true
			// ,pageSize: 20
			,url: ModFormDataManager.config.connector_url
			,baseParams: {
				action: 'tables/gettablefields'
				,formname: ModFormDataManager.config.formname
			}
			,value: ModFormDataManager.config.fldextra
		});
	}
	else {
		Ext.applyIf(config,{
			store: new Ext.data.ArrayStore({
				id: 0
				,fields: ['name']
				,data: []
			})
			,mode: 'local'
			,displayField: 'type'
			,valueField: 'name'
		});
	}
    ModFormDataManager.combo.Fields.superclass.constructor.call(this,config);
};
Ext.extend(ModFormDataManager.combo.Fields,MODx.combo.ComboBox);
Ext.reg('modx-combo-fields',ModFormDataManager.combo.Fields);  
