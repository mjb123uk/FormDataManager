<?php

class FormDataManagerHomeManagerController extends modExtraManagerController {
    
    function __construct(modX &$modx, $config = array()) {
        parent::__construct($modx, $config);
        $this->config['namespace_assets_path'] = $modx->call('modNamespace','translatePath',array(&$modx, $this->config['namespace_assets_path']));
        $this->config['assets_url'] = $modx->getOption('formdatamanager.assets_url', null, $modx->getOption('assets_url').'components/formdatamanager/');
        $this->config['connector_url'] = $this->config['assets_url'].'connector.php';
    }
	
	function process(array $scriptProperties = array()) {
        #add js
		$this->addJavascript($this->config['assets_url'].'js/widgets/formdatamanager.homegrid.js');
        $this->addJavascript($this->config['assets_url'].'js/widgets/formdatamanager.homepanel.js');
        $this->addJavascript($this->config['assets_url'].'js/sections/home.js');
    }

    public function getLanguageTopics() {
        return array('formdatamanager:default');
    }

    public function checkPermissions() { return true;}

    function initialize() {
		
		// temp until can find better way to set this
		$formzurl = str_replace('formdatamanager','formz',$this->config['connector_url']);
		
        $this->addHtml('<script type="text/javascript">
        ModFormDataManager.config.connector_url = "'.$this->config['connector_url'].'";
		ModFormDataManager.config.formzconnector_url = "'.$formzurl.'";
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
