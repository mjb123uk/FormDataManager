<?php

/**
 * Class FormDataManagerExportDataProcessor
 *
 * For FormDataManager Export 
 */
 
class FormDataManagerExportDataProcessor extends modProcessor
{

    public function initialize() {
        return parent::initialize();
    }
    
    public function checkPermissions() { return true; }

    public function process()
    {
		$scriptProperties = $this->getProperties();
		$formid = $scriptProperties['formid'];
		$formname = $scriptProperties['formname'];
		$layoutid = $scriptProperties['layoutid'];
		$selectionfield = $scriptProperties['selectionfield'];
		$template = $scriptProperties['template'];			
		$startDate = $scriptProperties['startDate'];
    	$endDate = $scriptProperties['endDate'];
		$savetofile = isset($scriptProperties['savetofile']) ? $scriptProperties['savetofile'] : "";
		$savetofolder = isset($scriptProperties['savetofolder']) ? $scriptProperties['savetofolder'] : "";
		$bulkexport = isset($scriptProperties['bulkexport']) ? $scriptProperties['bulkexport'] : false;
		$autoexport = isset($scriptProperties['autoexport']) ? $scriptProperties['autoexport'] : false;
		$istable = false;
		if (trim($formid) == 'table') $istable = true;
		$dateFormat = $this->modx->getOption('manager_date_format') . ' ' . $this->modx->getOption('manager_time_format');
		$afns = array();
		
		if (class_exists('FormDataManagerViewExportFunctions',false)) $xfs = new FormDataManagerViewExportFunctions();
		else $xfs = $this->modx->runSnippet("fdmViewExportFunctions",array());
		$afns = $xfs->fdmfunctionlist();
		
		if (!empty($savetofile)) {
			$exportPath = $this->modx->formdatamanager->getBEPath();
			if (!is_dir($exportPath)) mkdir($exportPath);
			if (!empty($savetofolder)) {
				$exportPath .= $savetofolder.'/';
				if (!is_dir($exportPath)) mkdir($exportPath);
			}
		}
		
		$layout = array();
		$loflds = array();
		
		$classname = 'FdmLayouts';

		$c = $this->modx->newQuery($classname);
		$c->select($this->modx->getSelectColumns($classname, $classname));
		$c->where(array('id' => $layoutid));
		$count = $this->modx->getCount($classname, $c);
		$fdmdata = $this->modx->getCollection($classname, $c);
		if (!empty($fdmdata)) $layout = $fdmdata;
		
		$lists = array();
		$rcount = 0;
		if ( ($istable) || ($template) ) $header = array();
		else $header = array('Sent On','IP Address');
		if (count($layout)) {
			
			// Format columns for export
			foreach($layout as $fdmd) {
				$fd = $fdmd->toArray();
				$ffdata = $fd['formfld_data'];
				if (empty($ffdata)) break;
				$ldata = json_decode($ffdata);
				foreach($ldata as $ro) {
					$rows = json_decode($ro,TRUE);
					foreach($rows as $r) {
						$loflds[] = $r;
					}
				}
				foreach($loflds as $lofld) {
					if ($lofld['include']) {		// only include if column wanted
						$fct = trim($lofld['coltitle']);
						if ($template) {
							$fct = trim($lofld['label']);
						}
						$header[] = $fct;
					}
				}
			}
			
			if (count($loflds)) {
					
				switch($formid) {
					case "formit":
						// get a sample of the formit saved data to use for new layout
						$packageName = "formit";
						$packagepath = $this->modx->getOption('core_path') . 'components/' . $packageName . '/';
						$modelpath = $packagepath . 'model/';
						if (is_dir($modelpath)) {
							$this->modx->addPackage($packageName, $modelpath);
							$classname = 'FormItForm';
							$c = $this->modx->newQuery($classname);
							$c->select($this->modx->getSelectColumns($classname, $classname));
							$c->where(array('form' => $formname));
							$expfld = "date";
							if ( ($template) && (!empty($selectionfield)) ) $expfld = $selectionfield;
							if (!empty($startDate)) {
								$c->andCondition(array(
									$expfld.':>' => strtotime($startDate)
								));
							}
							if (!empty($endDate)) {
								$c->andCondition(array(
									$expfld.':<' => strtotime($endDate)
								));
							}				
							$count = $this->modx->getCount($classname, $c);
							$c->sortby('`'.$expfld.'`','ASC');
							$frmrecs = $this->modx->getCollection($classname, $c);
							$frmflds = array();
							foreach($frmrecs as $frmr) {
								$item = $frmr->toArray();
								$values = $this->modx->fromJSON($item['values'], false);
								$data = array();
								if (!$template) {
									$data[] = !empty($item['date']) ? date($dateFormat, $item['date']) : '';
									$data[] = !empty($item['ip']) ? $item['ip'] : '';
								}
								foreach($loflds as $lofld) {
									if ($lofld['include']) {		// only include if column wanted
										$fl = $lofld['label'];
										if ( ($template) && (!empty($lofld['mapfield'])) ) $fl = $lofld['mapfield'];
										$v = null;
										if ( ($template) && ($fl == "date") && (!empty($item['date'])) ) $v = date($dateFormat, $item['date']);
										if ( ($template) && ($fl == "ip") && (!empty($item['ip'])) ) $v = $item['ip'];
										if (is_null($v)) $v = (isset($values->$fl)) ? $values->$fl : "";								
										if (is_array($v)) $v = implode('/', $v);
										$ofn = (isset($lofld['ofn'])) ? trim($lofld['ofn']) : "";
										if (!empty($ofn)) $v = $xfs->fdmdofunction($ofn,$v);								
										if ( (empty($v)) && (!empty($lofld['default'])) ) $v = $lofld['default'];
										$data[] = $v;
									}
								}
								$lists[] = $data;
								$rcount++;
							}
						}
						break;
					case "table":
						$q = "SELECT * FROM ".$formname;
						$wh = "";
						if (!empty($selectionfield)) {
							// get a record to test format of date field
							$result = $this->modx->query($q." LIMIT 1");
							if (is_object($result)) {
								$row = $result->fetch(PDO::FETCH_ASSOC);
								$val = $row[$selectionfield];
								$usedatestring = false;
								// test if string or internal date/time stamp
								if (!$this->isValidTimeStamp($val)) $usedatestring = true;
								if (!empty($startDate)) {
									if ($usedatestring) $w = "'".date('Y-m-d H:i:s', strtotime($startDate))."'";
									else $w = strtotime($startDate);
									if (empty($wh)) $wh = " WHERE ";
									$wh .= '`'.$selectionfield.'` > '.$w;
								}
								if (!empty($endDate)) {
									if ($usedatestring) $w = "'".date('Y-m-d H:i:s', strtotime($endDate))."'";
									else $w = strtotime($endDate);
									if (empty($wh)) $wh = " WHERE ";
									else $wh .= " AND ";
									$wh .= '`'.$selectionfield.'` < '.$w;
								}			
							}
							unset($result);
							$q .= $wh;
							$q .= ' ORDER BY `'.$selectionfield.'`';
						}
						$result = $this->modx->query($q);
						$tdata = array();
						if (is_object($result)) {
							$tdata = $result->fetchAll(PDO::FETCH_ASSOC);
						}
						foreach ($tdata as &$values) {
							$data = array();
							foreach($loflds as $lofld) {
								if ($lofld['include']) {		// only include if column wanted
									$fl = $lofld['label'];
									if ( ($template) && (!empty($lofld['mapfield'])) ) $fl = $lofld['mapfield'];
									$v = (isset($values[$fl])) ? $values[$fl] : "";
									if ( (is_string($v)) && (substr($v,0,1) == "{") ) $v = str_replace('"','""',$v);
									if (is_array($v)) $v = implode('/', $v);
									$ofn = (isset($lofld['ofn'])) ? trim($lofld['ofn']) : "";
									if (!empty($ofn)) $v = $xfs->fdmdofunction($ofn,$v);
									if ( (empty($v)) && (!empty($lofld['default'])) ) $v = $lofld['default'];
									$v = $this->formatfld($v,$lofld['type'],$dateFormat);
									
									$data[] = $v;
								}
							}
							$lists[] = $data;
							$rcount++;
						}
						break;
					default:
						// now get formz data and format to match layout
						$packageName = "formz";
						$packagepath = $this->modx->getOption('core_path') . 'components/' . $packageName . '/';
						$modelpath = $packagepath . 'model/';
						if (is_dir($modelpath)) {
							$this->modx->addPackage($packageName, $modelpath);
							$classname = 'fmzFormsData';
							$c = $this->modx->newQuery($classname);
							$c->select($this->modx->getSelectColumns($classname, $classname));
							$c->where(array('form_id' => $formid));
							$expfld = "senton";
							if ( ($template) && (!empty($selectionfield)) ) $expfld = $selectionfield;
							if (!empty($startDate)) {
								$c->andCondition(array(
									$expfld.':>' => date('Y-m-d H:i:s', strtotime($startDate))
								));
							}
							if (!empty($endDate)) {
								$c->andCondition(array(
									$expfld.':<' => date('Y-m-d H:i:s', strtotime($endDate))
								));
							}
							$count = $this->modx->getCount($classname, $c);
							if ($count > 0) {
								$c->sortby('`'.$expfld.'`','ASC');
								$frms = $this->modx->getCollection($classname, $c);
								foreach ($frms as $itemobj) {
									$item = $itemobj->toArray();
									$form = $this->modx->getObject('fmzForms', $item['form_id']);
									$formData = unserialize($item['data']);
									$fieldsData = $this->modx->getCollection('fmzFormsDataFields', array('data_id' => $item['id']));

									$data = array();
									if (!$template) {
										$data[] = !empty($item['senton']) ? date($dateFormat, strtotime($item['senton'])) : '';
										$data[] = !empty($formData['ip_address']) ? $formData['ip_address'] : '';
									}
									foreach($loflds as $lofld) {
										if ($lofld['include']) {		// only include if column wanted
											$fl = $lofld['label'];
											if ( ($template) && (!empty($lofld['mapfield'])) ) $fl = $lofld['mapfield'];										
											$v = "";
											if ( ($template) && ($fl == "senton") && (!empty($item['senton'])) ) {
												$v = date($dateFormat, strtotime($item['senton']));
											}
											else if ( ($template) && ($fl == "ip_address") && (!empty($formData['ip_address'])) ) {
												$v = $formData['ip_address'];				
											}	
											else {
												foreach ($fieldsData as $fd) {
													$label = $fd->label;
													if ($label == $fl) {			
														$values = unserialize($fd->value);
														if (is_array($values)) $values = implode('/', $values);										
														$v = $values;
														$ofn = (isset($lofld['ofn'])) ? trim($lofld['ofn']) : "";
														if (!empty($ofn)) $v = $xfs->fdmdofunction($ofn,$v);									
														break;
													}
												}
											}
											if ( (empty($v)) && (!empty($lofld['default'])) ) $v = $lofld['default'];
											$data[] = $v;
										}
									}
									$lists[] = $data;
									$rcount++;
								}
							}
						}
				}
			}		
		}
		
		// if no export to save then exit
		if ( (!empty($savetofile)) && ($rcount == 0) ) return;
		
	    $modRes = $this->modx->newObject('modResource');
		if (!isset($form)) $alias = str_replace(' ','-',$formname);
        else $alias = $modRes->cleanAlias($form->get('name'));
        $now = date('Y_m_d_H_i_s', time());
        $filename = $alias . '_' . $now . '.csv';
		
		$lastfld = "export";
        if (empty($savetofile)) $csv = $this->toCSV($lists, $header, ',', '"', "\r\n");
		if ($autoexport) $lastfld = "autoexp";
			
		// Update Layout last export dates
		$layout = $this->modx->getObject('FdmLayouts',$layoutid);
		$df = null;
		if (! empty($startDate)) $df = $startDate;
        if (! empty($endDate)) {
			$dt = $endDate;
        }
		else {
			$dt = date('Y-m-d H:i:s',time());
		}
		$layout->set('last'.$lastfld.'from',$df);
		$layout->set('last'.$lastfld.'to',$dt);
		$layout->save();
		unset($layout);
		
        if (empty($savetofile)) {
			$this->download($csv, $filename);
		}
		else {
			$this->exportfile($header, $lists, $exportPath.$filename);
		}
    }

