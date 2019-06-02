<?php
/**
 * Reset a layout
 *
 * @package formdatamanager
 * @subpackage processors
 */
 
class FormDataManagerLayoutResetProcessor extends modObjectUpdateProcessor
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

		$this->setProperty('formfld_data',null);
		$this->setProperty('formfld_extra',null);
		$this->setProperty('selectionfield',null);
		$this->setProperty('templateid',0);
		$this->setProperty('createdon',null);
		$this->setProperty('createdby',0);
		$this->setProperty('editedon',null);
		$this->setProperty('editedby',0);
		$this->setProperty('lastexportfrom',null);
		$this->setProperty('lastexportto',null);
		$this->setProperty('lastautoexpfrom',null);
		$this->setProperty('lastautoexpto',null);
		
        return parent::beforeSet();
    }
}
return 'FormDataManagerLayoutResetProcessor';