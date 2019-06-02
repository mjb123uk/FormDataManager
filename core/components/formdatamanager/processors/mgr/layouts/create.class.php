<?php
/**
 * Create a layout
 *
 * @package formdatamanager
 * @subpackage processors
 */
 
class FormDataManagerLayoutCreateProcessor extends modObjectCreateProcessor
{
    public $classKey = 'FdmLayouts';
    public $languageTopics = array('FormDataManager:default');
	
    public function beforeSet()
    {
		
        $formid = $this->getProperty('id');
		$formname = $this->getProperty('formname');
		if ($formid == "formit") {
			$id = $formname;
			$lf = "formname";
			$formtype = "formit";
			$formid = 0;
		}
		else {
			$id = $formid;
			$lf = "formid";
			$formtype = "formz";
		}
				
        if (empty($id)) {
            $this->addFieldError('id',$this->modx->lexicon('formdatamanager_layout_err_ns_name'));
        } else {
            //if ($this->doesAlreadyExist(array($lf => $id))) {
            //   $this->addFieldError($id,$this->modx->lexicon('formdatamanager_layout_err_ae'));
            //}
        }		
					
		$this->setProperty('sortorder', $order);
		$this->setProperty('formid',$formid);
		$this->setProperty('formtype',$formtype);
		$this->setProperty('formname',$formname);
		$this->setProperty('formfld_data',$this->getProperty('data'));
		$this->setProperty('selectionfield',$this->getProperty('selectionfield'));
		$this->setProperty('templateid',$this->getProperty('templateid'));
		$this->setProperty('createdon',date('Y-m-d H:i:s',time()));
		$this->setProperty('createdby',$this->modx->user->get('id'));
		
        return parent::beforeSet();
    }
}
return 'FormDataManagerLayoutCreateProcessor';