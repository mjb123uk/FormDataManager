<?php

/**
 * Class FormDataManagerGetFormListProcessor
 *
 * For FormDataManager Layout Grid.
 */
 
class FormDataManagerGetFormListProcessor extends modProcessor
{

    public function initialize() {
        return parent::initialize();
    }
    
    public function checkPermissions() { return true; }

    public function process()
    {
		$data = array();
		
		// get latest formz forms
		$packageName = "formz";
		$packagepath = $this->modx->getOption('core_path') . 'components/' . $packageName . '/';
		$modelpath = $packagepath . 'model/';
		if (is_dir($modelpath)) {
			$this->modx->addPackage($packageName, $modelpath);
			$classname = 'fmzForms';
			$c = $this->modx->newQuery($classname);
			$c->select($this->modx->getSelectColumns($classname, $classname));
			$count = $this->modx->getCount($classname, $c);
			$c->sortby('`id`','ASC');
			$frms = $this->modx->getCollection($classname, $c);

			foreach($frms as $frm) {
				$fd = $frm->toArray();
				$data[] = array('id' => $fd['id'],'name' => $fd['name'],'editedon' => $fd['editedon'], 'has_layout' => 'No', 'lastexport' => '');
			}

			$currentIndex = 0;
			$lists = array();
			foreach ($data as $item) {
				$lists[] = $item;
				$classname = 'FdmLayouts';
				$c = $this->modx->newQuery($classname);
				$c->select($this->modx->getSelectColumns($classname, $classname));
				$c->where(array('formid' => $item['id']));
				$count = $this->modx->getCount($classname, $c);
				if ($count == 1) {
					$lists[$currentIndex]['has_layout'] = 'Yes';
					$layout = $this->modx->getCollection($classname, $c);
					foreach($layout as $fdmd) {
						$fd = $fdmd->toArray();
						$lists[$currentIndex]['lastexport'] = $fd['lastexportto'];
					}
				}
				$c = $this->modx->newQuery('fmzFormsData');
				$c->where(array('form_id' => $item['id']));
				$total = $this->modx->getCount('fmzFormsData', $c);
				$lists[$currentIndex]['submissions'] = $total;
				if ($total) $lists[$currentIndex]['has_submission'] = true;
				$currentIndex++;
			}
			$data = !empty($lists) ? $lists : $data;
			
		}
			
		return $this->outputArray($data,count($data));
    }
}
return 'FormDataManagerGetFormListProcessor';