    private function toCSV(array $content, array $header, $delimiter = ',', $enclosure, $lineEnding = null)
    {
        if ($lineEnding === null) {
            $lineEnding = PHP_EOL;
        }
		
        $csv = $enclosure . implode($enclosure . $delimiter . $enclosure, $header) . $enclosure . $lineEnding;
        foreach ($content as $li) {
            $csv .= $enclosure . implode($enclosure . $delimiter . $enclosure, $li) . $enclosure . $lineEnding;
        }

        return $csv;
    }

    private function download($data, $filename)
    {
        $headers = array();
        $headers[] = 'Pragma: public';
        $headers[] = 'Content-type: application/csv; charset=utf-8';
        $headers[] = 'Content-Disposition: attachment; filename="' . $filename . '";';
        $headers[] = 'Content-Transfer-Encoding: binary';
        $headers[] = 'Content-Length: ' . strlen($data);
        $headers[] = 'Pragma: no-cache';

        $this->setHeaders($headers);

        echo $data;
        exit;
    }

    private function exportfile(array $hdrflds, array $data, $filename)
    {
		$fp = fopen($filename, 'wt');
		fputcsv($fp, $hdrflds);
		foreach($data as $fields) {
			fputcsv($fp, $fields);
		}
		fclose($fp);		
	}
		
    private function setHeaders(array $headers)
    {
        if (headers_sent()) return false;

        foreach ($headers as $header) {
            header((string) $header);
        }
    }	
	
	private function formatfld($val,$type,$dateFormat) {
		if ($type == "date") {
			// test if string or internal date/time stamp
			if ($this->isValidTimeStamp($val)) {
				// convert to date string
				$val = date($dateFormat, $val);
			}
		}
		return $val;
	}
	
	private function isValidTimeStamp($timestamp) {
		$check = (is_int($timestamp) OR is_float($timestamp))
			? $timestamp
			: (string) (int) $timestamp;
		return  ($check === $timestamp)
			AND ( (int) $timestamp <=  PHP_INT_MAX)
			AND ( (int) $timestamp >= ~PHP_INT_MAX);
	}		
	
}
return 'FormDataManagerExportDataProcessor';