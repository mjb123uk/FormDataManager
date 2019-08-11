<?php
/*
 * fdmExportTask 
 *
 * FormDataManager
 */

// Check this functionality allowed - system setting

$allowed = $this->modx->getOption('fdm_allow_autoexports',null,0);
if (!$allowed) {
    echo "<html><title>FormDataManger Error</title><body><h1>Permission Error</h1><p>The FormDataManger auto export function is unavailable.</p></body></html>";
    exit();
}

// Load Class

if (!isset($scriptProperties)) $scriptProperties = array();
$fdm = $modx->getService('formdatamanager', 'FormDataManager', $modx->getOption('formdatamanager.core_path', null, $modx->getOption('core_path') . 'components/formdatamanager/') . 'model/formdatamanager/', $scriptProperties);
if (!($fdm instanceof FormDataManager)) return '';

$opt = (isset($fdmRunOpts)) ? trim($fdmRunOpts) : "";
switch ($opt) {
    case "-zip":
        $fdm->createZip();
        break;
    case "-rar":
        $fdm->createRar();
        break;
    default:
        
}

if (!empty($opt)) return;

$processorsPath = $modx->getOption('core_path', null, MODX_CORE_PATH) .'components/formdatamanager/processors/';

$templates = array();

// get all templates to process
$classname = 'FdmLayouts';
$c = $modx->newQuery($classname);
$c->select($modx->getSelectColumns($classname, $classname));
$c->where(array('formtype' => 'template'));
$count = $modx->getCount($classname, $c);
$fdmdata = $modx->getCollection($classname, $c);

// loop through these templates
if (count($fdmdata)) {
	// Format for export
	foreach($fdmdata as $fdmd) {
		$fd = $fdmd->toArray();
		$templates[$fd['id']] = $fd['formfld_extra'];
	}
}
unset($c, $fdmdata);

// get all forms to process
$classname = 'FdmLayouts';
$c = $modx->newQuery($classname);
$c->select($modx->getSelectColumns($classname, $classname));
$c->where(array('formtype' => 'formit'));
$c->where(array('formtype' => 'formz'),xPDOQuery::SQL_OR);
$count = $modx->getCount($classname, $c);
$fdmdata = $modx->getCollection($classname, $c);

// set folder name to use
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
		$fields['autoexport'] = true;
		$lastautoexpto = $fd['lastautoexpto'];
		if (!empty($lastautoexpto)) $lastautoexpto = date('Y-m-d H:i:s',strtotime($lastautoexpto) + 1);
		$fields['startDate'] = $lastautoexpto;
		$fields['endDate'] = '';
		$fields['savetofile'] = $fd['formname'];
	    $fields['savetofolder'] = $savetofolder;
		$response = $modx->runProcessor('mgr/exportdata', $fields, array('processors_path' => $processorsPath));
	}
}