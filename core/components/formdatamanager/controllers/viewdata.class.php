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
		$layoutid = 0;
		$layout = array();
		$lastexportto = "";
		$istable = false;
		
		$packageName = "formdatamanager";
		$packagepath = $this->modx->getOption('core_path') . 'components/' . $packageName . '/';
		$modelpath = $packagepath . 'model/';
		$this->modx->addPackage($packageName, $modelpath);
		$classname = 'FdmLayouts';
		$c = $this->modx->newQuery($classname);
		$c->select($this->modx->getSelectColumns($classname, $classname));
		if ($formid == 'table') $istable = true;
		if ( ($formid == 'formit') || ($istable) ) {
			$formid = '"'.$formid.'"';
			$c->where(array('formname' => $formname));
		}
		else $c->where(array('formid' => $formid));
		$fdmdata = $this->modx->getCollection($classname, $c);
		if (!empty($fdmdata)) $layout = $fdmdata;
		if (count($layout)) {
			foreach($layout as $fdmd) {
				$fd = $fdmd->toArray();
				if (isset($fd['id'])) $layoutid = $fd['id'];
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
				$cms[] = "{ header: '".$lofld['coltitle']."', dataIndex: '".$str."' }";
			}
		}
		
		$gs = "var viewdataFields = [".implode(',',$flds)."];";
		$gs .= "var viewdataColumnModel = new Ext.grid.ColumnModel([".implode(',',$cms)."]);";
		
        $this->addHtml('<script type="text/javascript">
        ModFormDataManager.config.connector_url = "'.$this->config['connector_url'].'";
		ModFormDataManager.config.formid = '.$formid.';
		ModFormDataManager.config.formname = "'.$formname.'";
		ModFormDataManager.config.layoutid = '.$layoutid.';
		ModFormDataManager.config.lastexportto = "'.$lastexportto.'";
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