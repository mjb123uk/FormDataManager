<?php
/**
 * Remove a layout
 *
 * @package formdatamanager
 * @subpackage processors
 */
 
class FormDataManagerBulkExportRemoveProcessor extends modProcessor
{
    public function initialize() {
        return parent::initialize();
    }
    
    public function checkPermissions() { return true; }

    public function process()
    {
		$scriptProperties = $this->getProperties();
		$filename = $scriptProperties['filename'];
		$p = $this->modx->formdatamanager->getBEPath();
		$msg = "";
		
		@unlink($p.$filename);
		$msg = "Removed ".$filename;
		
		$filename = str_replace(".zip","",$filename);
		// remove associated directory
		$fp = $p.$filename;
		if (is_dir($fp)) {			
			array_map('unlink', glob("$fp/*.*"));
			rmdir($fp);
			$msg .= " and associated directory";
		}
		
		$data = array($msg);
		
		return $this->outputArray($data,count($data));
	}
}
return 'FormDataManagerBulkExportRemoveProcessor';