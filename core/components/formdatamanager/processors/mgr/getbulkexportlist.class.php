<?php

/**
 * Class FormDataManagerGetBulkexportListProcessor
 *
 * For FormDataManager Bulkexport Grid.
 */
 
class FormDataManagerGetBulkexportListProcessor extends modProcessor
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
		$data = array();
		
		// get export files

		$files = array();
		
		$p = $this->modx->formdatamanager->getBEPath();
		
		$dir = opendir($p);
		$scale = array(" bytes"," KB"," MB"," GB");
		$count = 0;
		$limit += $start;
		while ($file = readdir($dir))
		{
			if ($file == '.' || $file == '..') continue;
			if (is_dir($p.$file)) continue;
			if (substr($file,-4) != ".zip") continue;
			if ( ($count < $start) || ($count >= $limit) ) {
				$count++;
				continue;
			}
			// Get file data
			$stat = stat($p.$file);
			$size = $stat[7];
			for($s=0;$size>1024&&$s<4;$s++) $size=$size/1024;	//Calculate in Bytes,KB,MB etc.
			if($s>0) $size= number_format($size,2).$scale[$s];
			else $size= number_format($size).$scale[$s];

			$fdate =  date("M d, Y H:i:s", $stat[9]);	
			//$perms = decoct(fileperms($file)%01000);
			$filename = htmlentities($file,ENT_QUOTES);
			$filename = str_replace('.zip','',$filename);
			$ftype = 'zip';
			
			$data[] = array('id' => $file, 'filename' => $filename, 'filetype' => $ftype, 'filesize' => $size, 'createdon' => $fdate );
			$count++;
		}

		return $this->outputArray($data,$count);
    }
}
return 'FormDataManagerGetBulkexportListProcessor';