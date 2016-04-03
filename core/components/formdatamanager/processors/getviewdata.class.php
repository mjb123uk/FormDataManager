<?php

/**
 * Class FormDataManagerGetViewDataProcessor
 *
 * For FormDataManager ViewData Grid.
 */
 
class FormDataManagerGetViewDataProcessor extends modProcessor
{

    public function initialize() {
        return parent::initialize();
    }
    
    public function checkPermissions() { return true; }

    public function process()
    {
		$scriptProperties = $this->getProperties();
		$formid = $scriptProperties['formid'];
		$layoutid = $scriptProperties['layoutid'];
		
		$vrows = array();
		$layout = array();
		$loflds = array();		
		
		$classname = 'FdmLayouts';
		$c = $this->modx->newQuery($classname);
		$c->select($this->modx->getSelectColumns($classname, $classname));
		$c->where(array('formid' => $formid));
		$count = $this->modx->getCount($classname, $c);
		$fdmdata = $this->modx->getCollection($classname, $c);
		if (!empty($fdmdata)) $layout = $fdmdata;
				
		if (count($layout)) {
			// Format for grid
			foreach($layout as $fdmd) {
				$fd = $fdmd->toArray();
				$ldata = json_decode($fd['formfld_data']);
				foreach($ldata as $ro) {
					$rows = json_decode($ro,TRUE);
					foreach($rows as $r) {
						$loflds[] = $r;
					}
				}
			}
			
			// now get formz data and format to match layout
			$packageName = "formz";
			$packagepath = $this->modx->getOption('core_path') . 'components/' . $packageName . '/';
			$modelpath = $packagepath . 'model/';
			if (is_dir($modelpath)) {
				$this->modx->addPackage($packageName, $modelpath);
				$classname = 'fmzFormsData';
				$c = $this->modx->newQuery($classname);
				$c->select($this->modx->getSelectColumns($classname, $classname));
				$c->where(array('form_id' => $formid));
				$count = $this->modx->getCount($classname, $c);
				$c->sortby('`senton`','ASC');
				$frms = $this->modx->getCollection($classname, $c);
				foreach ($frms as $itemobj) {
					$item = $itemobj->toArray();
					$form = $this->modx->getObject('fmzForms', $item['form_id']);
					$formData = unserialize($item['data']);
					$fieldsData = $this->modx->getCollection('fmzFormsDataFields', array('data_id' => $item['id']));

					$data = array();
					$data['senton'] = !empty($item['senton']) ? date('d/m/Y H:i:s', strtotime($item['senton'])) : '';
					$data['ip_address'] = !empty($formData['ip_address']) ? $formData['ip_address'] : '';
					foreach($loflds as $lofld) {
						if ($lofld['include']) {		// only include if column wanted
							$fl = $lofld['label'];
							$v = "";
							foreach ($fieldsData as $fd) {
								$values = unserialize($fd->value);
								if (is_array($values)) $values = implode('/', $values);
								$label = $fd->label;
								if ($label == $fl) {
									$v = $values;
									break;
								}
							}
							if ( (empty($v)) && (!empty($lofld['default'])) ) $v = $lofld['default'];
							$str = preg_replace('/[^A-Za-z0-9_-]/', '', $fl);
							$data[$str] = $v;
						}
					}	
					$vrows[] = $data;
				}
			}		
		}
		
		return $this->outputArray($vrows,count($vrows));
    }
}
return 'FormDataManagerGetViewDataProcessor';