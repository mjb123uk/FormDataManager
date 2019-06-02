<?php
/**
 * Update a layout
 *
 * @package formdatamanager
 * @subpackage processors
 */
 
class FormDataManagerLayoutUpdateProcessor extends modObjectUpdateProcessor
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
		$wtype = $this->getProperty('formtype'); 
		$ntbl = false;
		if ($wtype == "table") {
			$wdata = $this->getProperty('formfld_data');
			if (empty($wdata)) $ntbl = true;
		}
		
		$this->setProperty('formfld_data',$this->getProperty('data'));
		$this->setProperty('selectionfield',$this->getProperty('selectionfield'));
		$this->setProperty('templateid',$this->getProperty('templateid'));
		if ($ntbl) {
			$this->setProperty('createdon',date('Y-m-d H:i:s',time()));
			$this->setProperty('createdby',$this->modx->user->get('id'));
		}
		else {
			$this->setProperty('editedon',date('Y-m-d H:i:s',time()));
			$this->setProperty('editedby',$this->modx->user->get('id'));
		}

        return parent::beforeSet();
    }
}
return 'FormDataManagerLayoutUpdateProcessor';