<?php

/**
 * Class FormDataManagerBulkExportProcessor
 *
 * For FormDataManager Bulk Export 
 */
 
class FormDataManagerBulkExportProcessor extends modProcessor
{

    public function initialize() {
        return parent::initialize();
    }
    
    public function checkPermissions() { return true; }

    public function process()
    {
		
		$scriptProperties = $this->getProperties();
		$layouts = $scriptProperties['layouts'];
		$ftype = $scriptProperties['ftype'];
		// turn into list for query
		$ids = explode(',',$layouts);
		if ($ftype == "formz") $fid = "formid";
		else if ($ftype == "formit") $fid = "formname";
		else $fid = "id";
		
		$processorsPath = $this->modx->getOption('core_path', null, MODX_CORE_PATH) .'components/formdatamanager/processors/';
		
		// get all forms to process
		$classname = 'FdmLayouts';
		$c = $this->modx->newQuery($classname);
		$c->select($this->modx->getSelectColumns($classname, $classname));
		$c->where(array($fid.':IN' => $ids));
		$count = $this->modx->getCount($classname, $c);
		$fdmdata = $this->modx->getCollection($classname, $c);

		$data = array();
		$savetofolder = date('Ymd_His',time());
		
		// loop through these forms	
		if (count($fdmdata)) {
			// Format for export
			foreach($fdmdata as $fdmd) {
				$fd = $fdmd->toArray();
				if ($fd['inactive']) continue;
				$fields = array();
				$fields['formid'] = $fd['formid'];
				$fields['formname'] = $fd['formname']; 
				$fields['layoutid'] = $fd['id'];
				$fields['template'] = $fd['templateid'];
				$fields['bulkexport'] = true;				
				$lastexportto = $fd['lastexportto'];
				if (!empty($lastexportto)) $lastexportto = date('Y-m-d H:i:s',strtotime($lastexportto) + 1);
				$fields['startDate'] = $lastexportto;
				$fields['endDate'] = '';
				$fields['savetofile'] = $fd['formname'];
				$fields['savetofolder'] = $savetofolder;				
				$data[] = $fd['formid'];
				$response = $this->modx->runProcessor('mgr/exportdata', $fields, array('processors_path' => $processorsPath));
			}
		}
		$remove = 0;
		$this->modx->formdatamanager->createZip($savetofolder,$remove);	
		$data[] = $savetofolder;

		return $this->outputArray($data,count($data));
	}		
}
return 'FormDataManagerBulkExportProcessor';