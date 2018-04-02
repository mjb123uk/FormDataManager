<?php

class FormDataManagerViewdataManagerController extends modExtraManagerController {
	
    function __construct(modX &$modx, $config = array()) {
        parent::__construct($modx, $config);
        $this->config['namespace_assets_path'] = $modx->call('modNamespace','translatePath',array(&$modx, $this->config['namespace_assets_path']));
        $this->config['assets_url'] = $modx->getOption('formdatamanager.assets_url', null, $modx->getOption('assets_url').'components/formdatamanager/');
        $this->config['connector_url'] = $this->config['assets_url'].'connector.php';
    }
	
	function process(array $scriptProperties = array()) {
        #add js
		$this->addJavascript($this->config['assets_url'].'js/widgets/formdatamanager.viewdatagrid.js');
        $this->addJavascript($this->config['assets_url'].'js/widgets/formdatamanager.viewdatapanel.js');
        $this->addJavascript($this->config['assets_url'].'js/sections/viewdata.js');
    }

    public function getLanguageTopics() {
        return array('formdatamanager:default');
    }

    public function checkPermissions() { return true;}

    function initialize() {
		$formid = trim($_GET['id']);
		$formname = trim($_GET['fnm']);
		$gh = (isset($_GET['gh'])) ? trim($_GET['gh']) : 500;
		if ($gh == "undefined") $gh = 500;
		$layoutid = 0;
		$layout = array();
		$lastexportto = "";
		$istable = false;
		$hometab = "FormIt";
		$fldextra = "";
		
		$packageName = "formdatamanager";
		$packagepath = $this->modx->getOption('core_path') . 'components/' . $packageName . '/';
		$modelpath = $packagepath . 'model/';
		$this->modx->addPackage($packageName, $modelpath);
		$classname = 'FdmLayouts';
		$c = $this->modx->newQuery($classname);
		$c->select($this->modx->getSelectColumns($classname, $classname));
		if ($formid == 'table') {
			$istable = true;
			$hometab = "Table";		
		}
		if ( ($formid == 'formit') || ($istable) ) {
			$formid = '"'.$formid.'"';
			$c->where(array('formname' => $formname));
		}
		else {
			$c->where(array('formid' => $formid));
			$hometab = "Formz";
		}
		$fdmdata = $this->modx->getCollection($classname, $c);
		if (!empty($fdmdata)) $layout = $fdmdata;
		if (count($layout)) {
			foreach($layout as $fdmd) {
				$fd = $fdmd->toArray();
				if (isset($fd['id'])) $layoutid = $fd['id'];
				if ($istable) {
					if (isset($fd['formfld_extra'])) $fldextra = $fd['formfld_extra'];
					if ( (empty($fldextra)) || ($fldextra == "N/A") ) $fldextra = "";	// always clear in case null
				}
				$data = json_decode($fd['formfld_data']);
				foreach($data as $ro) {
					$rows = json_decode($ro,TRUE);
					foreach($rows as $r) {
						$loflds[] = $r;
					}
				}
				$lastexportto = $fd['lastexportto'];
			}
		}

		$flds = array();
		$cms = array();
		
		if (!istable) {
			$flds[] = "'senton'";
			$flds[] = "'ip_address'";
			$cms[] = "{ header: 'Created', width: 112, dataIndex: 'senton' }";
			$cms[] = "{ header: 'IP Address', width: 72, dataIndex: 'ip_address' }";
		}
		
		foreach($loflds as $lofld) {
			if ($lofld['include']) {		// only include if column wanted		
				$str = preg_replace('/[^A-Za-z0-9_-]/', '', $lofld['label']);
				$flds[] = "'".$str."'";
				$fw = "200";
				switch ($lofld['type']) {
					case 'text':
						$fw = "200";
						break;
					case 'textarea':
						$fw = "300";
						break;
					case 'date':
						$fw = "160";					
						/*
							//renderer: Ext.util.Format.dateRenderer('m/d/Y'),
							xtype: 'datecolumn', // use xtype instead of renderer
							format: 'M/d/Y' // configuration property for Ext.grid.DateColumn
						*/
						//$fw = "160, xtype: 'datecolumn', format: 'd M Y'";						
						break;
					case 'number':						
						$fw = "120";
						if (strtolower($str) == "id") $fw = "80";
						$fw .= ", align: 'right'";
						break;						
				}				
				$cms[] = "{ header: '".$lofld['coltitle']."', width: $fw, dataIndex: '".$str."' }";
			}
		}
		
		$gs = "var viewdataFields = [".implode(',',$flds)."];\n";
		$gs .= "var viewdataColumnModel = new Ext.grid.ColumnModel([".implode(',',$cms)."]);\n";
		$gs .= "var fdmgh = ".$gh.";\n";
		
        $this->addHtml('<script type="text/javascript">
        ModFormDataManager.config.connector_url = "'.$this->config['connector_url'].'";
		ModFormDataManager.config.formid = '.$formid.';
		ModFormDataManager.config.formname = "'.$formname.'";
		ModFormDataManager.config.layoutid = '.$layoutid.';
		ModFormDataManager.config.fldextra = "'.$fldextra.'";			
		ModFormDataManager.config.lastexportto = "'.$lastexportto.'";
		ModFormDataManager.config.hometab = "'.$hometab.'";
		'.$gs.'
        </script>');
		$this->addJavascript($this->config['assets_url'].'js/formdatamanager.js');
    }
    
    function getTemplate($tpl) {
        return $this->config['namespace_path']."templates/default/{$tpl}";
    }
	
	function getTemplateFile() {
        return $this->getTemplate('viewdata.tpl');
    }

}
?>