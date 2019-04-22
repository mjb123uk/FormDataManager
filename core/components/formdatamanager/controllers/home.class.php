<?php

class FormDataManagerHomeManagerController extends modExtraManagerController {
	public $hasformz = 0;
	public $hasformit = 0;
    
    function __construct(modX &$modx, $config = array()) {
        parent::__construct($modx, $config);
        $this->config['namespace_assets_path'] = $modx->call('modNamespace','translatePath',array(&$modx, $this->config['namespace_assets_path']));
        $this->config['assets_url'] = $modx->getOption('formdatamanager.assets_url', null, $modx->getOption('assets_url').'components/formdatamanager/');
        $this->config['connector_url'] = $this->config['assets_url'].'connector.php';
    }
	
	function process(array $scriptProperties = array()) {
		#add css
		$this->addCss($this->config['assets_url'].'css/mgr.css');
		#add js
		if ($this->hasformz) $this->addJavascript($this->config['assets_url'].'js/widgets/formdatamanager.formzgrid.js');
		if ($this->hasformit) $this->addJavascript($this->config['assets_url'].'js/widgets/formdatamanager.formitgrid.js');
		$this->addJavascript($this->config['assets_url'].'js/widgets/formdatamanager.tablesgrid.js');
		$this->addJavascript($this->config['assets_url'].'js/widgets/formdatamanager.templatesgrid.js');        
		$this->addJavascript($this->config['assets_url'].'js/widgets/formdatamanager.homepanel.js');
        $this->addJavascript($this->config['assets_url'].'js/sections/home.js');
    }

    public function getLanguageTopics() {
        return array('formdatamanager:default');
    }

    public function checkPermissions() { return true;}

    function initialize() {
		
		$hometab = (isset($_GET['tn'])) ? $_GET['tn'] : "";
		$formzmodelpath = $this->modx->getOption('core_path') . 'components/formz/model/';
		if (is_dir($formzmodelpath)) $this->hasformz = 1;
		$formitmodelpath = $this->modx->getOption('core_path') . 'components/formit/model/';
		if (is_dir($formitmodelpath)) $this->hasformit = 1;
		$defTemplate = "";
		$packageName = "formdatamanager";
		$packagepath = $this->modx->getOption('core_path') . 'components/' . $packageName . '/';
		$modelpath = $packagepath . 'model/';
		$this->modx->addPackage($packageName, $modelpath);
		$classname = 'FdmLayouts';
		$c = $this->modx->newQuery($classname);
		$c->select($this->modx->getSelectColumns($classname, $classname));
		$c->where(array('formtype' => 'Template'));
		$count = $this->modx->getCount($classname, $c);
		if ($count == 1) {
			$fdmdata = $this->modx->getCollection($classname, $c);
			if (!empty($fdmdata)) $layout = $fdmdata;
			if (count($layout)) {
				foreach($layout as $fdmd) {
					$fd = $fdmd->toArray();
					if (isset($fd['formname'])) $defTemplate = $fd['formname'];
					break;
				}
			}
		}
		
        $this->addHtml('<script type="text/javascript">
        ModFormDataManager.config.connector_url = "'.$this->config['connector_url'].'";
		ModFormDataManager.config.hasformz = '.$this->hasformz.';
		ModFormDataManager.config.hasformit = '.$this->hasformit.';
		ModFormDataManager.config.gridheight = 400;
		ModFormDataManager.config.hometab = "'.$hometab.'";	
		ModFormDataManager.config.defaultTemplate = "'.$defTemplate.'";			
		ModFormDataManager.config.tbldata = [];
        </script>');
		$this->addJavascript($this->config['assets_url'].'js/formdatamanager.js');
    }
    
    function getTemplate($tpl) {
        return $this->config['namespace_path']."templates/default/{$tpl}";
    }
	
	function getTemplateFile() {
        return $this->getTemplate('home.tpl');
    }

}
?>
