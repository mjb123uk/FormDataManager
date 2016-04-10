<?php

/**
 * Class FormDataManagerGetFormItListProcessor
 *
 * For FormDataManager Layout Grid.
 */
 
class FormDataManagerGetFormItListProcessor extends modProcessor
{

    public function initialize() {
        return parent::initialize();
    }
    
    public function checkPermissions() { return true; }

    public function process()
    {
		$data = array();
		
		// get latest formit forms
		$packageName = "formit";
		$packagepath = $this->modx->getOption('core_path') . 'components/' . $packageName . '/';
		$modelpath = $packagepath . 'model/';
		if (is_dir($modelpath)) {
			$this->modx->addPackage($packageName, $modelpath);
			$classname = 'FormItForm';
			$c = $this->modx->newQuery($classname);
			$c->select($this->modx->getSelectColumns($classname, $classname));	
			$c->sortby('`id`','DESC');
			$c->groupby('form');
			$frms = $this->modx->getCollection($classname, $c);
			foreach($frms as $frm) {
				$fd = $frm->toArray();
				$total = $this->modx->getCount($classname, array('form' => $fd['form']));
				$data[] = array('id' => $fd['id'],'name' => $fd['form'],'editedon' => $fd['editedon'], 'has_layout' => 'No', 'lastexport' => '', 'total' => $total);
			}

			$currentIndex = 0;
			$lists = array();
			foreach ($data as $item) {
				$lists[] = $item;
				$classname = 'FdmLayouts';
				$c = $this->modx->newQuery($classname);
				$c->select($this->modx->getSelectColumns($classname, $classname));
				$c->where(array('formname' => $item['name']));
				$count = $this->modx->getCount($classname, $c);
				if ($count == 1) {
					$lists[$currentIndex]['has_layout'] = 'Yes';
					$layout = $this->modx->getCollection($classname, $c);
					foreach($layout as $fdmd) {
						$fd = $fdmd->toArray();
						$lists[$currentIndex]['lastexport'] = $fd['lastexportto'];
					}
				}
				$total = $item['total'];
				$lists[$currentIndex]['submissions'] = $total;
				if ($total) $lists[$currentIndex]['has_submission'] = true;
				else $lists[$currentIndex]['has_submission'] = false;
				$currentIndex++;
			}
			$data = !empty($lists) ? $lists : $data;
			
		}
			
		return $this->outputArray($data,count($data));
    }
}
return 'FormDataManagerGetFormItListProcessor';