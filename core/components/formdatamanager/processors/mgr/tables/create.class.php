<?php
/**
 * Create a layout record for a table
 *
 * @package formdatamanager
 * @subpackage processors
 */
 class FormDataManagerTableCreateProcessor extends modObjectCreateProcessor
{
    public $classKey = 'FdmLayouts';
    public $languageTopics = array('FormDataManager:default');
	
    public function beforeSet()
    {

		$formid = 0;
		$formname = $this->getProperty('dbtables');	// tablename
		$id = $formname;
		$lf = "formname";
		$formtype = "table";
		
		if (empty($id)) {
            $this->addFieldError('id',$this->modx->lexicon('formdatamanager_layout_err_ns_name'));
        } else {
            //if ($this->doesAlreadyExist(array($lf => $id))) {
            //    $this->addFieldError($id,$this->modx->lexicon('formdatamanager_layout_err_ae'));
            //}
        }	

		//$data = $this->getProperty('data');
		$this->setProperty('formid',$formid);
		$this->setProperty('formtype',$formtype);
		$this->setProperty('formname',$formname);
		//$this->setProperty('formfld_data',$data);
		
        return parent::beforeSet();
    }
}
return 'FormDataManagerTableCreateProcessor';