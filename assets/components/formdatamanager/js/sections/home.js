Ext.onReady(function() {
	MODx.load({xtype:'mod-formdatamanager-page-home'});
});

ModFormDataManager.page.Home=function(config) {
	config = config || {};
	Ext.applyIf(config,{
		formpanel:'mod-formdatamanager-homepanel'
		,components:[{
			xtype:'mod-formdatamanager-homepanel'
			,renderTo:'mod-extra-formdatamanager'
		}]
	});
	ModFormDataManager.page.Home.superclass.constructor.call(this,config);
};
Ext.extend(ModFormDataManager.page.Home,MODx.Component);
Ext.reg('mod-formdatamanager-page-home',ModFormDataManager.page.Home);

ModFormDataManager.combo.Templates = function(config) {
	Ext.applyIf(config,{
		id: 'fdmTpName'
		,displayField: 'name'
		,valueField: 'name'
		,fields: ['name']
		,allowBlank: true
		,url: ModFormDataManager.config.connector_url
		,baseParams: {
			action: 'gettemplateslist'
			,forcombo: true
		}
	});
    ModFormDataManager.combo.Templates.superclass.constructor.call(this,config);
};
Ext.extend(ModFormDataManager.combo.Templates,MODx.combo.ComboBox);
Ext.reg('modx-combo-template',ModFormDataManager.combo.Templates); 

ModFormDataManager.combo.ActiveFilter = function(config) {
    Ext.applyIf(config,{
        store: new Ext.data.ArrayStore({
			id: 'fdmActiveFilter'
            ,fields: ['opt']
            ,data: [
                ['All']
                ,['Active']
                ,['Inactive']
            ]
        })
        ,mode: 'local'
        ,displayField: 'opt'
        ,valueField: 'opt'
    });	
    ModFormDataManager.combo.ActiveFilter.superclass.constructor.call(this,config);
};
Ext.extend(ModFormDataManager.combo.ActiveFilter,MODx.combo.ComboBox);
Ext.reg('modx-combo-activefilter',ModFormDataManager.combo.ActiveFilter); 

/**
 * Generates the SelectTemplate window.
 *
 * @class MODx.window.SelectTemplate
 * @extends MODx.Window
 * @param {Object} config An object of options.
 * @xtype modx-window-formdatamanager-create-field
 */
MODx.window.SelectTemplate = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        title: _('formdatamanager_selecttemplate_new')
        ,fields: [{
			xtype: 'modx-combo-boolean'
			,fieldLabel: _('formdatamanager_templates.usetemplate')
			,renderer: 'boolean'
			,value: 'No'
			,listeners:{
				 scope: this,
				 'select': this.useTemplateYN
			}
		}, {
            xtype: 'modx-combo-template'
			,fieldLabel: _('formdatamanager_templates.templatename')
			,name: 'tpname'
			,hiddenName: 'tpname'
			,anchor: '100%'
			,disabled: true
        }]
		,cancelBtnText: _('formdatamanager_cancel')
		,saveBtnText: _('formdatamanager_continue')
        ,keys: []
    });
    MODx.window.SelectTemplate.superclass.constructor.call(this,config);
};

Ext.extend(MODx.window.SelectTemplate,MODx.Window,{
    submit: function() {
        var v = this.fp.getForm().getValues();
        if (this.fp.getForm().isValid()) {
			this.fireEvent('success',v); 
        }
        return false;
    }
	,onShow: function() {
		var TpNameField = Ext.getCmp('fdmTpName');
		TpNameField.allowBlank = true;
		TpNameField.setValue("");
        TpNameField.disable();
	}
	,useTemplateYN: function(field, record, i) {
		var TpNameField = Ext.getCmp('fdmTpName');
        if (field.value) {
			TpNameField.allowBlank = false;
			TpNameField.setValue(ModFormDataManager.config.defaultTemplate);
            TpNameField.enable();
        } else {
			TpNameField.allowBlank = true;
			TpNameField.setValue("");
            TpNameField.disable();
        }
	}
});
Ext.reg('modx-window-formdatamanager-select-template',MODx.window.SelectTemplate);
