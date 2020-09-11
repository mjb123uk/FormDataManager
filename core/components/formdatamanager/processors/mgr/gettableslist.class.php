<?php

/**
 * Class FormDataManagerGetTablesListProcessor
 *
 * For FormDataManager Tables Grid.
 */
 
class FormDataManagerGetTablesListProcessor extends modProcessor
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
		$count = 0;	
		$data = array();
		
		// get tables forms
		$classname = 'FdmLayouts';
		$c = $this->modx->newQuery($classname);
		$c->select($this->modx->getSelectColumns($classname, $classname));
		$c->where(array('formtype' => 'table'));
		$count = $this->modx->getCount($classname, $c);
		$tblfmts = $this->modx->getCollection($classname, $c);
		$ic = 0;
		$limit += $start;
		foreach($tblfmts as $tfmt) {
			if ($ic >= $limit) break;
			if ($ic < $start) {
				$ic++;
				continue;
			}
			$fd = $tfmt->toArray();
			$tbl = $fd['formname'];
			$hasl = 'No';
			if (!empty($fd['formfld_data'])) $hasl = 'Yes';
			$hast = 'No';
			if (!empty($fd['templateid'])) $hast = 'Yes';
			$tc = 0;
			$tbldata = array();			
			// get table record count        
			$q = "SHOW TABLE STATUS LIKE '".$tbl."'";
			$result = $this->modx->query($q);
			if (is_object($result)) {
				$tbldata = $result->fetchAll(PDO::FETCH_ASSOC);
			}
			foreach ($tbldata as &$tdata) {
				$tc = $tdata['Rows'];
			}
			$hassub = false;
			if ($tc > 0) $hassub = true;
			$data[] = array('id' => $fd['id'],'name' => $tbl,'editedon' => $fd['editedon'], 'has_layout' => $hasl, 'has_tpl' => $hast, 'lastexport' => $fd['lastexportto'], 'selectionfield' => trim($fd['selectionfield']), 'templateid' => $fd['templateid'], 'submissions' => $tc, 'has_submission' => $hassub, 'total' => $tc);
			$ic++;
		}
		
		return $this->outputArray($data,$count);
    }
}
return 'FormDataManagerGetTablesListProcessor';