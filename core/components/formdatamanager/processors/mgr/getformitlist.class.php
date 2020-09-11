<?php

/**
 * Class FormDataManagerGetFormItListProcessor
 *
 * For FormDataManager FormIt Grid.
 */
 
class FormDataManagerGetFormItListProcessor extends modProcessor
{

    public function initialize() {
        return parent::initialize();
    }
    
    public function checkPermissions() { return true; }

    public function process()
    {
		$scriptProperties = $this->getProperties();
		$limit = (isset($scriptProperties['limit'])) ? $scriptProperties['limit'] : 20;
		$start = (isset($scriptProperties['start'])) ? $scriptProperties['start'] : 0;
		$activeFilter = (isset($scriptProperties['activeFilter'])) ? $scriptProperties['activeFilter'] : "";
		if ($activeFilter == "All") $activeFilter = "";
		$count = 0;
		$data = array();
		$filters = array();
		
		// Get any layouts to filter
		if (!empty($activeFilter)) {
			$classname = 'FdmLayouts';
			$c = $this->modx->newQuery($classname);
			$c->select($this->modx->getSelectColumns($classname, $classname));
			$c->where(array('formtype' => 'formit'));
			$c->where(array('inactive' => 1));
			$filtercount = $this->modx->getCount($classname, $c);
			if ($filtercount) {
				$elrs = $this->modx->getCollection($classname, $c);
				foreach($elrs as $elr) {
					$er = $elr->toArray();
					$filters[] = $er['formname'];
				}
			}
		}
		
		// get formit forms
		$packageName = "formit";
		$packagepath = $this->modx->getOption('core_path') . 'components/' . $packageName . '/';
		$modelpath = $packagepath . 'model/';
		if (is_dir($modelpath)) {
			$this->modx->addPackage($packageName, $modelpath);
			$classname = 'FormItForm';
			$c = $this->modx->newQuery($classname);
			$c->select($this->modx->getSelectColumns($classname, $classname));
			if ($activeFilter == "Inactive") $c->where(array('form:IN' => $filters));
			if ( ($activeFilter == "Active") && (count($filters)) ) $c->where(array('form:NOT IN' => $filters));
			$c->groupby('form');
			$count = $this->modx->getCount($classname, $c);
			$c->limit($limit, $start); 			
			$c->sortby('`form`','ASC');
			$frms = $this->modx->getCollection($classname, $c);
			$i = 1;
			foreach($frms as $frm) {
				$fd = $frm->toArray();
				$total = $this->modx->getCount($classname, array('form' => $fd['form']));
				$data[] = array('id' => $fd['form'], 'type' => 'formit', 'name' => $fd['form'], 'context_key' => $fd['context_key'], 'inactive' => 0, 'editedon' => $fd['editedon'], 'has_layout' => 'No', 'layoutid' => 0, 'has_tpl' => 'No', 'lastexport' => '', 'selectionfield' => '', 'templateid' => 0, 'total' => $total);
				$i++;
			}
			
			$currentIndex = 0;
			$lists = array();
			$hasactivelayouts = 0;
			foreach ($data as $item) {
				$lists[] = $item;
				$classname = 'FdmLayouts';
				$c = $this->modx->newQuery($classname);
				$c->select($this->modx->getSelectColumns($classname, $classname));
				$c->where(array('formtype' => 'formit','formname' => $item['name']));
				$lcount = $this->modx->getCount($classname, $c);
				if ($lcount == 1) {
					$hasactivelayouts = 1;
					$layout = $this->modx->getCollection($classname, $c);
					foreach($layout as $fdmd) {
						$fd = $fdmd->toArray();
						$lists[$currentIndex]['layoutid'] = $fd['id'];
						if (!empty($fd['formfld_data'])) $lists[$currentIndex]['has_layout'] = 'Yes';
						$lists[$currentIndex]['inactive'] = $fd['inactive'];
						$lists[$currentIndex]['lastexport'] = $fd['lastexportto'];
						$lists[$currentIndex]['selectionfield'] = trim($fd['selectionfield']);
						if (!empty($fd['templateid'])) $lists[$currentIndex]['has_tpl'] = 'Yes';
						$lists[$currentIndex]['templateid'] = $fd['templateid'];
					}
				}
				$total = $item['total'];
				$lists[$currentIndex]['submissions'] = $total;
				if ($total) $lists[$currentIndex]['has_submission'] = true;
				else $lists[$currentIndex]['has_submission'] = false;
				$currentIndex++;
			}
			if ($hasactivelayouts) $data = $lists;
			else $data = !empty($lists) ? $lists : $data;
			
		}
		
		return $this->outputArray($data,$count);
    }
}
return 'FormDataManagerGetFormItListProcessor';