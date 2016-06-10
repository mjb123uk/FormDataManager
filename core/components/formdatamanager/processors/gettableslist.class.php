<?php

/**
 * Class FormDataManagerGetTablesListProcessor
 *
 * For FormDataManager Layout Grid.
 */
 
class FormDataManagerGetTablesListProcessor extends modProcessor
{

    public function initialize() {
        return parent::initialize();
    }
    
    public function checkPermissions() { return true; }

    public function process()
    {
		$data = array();
		
		// get tables forms
		$classname = 'FdmLayouts';
		$c = $this->modx->newQuery($classname);
		$c->select($this->modx->getSelectColumns($classname, $classname));
		$c->where(array('formtype' => 'table'));
		$count = $this->modx->getCount($classname, $c);
		$tblfmts = $this->modx->getCollection($classname, $c);
		foreach($tblfmts as $tfmt) {
			$fd = $tfmt->toArray();
			$tbl = $fd['formname'];
			$hasl = 'No';
			if (!empty($fd['formfld_data'])) $hasl = 'Yes';
			$tc = 0;
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
			$data[] = array('id' => $fd['id'],'name' => $tbl,'editedon' => $fd['editedon'], 'has_layout' => $hasl, 'lastexport' => $fd['lastexportto'], 'submissions' => $tc, 'has_submission' => $hassub, 'total' => $tc);
		}
			
		return $this->outputArray($data,count($data));
    }
}
return 'FormDataManagerGetTablesListProcessor';