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
		$selectionfield = "";
		$selectionfield = "";
		$templateid = 0;
		
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
			// formid can be formit or table
			$c->where(array('formtype' => $formid,'formname' => $formname));
			$formid = '"'.$formid.'"';
		}
		if (is_numeric($formid)) {
			$c->where(array('formtype' => 'formz','formid' => $formid));
			$hometab = "Formz";
		}
		$fdmdata = $this->modx->getCollection($classname, $c);
		if (!empty($fdmdata)) $layout = $fdmdata;
		unset($c);
		unset($fdmdata);
		
		if (count($layout)) {
			foreach($layout as $fdmd) {
				$fd = $fdmd->toArray();
				if (isset($fd['id'])) $layoutid = $fd['id'];
				if (isset($fd['selectionfield'])) $selectionfield = $fd['selectionfield'];
				if (isset($fd['selectionfield'])) $selectionfield = $fd['selectionfield'];
				if ( (empty($selectionfield)) || ($selectionfield == "N/A") ) $selectionfield = "";	// always clear in case null
				if (isset($fd['templateid'])) $templateid = $fd['templateid'];
				$data = json_decode($fd['formfld_data']);
				foreach($data as $ro) {
					$rows = json_decode($ro,TRUE);
					foreach($rows as $r) {
						$loflds[] = $r;
					}
				}
				$lastexportto = $fd['lastexportto'];
				if (!empty($lastexportto)) $lastexportto = date('Y-m-d H:i:s',strtotime($lastexportto) + 1);
			}
		}

		if (!empty($templateid)) {
			// get export date field from template layout record
			$c = $this->modx->newQuery($classname);
			$c->select($this->modx->getSelectColumns($classname, $classname));
			$c->where(array('id' => $templateid));
			$fdmdata = $this->modx->getCollection($classname, $c);
			if (!empty($fdmdata)) {
				foreach($fdmdata as $fdmd) {
					$fd = $fdmd->toArray();
					$selectionfield = trim($fd['selectionfield']);
					$found = 0;
					foreach ($loflds as $lofld) {
						if ($lofld['label'] == $selectionfield) {
							$found = 1;
							$selectionfield = trim($lofld['mapfield']);
							break;
						}
					}
				}
			}
			unset($c);
			unset($fdmdata);
		}
		
		$flds = array();
		$cms = array();
		
		if ( (!$istable) && (empty($templateid)) ) {
			$flds[] = "'senton'";
			$flds[] = "'ip_address'";
			$cms[] = "{ header: 'Created', width: 112, dataIndex: 'senton' }";
			$cms[] = "{ header: 'IP Address', width: 72, dataIndex: 'ip_address' }";
		}
		
		foreach($loflds as $lofld) {
			if ($lofld['include']) {		// only include if column wanted
				$w = $lofld['label'];
				$str = preg_replace('/[^A-Za-z0-9_-]/', '', $w);
				if ( ($templateid) && (!empty($lofld['mapfield'])) ) $w = $lofld['mapfield'];
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
				if ($templateid) $cms[] = "{ header: '".$lofld['label']."', width: $fw, dataIndex: '".$str."' }"; 				
				else $cms[] = "{ header: '".$lofld['coltitle']."', width: $fw, dataIndex: '".$str."' }";
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
		ModFormDataManager.config.selectionfield = "'.$selectionfield.'";
		ModFormDataManager.config.template = "'.$templateid.'";
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