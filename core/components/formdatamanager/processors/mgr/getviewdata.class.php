<?php

/**
 * Class FormDataManagerGetViewDataProcessor
 *
 * For FormDataManager ViewData Grid.
 */
 
class FormDataManagerGetViewDataProcessor extends modProcessor
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
		$limit = (isset($scriptProperties['limit'])) ? $scriptProperties['limit'] : 20;
		$start = (isset($scriptProperties['start'])) ? $scriptProperties['start'] : 0;
		$dateFormat = $this->modx->getOption('manager_date_format') . ' ' . $this->modx->getOption('manager_time_format');
		$afns = array();
		
		$xfs = $this->modx->runSnippet("fdmViewExportFunctions",array());
		$afns = $xfs->fdmfunctionlist();

		$vrows = array();
		$layout = array();
		$loflds = array();		
		
		$classname = 'FdmLayouts';
		$c = $this->modx->newQuery($classname);
		$c->select($this->modx->getSelectColumns($classname, $classname));
		$c->where(array('id' => $layoutid));
		$count = $this->modx->getCount($classname, $c);
		$fdmdata = $this->modx->getCollection($classname, $c);
		if (!empty($fdmdata)) $layout = $fdmdata;
				
		if (count($layout)) {
			// Format for grid
			foreach($layout as $fdmd) {
				$fd = $fdmd->toArray();
				$ldata = json_decode($fd['formfld_data']);
				foreach($ldata as $ro) {
					$rows = json_decode($ro,TRUE);
					foreach($rows as $r) {
						$loflds[] = $r;
					}
				}
			}
			switch ($formid) {
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
						$count = $this->modx->getCount($classname, $c);
						$c->limit($limit, $start); 
						if ( ($template) && (!empty($selectionfield)) ) $c->sortby('`'.$selectionfield.'`','DESC');
						else $c->sortby('`id`','DESC');
						$frmrecs = $this->modx->getCollection($classname, $c);
						$frmflds = array();
						foreach($frmrecs as $frmr) {
							$item = $frmr->toArray();
							$values = $this->modx->fromJSON($item['values'], false);
							$data = array();
							if (!$template) {
								$data['senton'] = !empty($item['date']) ? date($dateFormat, $item['date']) : '';
								$data['ip_address'] = !empty($item['ip']) ? $item['ip'] : '';
							}
							foreach($loflds as $lofld) {
								if ($lofld['include']) {		// only include if column wanted
									$fl = $lofld['label'];
									$str = preg_replace('/[^A-Za-z0-9_-]/', '', $fl);
									if ( ($template) && (!empty($lofld['mapfield'])) ) $fl = $lofld['mapfield'];
									$v = null;
									if ( ($template) && ($fl == "date") && (!empty($item['date'])) ) $v = date($dateFormat, $item['date']);
									if ( ($template) && ($fl == "ip") && (!empty($item['ip'])) ) $v = $item['ip'];
									if (is_null($v)) $v = (isset($values->$fl)) ? $values->$fl : "";
									if (is_array($v)) $v = implode('/', $v);
									$ofn = (isset($lofld['ofn'])) ? trim($lofld['ofn']) : "";
									if (!empty($ofn)) $v = $xfs->fdmdofunction($ofn,$v);
									if ( (empty($v)) && (!empty($lofld['default'])) ) $v = $lofld['default'];
									$data[$str] = $v;
								}
							}
							$vrows[] = $data;
						}
					}
					break;
				case "table":
					$q = "SELECT * FROM ".$formname;
					if (!empty($selectionfield)) {
						$q .= ' ORDER BY `'.$selectionfield.'`';
					}
					if ($limit > 0) $q .= ' LIMIT '.$limit;
					if ($start > 0) $q .= ' OFFSET '.$start;
					$result = $this->modx->query($q);
					if (is_object($result)) {
						$tdata = $result->fetchAll(PDO::FETCH_ASSOC);
					}				
					$count = 0;
					$tbldata = array();			
					// get table record count        
					$q = "SHOW TABLE STATUS LIKE '".$formname."'";
					$result = $this->modx->query($q);
					if (is_object($result)) {
						$tbldata = $result->fetchAll(PDO::FETCH_ASSOC);
					}
					foreach ($tbldata as &$tda) {
						$count = $tda['Rows'];
					}
					foreach ($tdata as &$values) {	
						$data = array();

						foreach($loflds as $lofld) {
							if ($lofld['include']) {		// only include if column wanted
								$fl = $lofld['label'];
								$str = preg_replace('/[^A-Za-z0-9_-]/', '', $fl);
								if ( ($template) && (!empty($lofld['mapfield'])) ) $fl = $lofld['mapfield'];
								$v = (isset($values[$fl])) ? $values[$fl] : "";
								if (is_array($v)) $v = implode('/', $v);
								$ofn = (isset($lofld['ofn'])) ? trim($lofld['ofn']) : "";
								if (!empty($ofn)) $v = $xfs->fdmdofunction($ofn,$v);								
								if ( (empty($v)) && (!empty($lofld['default'])) ) $v = $lofld['default'];
								$v = $this->formatfld($v,$lofld['type'],$dateFormat);
								$data[$str] = $v;
							}
						}
						$vrows[] = $data;
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
						$count = $this->modx->getCount($classname, $c);
						$c->limit($limit, $start);
						if ( ($template) && (!empty($selectionfield)) ) $c->sortby('`'.$selectionfield.'`','DESC');						
						$c->sortby('`senton`','DESC');
						$frms = $this->modx->getCollection($classname, $c);
						foreach ($frms as $itemobj) {
							$item = $itemobj->toArray();
							$form = $this->modx->getObject('fmzForms', $item['form_id']);
							$formData = unserialize($item['data']);
							$fieldsData = $this->modx->getCollection('fmzFormsDataFields', array('data_id' => $item['id']));

							$data = array();
							if (!$template) {
								$data['senton'] = !empty($item['senton']) ? date($dateFormat, strtotime($item['senton'])) : '';
								$data['ip_address'] = !empty($formData['ip_address']) ? $formData['ip_address'] : '';
							}
							foreach($loflds as $lofld) {
								if ($lofld['include']) {		// only include if column wanted
									$fl = $lofld['label'];
									$str = preg_replace('/[^A-Za-z0-9_-]/', '', $fl);									
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

									$data[$str] = $v;
								}
							}	
							$vrows[] = $data;
						}
					}
			}
		}
		return $this->outputArray($vrows,$count);
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
return 'FormDataManagerGetViewDataProcessor';