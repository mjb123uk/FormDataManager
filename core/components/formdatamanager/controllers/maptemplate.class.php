<?php

class FormDataManagerMapTemplateManagerController extends modExtraManagerController {
	
    function __construct(modX &$modx, $config = array()) {
        parent::__construct($modx, $config);
        $this->config['namespace_assets_path'] = $modx->call('modNamespace','translatePath',array(&$modx, $this->config['namespace_assets_path']));
        $this->config['assets_url'] = $modx->getOption('formdatamanager.assets_url', null, $modx->getOption('assets_url').'components/formdatamanager/');
        $this->config['connector_url'] = $this->config['assets_url'].'connector.php';
    }
	
	function process(array $scriptProperties = array()) {
        #add js
		$this->addJavascript($this->config['assets_url'].'js/widgets/formdatamanager.maptemplategrid.js');
        $this->addJavascript($this->config['assets_url'].'js/widgets/formdatamanager.maptemplatepanel.js');
        $this->addJavascript($this->config['assets_url'].'js/sections/maptemplate.js');
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
		$hometab = "FormIt";
		$selectionfield = "";
		$template = (isset($_GET['tpn'])) ? trim($_GET['tpn']) : 0;
		$newtpl = 1;
		if (empty($template)) {
			$template = (isset($_GET['tpl'])) ? trim($_GET['tpl']) : 0;
			$newtpl = 0;
		}	
		
		$packageName = "formdatamanager";
		$packagepath = $this->modx->getOption('core_path') . 'components/' . $packageName . '/';
		$modelpath = $packagepath . 'model/';
		$this->modx->addPackage($packageName, $modelpath);
		$classname = 'FdmLayouts';

		$c = $this->modx->newQuery($classname);
		$c->select($this->modx->getSelectColumns($classname, $classname));
		if ($newtpl) $c->where(array('formtype' => 'template','formname' => $template));
		else $c->where(array('id' => $template));
		$tcount = $this->modx->getCount($classname, $c);
		if ($tcount<1) return $this->failure($this->modx->lexicon('formdatamanager_maptemplatelayout_tplerror'));
		$tplid = 0;
		$tplsf = "";
		$tpldata = $this->modx->getCollection($classname, $c);
		foreach($tpldata as $fdmd) {
			$fd = $fdmd->toArray();
			$tplid = $fd['id'];
			$tplsf = $fd['selectionfield'];
		}
		unset($c);
		unset($tpldata);
		// template set if new
		if ($newtpl) {
			$template = $tplid;
			$selectionfield = $tplsf;
		}
		else {
			$selectionfield = $tplsf;
		}
		// Now load form / table
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
		$count = $this->modx->getCount($classname, $c);
		$fdmdata = $this->modx->getCollection($classname, $c);
		if (!empty($fdmdata)) $layout = $fdmdata;
		if (count($layout)) {
			foreach($layout as $fdmd) {
				$fd = $fdmd->toArray();
				if (isset($fd['id'])) $layoutid = $fd['id'];
				if ( (!$newtpl) && (!empty($fd['formfld_data'])) ) {
					if (!empty($fd['selectionfield'])) $selectionfield = $fd['selectionfield'];
					if (!empty($fd['templateid'])) $template = $fd['templateid'];
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
		ModFormDataManager.config.template = "'.$template.'";
		ModFormDataManager.config.newtpl = '.$newtpl.';		
		ModFormDataManager.config.hometab = "'.$hometab.'";
		ModFormDataManager.config.outputfunctions = '.json_encode($ofns).';
        </script>');
		$this->addJavascript($this->config['assets_url'].'js/formdatamanager.js');
    }
    
    function getTemplate($tpl) {
        return $this->config['namespace_path']."templates/default/{$tpl}";
    }
	
	function getTemplateFile() {
        return $this->getTemplate('maptemplate.tpl');
    }

}
?>