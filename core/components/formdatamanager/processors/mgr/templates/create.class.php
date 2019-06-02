<?php
/**
 * Create a layout record for a template
 *
 * @package formdatamanager
 * @subpackage processors
 */
 class FormDataManagerTemplateCreateProcessor extends modObjectCreateProcessor
{
    public $classKey = 'FdmLayouts';
    public $languageTopics = array('FormDataManager:default');
	
    public function beforeSet()
    {

		$formid = 0;
		$formtype = "template";		
		$tplname = $this->getProperty('templatename');
		
		if (empty($tplname)) {
            $this->addFieldError('id',$this->modx->lexicon('formdatamanager_layout_err_ns_name'));
        } else {
            //if ($this->doesAlreadyExist(array('id' => $id))) {
            //    $this->addFieldError('id',$this->modx->lexicon('formdatamanager_layout_err_ae'));
            //}
        }	

		$this->setProperty('formid',$formid);
		$this->setProperty('formtype',$formtype);
		$this->setProperty('formname',$tplname);
		
        return parent::beforeSet();
    }
}
return 'FormDataManagerTemplateCreateProcessor';