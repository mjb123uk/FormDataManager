ModFormDataManager.maptemplategrid = function(config) {
	config=config || {};
	Ext.applyIf(config,{
		id:'mod-formdatamanager-maptemplategrid'
		,url: ModFormDataManager.config.connector_url
		,baseParams:{
			action:'GetMapFldData'
			,formid: ModFormDataManager.config.formid
			,formname: ModFormDataManager.config.formname
			,tpl: ModFormDataManager.config.template
			,newtpl: ModFormDataManager.config.newtpl
		}
		,fields:['id','order','label','type','include','mapfield','default','tplfield','ofn']
		,paging:false		// set to false as otherwise changes can be lost when paginating
		,autoheight: false
		,maxHeight: 500
		,primaryKey: 'order'
		,remoteSort:false
		,ddGroup:'ddGrid'+config.formid
		,enableDragDrop: false
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
			,disabled: true
		}, {
			header:_('formdatamanager_fldgrid.type')
			,dataIndex:'type'
			,sortable:false
			,width:20
			,disabled: true
		}, {
			header:_('formdatamanager_fldgrid.include')
			,dataIndex:'include'
			,sortable:false
			,width:20
			,editor: { xtype: 'modx-combo-boolean', renderer: 'boolean'	}
			//,editable: false
		}, {
			header:_('formdatamanager_fldgrid.mapfield')
			,dataIndex:'mapfield'
			,sortable:false
			,width:35
			,editor: { xtype: 'modx-combo-mapfieldname' }
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
		}, {
			header:_('formdatamanager_fldgrid.tplfield')
			,dataIndex:'tplfield'
			,sortable:false
			,hidden:true
		}]
		,tbar : new Ext.Toolbar({
			id : 'fdmlgtbar'
			//,hidden : true
			,items: [{
				text: _('formdatamanager_dummyfield_add')
				,cls: 'primary-button'
				,handler: this.newDummyFieldAdd
				,scope: this
				,hidden: false
			}, {
				xtype: 'tbtext'
				,text: _('formdatamanager_tables.selfldtext')+':'
				,style: 'font-size: 12px'
			}, {
				xtype: 'textfield'
				,id: 'selFldName'
				,anchor: '100%'
				,disabled: true
				,value: ModFormDataManager.config.selectionfield
			}]
		})
	});
	ModFormDataManager.maptemplategrid.superclass.constructor.call(this,config);
	this.fdmmtgRecord = Ext.data.Record.create(config.fields);
	
	// Setup Toolbar & Reorder by Drag and Drop
	this.on('render', this.setUp, this);
	
};
Ext.extend(ModFormDataManager.maptemplategrid, MODx.grid.Grid, {
    getMenu: function (grid, rowIndex, e) {
        /*
		return [{
            text: _('formdatamanager.field.help')
            ,handler: this.updateField
        }];
		*/
    }
	,setUp: function (grid) {
		//var tb = grid.getTopToolbar();		
		//this.dragAndDrop(grid);

		var ds = grid.getStore();
		ds.addListener('load',this.loaded,this);

		grid.on('beforeedit', function(editor) {
			if ( (editor.column == 4) && (editor.record.get('tplfield') == 1) ) return false;
		});
	}
	,loaded: function(ds) {
		// Grid Store
		var dc = ds.getCount();
		mappedfields = [];
		for (i=0; i<dc; i++) {
			var mf = ds.data.items[i].data.mapfield;
			if (mf != "") mappedfields.push(mf);
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
						var cl="", ok=false, x=0, maxid=0, maxdf = 0;
						// set maxdf - next DummyField label no.
						for (var i=0; i<sdi.length; i++) {
							x = sdi[i].data.id;
							if (x > maxid) maxid = x;						
							cl = sdi[i].data.tplfield;
							if (!cl) maxdf++;
						}
						maxid = maxid+1;
						maxdf = maxdf+1;
						
						var rec = new this.fdmmtgRecord({
							id: maxid
							,order: sdi.length
							,label: r.dfcolumntitle
							,type: r.dftype
							,include: 1
							//,mapfield: r.fdmMapFields
							,default: r.dfdefault
							,tplfield: 0
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
Ext.reg('mod-formdatamanager-maptemplategrid', ModFormDataManager.maptemplategrid);

/*
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
*/

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

ModFormDataManager.combo.MapFieldNames = function(config) {
    config = config || {};
	Ext.applyIf(config,{
		id: 'fdmMapFields'
		,name: 'fdmMapFields'
		,hiddenName: 'fdmMapFields'
		,displayField: 'name'
		,valueField: 'name'
		,fields: ['name']
		,allowBlank: true
		// ,pageSize: 20
		,url: ModFormDataManager.config.connector_url
		,baseParams: {
			action: 'getflddata'
			,formid: ModFormDataManager.config.formid
			,formname: ModFormDataManager.config.formname
			,forcombo: true
		}
	});
    ModFormDataManager.combo.MapFieldNames.superclass.constructor.call(this,config);
	
	this.on('afterrender',this.ready,this);
	
	this.on('change', this.changed, this);
};
Ext.extend(ModFormDataManager.combo.MapFieldNames,MODx.combo.ComboBox, {
	changed: function(e,nv,ov) {
		var ds = this.getStore();
		var sl = ds.getCount();
		// locate new value & remove
		var op = ds.find("name",nv);
		if (op != "") ds.removeAt(op);
		if (ov != "") {
			var rt = ds.recordType;
			var na = new rt({'name': ov});
			ds.add(na);
		}
	}
	,ready: function(e) {
		var ds = e.getStore();
		//currentmappedfield = e.gridEditor.record.data.mapfield;
		ds.addListener('load',this.loaded,this);
	}
	,loaded: function(ds) {
		// Combo Store
		var dc = ds.getCount();
		var rt = ds.recordType;
		var na = new rt({'name': ""});
        ds.insert(0,na); 
		// remove already mapped fields
		for ( var i = ds.data.length; i--; ) {
			if ( mappedfields.includes(ds.data.items[i].data.name) ) ds.removeAt(i);
		}
	}
	
});
Ext.reg('modx-combo-mapfieldname',ModFormDataManager.combo.MapFieldNames); 

/*
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
*/

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
			//xtype: 'modx-combo-fieldtype'
			xtype: 'textfield'
			,fieldLabel: _('formdatamanager_fldgrid.type')
			,name: 'dftype'
			,value: 'text'
			,hidden: true
		}, /* don't include as it creates a new combo box which doesn't update the one used in the grid
		{
			xtype: 'modx-combo-mapfieldname'
			,fieldLabel: _('formdatamanager_fldgrid.mapfield')
			,name: 'dfmapfield'
			,anchor: '100%'	
		}, */ {
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
