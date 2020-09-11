<?php

/**
 * Class FormDataManagerGetTemplatesListProcessor
 *
 * For FormDataManager Templates Grid.
 */
 
class FormDataManagerGetTemplatesListProcessor extends modProcessor
{

    public function initialize() {
        return parent::initialize();
    }
    
    public function checkPermissions() { return true; }

    public function process()
    {
		$scriptProperties = $this->getProperties();
		$forcombo = (isset($scriptProperties['forcombo'])) ? $scriptProperties['forcombo'] : false;
		$limit = (isset($scriptProperties['limit'])) ? $scriptProperties['limit'] : 20;
		if ($forcombo) $limit = 999;
		$start = (isset($scriptProperties['start'])) ? $scriptProperties['start'] : 0;
		$count = 0;	
		$data = array();
		$tudata = array();
		
		$classname = 'FdmLayouts';
		
		if (!$forcombo) {
			// get template usage
			$c = $this->modx->newQuery($classname);
			$c->select($this->modx->getSelectColumns($classname, $classname));
			$c->where(array('formtype:!=' => 'template','templateid:!=' => 0));
			$c->sortby('`templateid`','ASC');
			$tplfmts = $this->modx->getCollection($classname, $c);
			foreach($tplfmts as $tfmt) {
				$fd = $tfmt->toArray();
				$tid = $fd['templateid'];
				if (isset($tudata[$tid])) {
					$w = $tudata[$tid];
					$tudata[$tid] = $w+1;
				}
				else {
					$tudata[$tid] = 1;
				}
			}
			unset($c,$tplfmts);
		}
		// get templates
		$c = $this->modx->newQuery($classname);
		$c->select($this->modx->getSelectColumns($classname, $classname));		
		$c->where(array('formtype' => 'template'));
		$count = $this->modx->getCount($classname, $c);
		$tplfmts = $this->modx->getCollection($classname, $c);
		$ic = 0;
		if ($limit != 999) $limit += $start;
		foreach($tplfmts as $tfmt) {
			if ($ic >= $limit) break;
			if ($ic < $start) {
				$ic++;
				continue;
			}
			$fd = $tfmt->toArray();
			$tpl = $fd['formname'];
			$hasdata = 'No';
			if (!empty($fd['formfld_data'])) $hasdata = 'Yes';
			if ($forcombo) {
				if ($hasdata == 'Yes') $data[] = array('id' => $fd['id'],'name' => $tpl);
			}
			else {
				$w = (isset($tudata[$fd['id']])) ? $tudata[$fd['id']] : 0;
				$flddata = json_decode($fd['formfld_data']);
				$tpldata = array('fields','fldtypes','defaults');
				foreach($flddata as $ro) {
					$rows = json_decode($ro,TRUE);
					foreach($rows as $r) {
						$tpldata['fields'][] = $r['label'];
						$tpldata['fldtypes'][] = $r['type'];
						$tpldata['defaults'][] = $r['default'];
					}
				}
				$tpleditdata = array();				
				$tpleditdata['fields'] = implode(",",$tpldata['fields']);
				$tpleditdata['fldtypes'] = implode(",",$tpldata['fldtypes']);
				$tpleditdata['defaults'] = implode(",",$tpldata['defaults']);
				$tpleditdata['mapdata'] = $fd['formfld_extra'];
				$tpleditdata['selectfld'] = $fd['selectionfield'];				
				$www = json_encode($tpleditdata);
				$data[] = array('id' => $fd['id'],'name' => $tpl,'selectionfield' => $fd['selectionfield'], 'hasdata' => $hasdata, 'usedcount' => $w, 'tpleditdata' => $www);
			}
			$ic++;
		}
		if ($forcombo) $count = count($data);	
		return $this->outputArray($data,$count);
    }
}
return 'FormDataManagerGetTemplatesListProcessor';