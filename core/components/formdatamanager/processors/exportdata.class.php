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
		$startDate = $scriptProperties['startDate'];
    	$endDate = $scriptProperties['endDate'];
		
		$layout = array();
		$loflds = array();		
		
		$classname = 'FdmLayouts';
		$c = $this->modx->newQuery($classname);
		$c->select($this->modx->getSelectColumns($classname, $classname));
		$c->where(array('formid' => $formid));
		$count = $this->modx->getCount($classname, $c);
		$fdmdata = $this->modx->getCollection($classname, $c);
		if (!empty($fdmdata)) $layout = $fdmdata;
		
		$lists = array();
		$rcount = 0;
		$header = array('Sent On','IP Address');
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
				foreach($loflds as $lofld) {
					if ($lofld['include']) {		// only include if column wanted
						$fct = trim($lofld['coltitle']);
						$header[] = $fct;
					}
				}
			}
			
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
				if (! empty($startDate)) {
					$c->andCondition(array(
						'senton:>' => date('Y-m-d', strtotime($startDate)) . ' 00:00:00'
					));
				}
				if (! empty($endDate)) {
					$c->andCondition(array(
						'senton:<' => date('Y-m-d', strtotime($endDate)) . ' 23:59:59'
					));
				}
				$count = $this->modx->getCount($classname, $c);
				if ($count > 0) {
					$c->sortby('`senton`','ASC');
					$frms = $this->modx->getCollection($classname, $c);
					foreach ($frms as $itemobj) {
						$item = $itemobj->toArray();
						$form = $this->modx->getObject('fmzForms', $item['form_id']);
						$formData = unserialize($item['data']);
						$fieldsData = $this->modx->getCollection('fmzFormsDataFields', array('data_id' => $item['id']));

						$data = array();
						$data[] = !empty($item['senton']) ? date('d/m/Y H:i:s', strtotime($item['senton'])) : '';
						$data[] = !empty($formData['ip_address']) ? $formData['ip_address'] : '';
						foreach($loflds as $lofld) {
							if ($lofld['include']) {		// only include if column wanted
								$fl = $lofld['label'];						
								$v = "";
								foreach ($fieldsData as $fd) {
									$label = $fd->label;
									if ($label == $fl) {

										$values = unserialize($fd->value);
										if (is_array($values)) $values = implode('/', $values);
										$v = $values;
										break;
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
		
	    $modRes = $this->modx->newObject('modResource');
		if (!isset($form)) $alias = str_replace(' ','-',$formname);
        else $alias = $modRes->cleanAlias($form->get('name'));
        $now = date('d_m_Y_H_i_s', time());
        $filename = $alias . '_' . $now . '.csv';
		
        $csv = $this->toCSV($lists, $header, ',', '"', "\r\n");
		
		
		// Update Layout last export dates
		$layout = $this->modx->getObject('FdmLayouts',$layoutid);
		$df = null;
		if (! empty($startDate)) {
			$df = date('Y-m-d', strtotime($startDate)) . ' 00:00:00';
        }
        if (! empty($endDate)) {
			$dt = date('Y-m-d', strtotime($endDate)) . ' 23:59:59';
        }
		else {
			$dt = date('Y-m-d H:i:s',time());
		}
		$layout->set('lastexportfrom',$df);
		$layout->set('lastexportto',$dt);
		$layout->save();
		
        $this->download($csv, $filename);
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

    private function setHeaders(array $headers)
    {
        if (headers_sent()) return false;

        foreach ($headers as $header) {
            header((string) $header);
        }
    }	
	
}
return 'FormDataManagerExportDataProcessor';