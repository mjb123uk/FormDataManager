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
		#add js
		if ($this>hasformz) $this->addJavascript($this->config['assets_url'].'js/widgets/formdatamanager.formzgrid.js');
		if ($this->hasformit) $this->addJavascript($this->config['assets_url'].'js/widgets/formdatamanager.formitgrid.js');
        $this->addJavascript($this->config['assets_url'].'js/widgets/formdatamanager.homepanel.js');
        $this->addJavascript($this->config['assets_url'].'js/sections/home.js');
    }

    public function getLanguageTopics() {
        return array('formdatamanager:default');
    }

    public function checkPermissions() { return true;}

    function initialize() {
		$formzmodelpath = $this->modx->getOption('core_path') . 'components/formz/model/';
		if (is_dir($formzmodelpath)) $this->hasformz = 1;
		$formitmodelpath = $this->modx->getOption('core_path') . 'components/formit/model/';
		if (is_dir($formitmodelpath)) $this->hasformit = 1;
		
        $this->addHtml('<script type="text/javascript">
        ModFormDataManager.config.connector_url = "'.$this->config['connector_url'].'";
		ModFormDataManager.config.hasformz = '.$this->hasformz.';
		ModFormDataManager.config.hasformit = '.$this->hasformit.';
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
