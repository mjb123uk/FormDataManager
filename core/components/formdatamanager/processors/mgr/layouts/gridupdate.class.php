<?php
/**
 * Update a layout (grid edit)
 *
 * @package formdatamanager
 * @subpackage processors
 */
 
class FormDataManagerLayoutGridUpdateProcessor extends modObjectUpdateProcessor
{
    public $classKey = 'FdmLayouts';
    public $languageTopics = array('FormDataManager:default');
	
	public function initialize()
	{
		$data = $this->getProperty('data');
        if (empty($data)) return $this->modx->lexicon('invalid_data');
        $data = $this->modx->fromJSON($data);
        if (empty($data)) return $this->modx->lexicon('invalid_data');
        $this->setProperties($data);
		$this->unsetProperty('data');
		$layoutid = $this->getProperty('layoutid');
		if ($layoutid == 0) {
			// create new layout record
			$layout = $this->modx->newObject('FdmLayouts');
			$formid = $this->getProperty('id');
			$formtype = $this->getProperty('type');
			if ($formtype == "formit") $formid = 0;
			$layout->set('formid',$formid);
			$layout->set('formtype',$formtype);
			$layout->set('formname',$this->getProperty('name'));
			$layout->set('createdon',date('Y-m-d H:i:s',time()));
			$layout->set('createdby',$this->modx->user->get('id'));
			if ($layout->save() === false) {
				return $this->modx->error->failure($this->modx->lexicon('formdatamanager_layout_err_save'));
			}
			$layoutid = $this->modx->lastInsertId();
		}
		$this->setProperty('id',$layoutid);
		unset($data);

		return parent::initialize();
	}
	
    public function beforeSet()
    {
		// Only for Formz or Formit layouts - change of inactive field
		
		$layoutid = $this->getProperty('id');
		if (empty($layoutid)) {
            $this->addFieldError('id',$this->modx->lexicon('formdatamanager_layout_err_ns'));
        } else {
            if (!$this->doesAlreadyExist(array('id' => $layoutid))) {
                $this->addFieldError('id',$this->modx->lexicon('formdatamanager_layout_err_nf'));
            }
        }	
		
		$this->setProperty('inactive',$this->getProperty('inactive'));

        return parent::beforeSet();
    }
}
return 'FormDataManagerLayoutGridUpdateProcessor';