<?php
/**
 * Edit a layout record for a template
 *
 * @package formdatamanager
 * @subpackage processors
 */
 class FormDataManagerTemplateEditProcessor extends modObjectUpdateProcessor
{
    public $classKey = 'FdmLayouts';
    public $languageTopics = array('FormDataManager:default');
	
    public function beforeSet()
    {

        $formid = $this->getProperty('id');	
		if (empty($formid)) {
            $this->addFieldError('id',$this->modx->lexicon('formdatamanager_layout_err_ns'));
        } else {
            if (!$this->doesAlreadyExist(array('id' => $formid))) {
                $this->addFieldError($formid,$this->modx->lexicon('formdatamanager_layout_err_nf'));
            }
        }	
		$wdata = $this->getProperty('formfld_data');
		$fields = explode(",",$this->getProperty('templatefields'));
		$ftypes = explode(",",$this->getProperty('templatefldtypes'));
		$defaults = explode(",",$this->getProperty('templatedefaults'));	
		$tpldata = array();
		for ($i = 0; $i < count($fields); $i++) {
			$w = array();
			$w['id'] = $i+1;
			$w['order'] = $i;
			$w['label'] = trim($fields[$i]);
			$wft = isset($ftypes[$i]) ? trim($ftypes[$i]) : "text";
			if (empty($wft)) $wft = "text";
			$w['type'] = $wft;
			$w['include'] = 1;
			$w['coltitle'] = trim($fields[$i]);
			$wfd = isset($defaults[$i]) ? trim($defaults[$i]) : "";
			$w['default'] = $wfd;
			$tpldata[] = $w;
		}
		$w = json_encode($tpldata);
		$ww = array();
		$ww["data"] = $w;
		$w = json_encode($ww);
		$this->setProperty('formfld_data',$w);
		$w = $this->getProperty('templatemapdata');		
		$this->setProperty('formfld_extra',$w);
		$w = $this->getProperty('templateselectfld');
		if (empty($w)) $w = "N/A";
		$this->setProperty('selectionfield',$w);
		$this->setProperty('editedon',date('Y-m-d H:i:s',time()));
		$this->setProperty('editedby',$this->modx->user->get('id'));

        return parent::beforeSet();
    }
}
return 'FormDataManagerTemplateEditProcessor';