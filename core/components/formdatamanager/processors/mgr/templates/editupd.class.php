<?php
/**
 * EditUpd a layout record for a template
 *
 * @package formdatamanager
 * @subpackage processors
 */
class FormDataManagerTemplateEditUpdProcessor extends modProcessor
{
    public $classKey = 'FdmLayouts';
    public $languageTopics = array('FormDataManager:default');
	
	public function initialize() {
        return parent::initialize();
    }
    
    public function checkPermissions() { return true; }

    public function process() {
		
		$scriptProperties = $this->getProperties();
		$tplid = $scriptProperties['id'];
		
		$data = array();
	
		$classname = 'FdmLayouts';
		
		// get changed template
		$c = $this->modx->newQuery($classname);
		$c->select($this->modx->getSelectColumns($classname, $classname));		
		$c->where(array('id' => $tplid));
		$ntpl = $this->modx->getCollection($classname, $c);
		foreach($ntpl as $tfmt) {
			$fd = $tfmt->toArray();
			$nflddata = json_decode($fd['formfld_data']);
			$nselfld = $fd['selectionfield'];
			$ntpldata = array();
			foreach($nflddata as $ro) {
				$ntpldata = json_decode($ro,TRUE);
			}
		}		
		unset($c,$ntpl);
	
		// Process any layouts that use this template and update to reflect changes
		$compflds = array('include','mapfield','default','tplfield','ofn');
		
		$c = $this->modx->newQuery($classname);
		$c->select($this->modx->getSelectColumns($classname, $classname));
		$c->where(array('templateid' => $tplid));
		$tplfmts = $this->modx->getCollection($classname, $c);
		foreach($tplfmts as $tfmt) {
			$fd = $tfmt->toArray();
			$id = $fd['id'];
			// compare & change
			$flddata = json_decode($fd['formfld_data']);
	
			$selfld = $fd['selectionfield'];
			$compdata = array();			
			foreach($flddata as $ro) {
				$tpldata = json_decode($ro,TRUE);
				foreach($tpldata as $row) {
					$compdata[$row['label']] = $row;
				}
			}
			// get changed lines
			$nfdata = array();
			$changes = 0;
			foreach ($ntpldata as $row) {
				$nlbl = $row['label'];
				if (isset($compdata[$nlbl])) {
					$w = $compdata[$nlbl];
					foreach ($compflds as $cf) {
						// check if any changes / extra date
						if (!isset($row[$cf])) {
							if (isset($w[$cf])) {
								$row[$cf] = $w[$cf];
								$changes++;
							}
						}
						else {
							if ( (isset($w[$cf])) && ($row[$cf] != $w[$cf]) ) {
								$row[$cf] = $w[$cf];
								$changes++;
							}
						}
					}
					$compdata[$nlbl] = null;
				}
				else {
					$changes++;
				}
				if ( (!isset($row['tplfield'])) || (empty($row['tplfield'])) ) $row['tplfield'] = 1;
				$nfdata[] = $row;
			}
			// add any missing lines as dummy fields
			$nfc = count($nfdata);
			foreach ($compdata as $row) {
				if (!is_null($row)) {
					if ( (!isset($row['tplfield'])) || ($row['tplfield'] == 1) ) $row['tplfield'] = 0;
					$row['id'] = $nfc+1;
					$row['order'] = $nfc;
					$nfdata[] = $row;
					$changes++;
				}
			}
			// update ids of layouts updated
			if ($changes) {
				$data[] = $id;			
				// Update FdmLayouts record
				$layout = $this->modx->getObject('FdmLayouts',$id);
				$w = json_encode($nfdata);
				$ww = array();
				$ww["data"] = $w;
				$w = json_encode($ww);
				$layout->set('formfld_data',$w);
				$layout->set('selectionfield',$nselfld);
				$layout->save();
				unset($layout);
			}
			
		}
		unset($c,$tplfmts);
		
        return $this->outputArray($data,count($data));
    }
}
return 'FormDataManagerTemplateEditUpdProcessor';