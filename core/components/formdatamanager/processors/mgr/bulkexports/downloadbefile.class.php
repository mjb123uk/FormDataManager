<?php

/**
 * Class FormDataManagerDownloadBEFIleProcessor
 *
 * For FormDataManager Bulk Export Download 
 */
 
class FormDataManagerDownloadBEFIleProcessor extends modProcessor
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
		
		if (file_exists($p.$filename)) {
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: public");
			header("Content-Description: File Transfer");
			header('Content-Type: application/zip');
			header("Content-Disposition: attachment; filename=\"".$filename."\"");
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: ".filesize($p.$filename));
			ob_end_flush();
			@readfile($p.$filename);
			exit;
		}
    }
	
}
return 'FormDataManagerDownloadBEFIleProcessor';