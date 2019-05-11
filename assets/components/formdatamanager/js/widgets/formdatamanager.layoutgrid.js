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
		,fields:['id','order','label','type','include','coltitle','default','ofn']
		,paging:false		// set to false as otherwise changes can be lost when paginating
		,autoheight: false
		,maxHeight: 500
		,primaryKey: 'order'
		,remoteSort:false
		,ddGroup:'ddGrid'+config.formid
		,enableDragDrop: (ModFormDataManager.config.formid != "template" ) ? true : false
		,disabled: (ModFormDataManager.config.formid == "template" ) ? true : false
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
			,hidden: (ModFormDataManager.config.formid == "template" ) ? true : false
		}, {
			header:_('formdatamanager_fldgrid.outputfunction')
			,dataIndex:'ofn'
			,sortable:false
			,width:20
			,editor: { xtype: 'modx-combo-outputfunctions' }
		}, {
			header:_('formdatamanager_fldgrid.default')
			,dataIndex:'default'
			,sortable:false
			,width: 40
			,editor: { xtype: 'textfield' }
		}]
		,tbar : new Ext.Toolbar({
			id : 'fdmlgtbar'
			//,hidden : true
			,items: [{
				text: _('formdatamanager_dummyfield_add')
				,cls: 'primary-button'
				,handler: this.newDummyFieldAdd
				,scope: this
				,hidden: true
			}, {
				xtype: 'tbtext'
				,text: _('formdatamanager_tables.selfldname')+':'
				,style: 'font-size: 12px'
				,hidden : true
			}, {
				xtype: 'modx-combo-fields'
				,id: 'selFldName'
				,anchor: '100%'
				,allowBlank: true
				,hidden : true
			}]
		})
	});
	ModFormDataManager.layoutgrid.superclass.constructor.call(this,config);
	this.fdmlgRecord = Ext.data.Record.create(config.fields);
	
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
		var tb = grid.getTopToolbar();
		// enable top tool bar if 'Custom Table'
		if ( (ModFormDataManager.config.hometab == "Table") || (ModFormDataManager.config.hometab == "Template")  ) {
			tb.items.items[1].show();
			tb.items.items[2].show();
		}
		if (ModFormDataManager.config.formid != "template") {
			tb.items.items[0].show();			
			this.dragAndDrop(grid);
		}
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
	,newDummyFieldAdd: function(btn, e) {
		if (!window.fdmDummyFieldWindow) {
			fdmDummyFieldWindow = new MODx.window.CreateDummyField({
				listeners: {			
					'success': { fn:function(r) {
						var s = this.getStore();
						var sdi = s.data.items;
						var dflbl = r.dfcolumntitle;
						dflbl = dflbl.replace(" ","",dflbl);
						// check label unique
						var w="", ww="", cl="", ct="", ok=false, x=0, maxid=0, maxdf = 0;
						// set maxdf - next DummyField label no.
						for (var i=0; i<sdi.length; i++) {					
							cl = sdi[i].data.label;
							if (cl.substr(0,10) == "DummyField") {
								xx = parseInt(cl.substr(10));
								if (xx > maxdf) maxdf = xx;
							}
						}
						for (var z=0; z<99; z++) {
							w = dflbl;
							ok = true;
							for (var i=0; i<sdi.length; i++) {
								ww = z.toString();
								if (z>0) w = dflbl+ww;
								x = sdi[i].data.id;
								if (x > maxid) maxid = x;
								ct = sdi[i].data.coltitle;
								if (w == ct) {
									ok = false;
									break;
								}
							}
							if (ok) break;
						}
						if (ww == 0) ww = "";
						maxid = maxid+1;
						maxdf = maxdf+1;
						
						var rec = new this.fdmlgRecord({
							id: maxid
							,order: sdi.length
							,label: "DummyField"+maxdf
							,type: r.dftype
							,include: true
							,coltitle: w
							,default: r.dfdefault
							,ofn: ""
						});
						s.add(rec);
					},scope:this }
				}
			});
		}
		else {
			window.fdmDummyFieldWindow.fp.getForm().reset();
		}	
        window.fdmDummyFieldWindow.show(e.target);
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

ModFormDataManager.combo.OutputFunctions = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        store: new Ext.data.ArrayStore({
            id: 0
            ,fields: ['fname']
			,allowBlank: true
			,data: ModFormDataManager.config.outputfunctions
        })
        ,mode: 'local'
        ,displayField: 'fname'
        ,valueField: 'fname'
    });
    ModFormDataManager.combo.OutputFunctions.superclass.constructor.call(this,config);
};
Ext.extend(ModFormDataManager.combo.OutputFunctions,MODx.combo.ComboBox);
Ext.reg('modx-combo-outputfunctions',ModFormDataManager.combo.OutputFunctions);

ModFormDataManager.combo.Fields = function(config) {
    config = config || {};
	if ( (ModFormDataManager.config.hometab == "Table") || (ModFormDataManager.config.hometab == "Template") ) {
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
			,value: ModFormDataManager.config.selectionfield
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

/**
 * Generates the DummyField window.
 *
 * @class MODx.window.DummyField
 * @extends MODx.Window
 * @param {Object} config An object of options.
 * @xtype modx-window-formdatamanager-create-field
 */
MODx.window.CreateDummyField = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        title: _('formdatamanager_dummyfield_new')
        ,fields: [
		{
            xtype: 'textfield'
            ,fieldLabel: _('formdatamanager_dummyfield_columntitle')
            ,name: 'dfcolumntitle'
            ,anchor: '100%'
            ,allowBlank: false			
        }, {
			xtype: 'modx-combo-fieldtype'
			,fieldLabel: _('formdatamanager_fldgrid.type')
			,name: 'dftype'
			,value: 'text'
		}, {
            xtype: 'textfield'
            ,fieldLabel: _('formdatamanager_fldgrid.default')
            ,name: 'dfdefault'
            ,anchor: '100%'		
        }]
        ,keys: []
    });
    MODx.window.CreateDummyField.superclass.constructor.call(this,config);
};
Ext.extend(MODx.window.CreateDummyField,MODx.Window,{
    submit: function() {
        var v = this.fp.getForm().getValues();
		
        if (this.fp.getForm().isValid()) {
            if (this.fireEvent('success',v)) {
                this.fp.getForm().reset();
                this.hide();
                return true;
            }
        }
        return false;
    }

});
Ext.reg('modx-window-formdatamanager-create-field',MODx.window.CreateDummyField);
