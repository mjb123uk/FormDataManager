<?php

/**
 * Class FormDataManagerGetFldDataProcessor
 *
 * For FormDataManager Layout Grid.
 */
 
class FormDataManagerGetFldDataProcessor extends modProcessor
{

    public function initialize() {
        return parent::initialize();
    }
    
    public function checkPermissions() { return true; }

    public function process()
    {
		$scriptProperties = $this->getProperties();
		$formid = $scriptProperties['formid'];

		$data = array();
		$layout = array();
		
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
						$data[] = $r;
					}
				}
			}
		}
		else {
			// get latest formz fields and use for new layout
			$packageName = "formz";
			$packagepath = $this->modx->getOption('core_path') . 'components/' . $packageName . '/';
			$modelpath = $packagepath . 'model/';
			if (is_dir($modelpath)) {
				$this->modx->addPackage($packageName, $modelpath);
				$classname = 'fmzFormsFields';
				$c = $this->modx->newQuery($classname);
				$c->select($this->modx->getSelectColumns($classname, $classname));
				$c->where(array('form_id' => $formid));
				$count = $this->modx->getCount($classname, $c);
				$c->sortby('`order`','ASC');
				$frmflds = $this->modx->getCollection($classname, $c);
				$ord = 0;
				foreach($frmflds as $frmfld) {
					$fd = $frmfld->toArray();
					$settings = $this->modx->fromJSON($fd['settings'], false);
					$data[] = array('id' => $fd['id'],'order' => $ord,'label' => $settings->label,'type' => $fd['type'],'include' => 1,'coltitle' => $settings->label,'default' => '');
					$ord++;
				}
			}
		}
			
		return $this->outputArray($data,count($data));
    }
}
return 'FormDataManagerGetFldDataProcessor';