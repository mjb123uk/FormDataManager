<?php

class FormDataManagerLayoutManagerController extends modExtraManagerController {
	
    function __construct(modX &$modx, $config = array()) {
        parent::__construct($modx, $config);
        $this->config['namespace_assets_path'] = $modx->call('modNamespace','translatePath',array(&$modx, $this->config['namespace_assets_path']));
        $this->config['assets_url'] = $modx->getOption('formdatamanager.assets_url', null, $modx->getOption('assets_url').'components/formdatamanager/');
        $this->config['connector_url'] = $this->config['assets_url'].'connector.php';
    }
	
	function process(array $scriptProperties = array()) {
        #add js
		$this->addJavascript($this->config['assets_url'].'js/widgets/formdatamanager.layoutgrid.js');
        $this->addJavascript($this->config['assets_url'].'js/widgets/formdatamanager.layoutpanel.js');
        $this->addJavascript($this->config['assets_url'].'js/sections/layout.js');
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
		$istable = false;
		$istemplate = false;			
		$hometab = "FormIt";
		$selectionfield = "N/A";
		
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
		if ($formid == "template") {
			$istemplate = true;
			$formid = '"'.$formid.'"';
			$c->where(array('formtype' => 'template','formname' => $formname));
			$hometab = "Template";
		}
		$count = $this->modx->getCount($classname, $c);
		$fdmdata = $this->modx->getCollection($classname, $c);
		if (!empty($fdmdata)) $layout = $fdmdata;
		if (count($layout)) {
			foreach($layout as $fdmd) {
				$fd = $fdmd->toArray();
				if (isset($fd['id'])) $layoutid = $fd['id'];
				if ( ($istable) || ($istemplate) ){
					if (isset($fd['selectionfield'])) $selectionfield = $fd['selectionfield'];
				}
			}
		}
		// load any output functions
		$xfs = $this->modx->runSnippet("fdmViewExportFunctions",array());
		$afns = $xfs->fdmfunctionlist();
		$ofns = array(array(" "));
		foreach ($afns as $afn) {
			$ofns[] = array($afn);
		}
	
        $this->addHtml('<script type="text/javascript">
        ModFormDataManager.config.connector_url = "'.$this->config['connector_url'].'";
		ModFormDataManager.config.formid = '.$formid.';
		ModFormDataManager.config.formname = "'.$formname.'";
		ModFormDataManager.config.layoutid = '.$layoutid.';
		ModFormDataManager.config.selectionfield = "'.$selectionfield.'";		
		ModFormDataManager.config.hometab = "'.$hometab.'";
		ModFormDataManager.config.outputfunctions = '.json_encode($ofns).';		
        </script>');
		$this->addJavascript($this->config['assets_url'].'js/formdatamanager.js');
    }
    
    function getTemplate($tpl) {
        return $this->config['namespace_path']."templates/default/{$tpl}";
    }
	
	function getTemplateFile() {
        return $this->getTemplate('layout.tpl');
    }

}
?>