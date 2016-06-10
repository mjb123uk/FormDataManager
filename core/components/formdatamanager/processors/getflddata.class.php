<?php

/**
 * Class FormDataManagerGetFldDataProcessor
 *
 * For FormDataManager Layout Grid.
 */
 
class FormDataManagerGetFldDataProcessor extends modProcessor
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
		
		$data = array();
		$layout = array();
		
		$classname = 'FdmLayouts';
		$c = $this->modx->newQuery($classname);
		$c->select($this->modx->getSelectColumns($classname, $classname));
		// note formid = formid or formname
		switch ($formid) {
			case "formit":
				$c->where(array('formname' => $formname));
				break;
			case "table":
				// custom table
				$c->where(array('formname' => $formname));
				break;			
			default:
				// formz
				$c->where(array('formid' => $formid));
		}
		$count = $this->modx->getCount($classname, $c);
		$fdmdata = $this->modx->getCollection($classname, $c);
		if (!empty($fdmdata)) $layout = $fdmdata;

		if (count($layout)) {
			// Format for grid
			foreach($layout as $fdmd) {
				$fd = $fdmd->toArray();
				if ( ($fd["formtype"] == "table") && (empty($fd['formfld_data'])) ) {
					// first time so build fields from table
					$query = "SHOW COLUMNS FROM ".$formname;
					$result = $this->modx->query($query);
					if (!is_object($result)) return $this->failure($this->modx->lexicon('formdatamanager_tables_sqlfail'));
					$flddata = $result->fetchAll(PDO::FETCH_ASSOC);
					$ord = 0;
					foreach ($flddata as &$field) {
						$fl = $field['Field'];
						//$type = $this->getFieldType($field['Type']);
						$type = 'text';
						$data[] = array('id' => $ord,'order' => $ord,'label' => $fl,'type' => $type,'include' => 1,'coltitle' => $fl,'default' => '');
						$ord++;
					}				
				}
				else {
					$ldata = json_decode($fd['formfld_data']);
					foreach($ldata as $ro) {
						$rows = json_decode($ro,TRUE);
						foreach($rows as $r) {
							$data[] = $r;
						}
					}
				}
			}
		}
		else {
			if ($formid == "formit") {
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
					$c->sortby('`id`','DESC');
					$frmrecs = $this->modx->getCollection($classname, $c);
					$frmflds = array();
					$fc = 0;
					foreach($frmrecs as $frmr) {
						if ($fc > 10) break;	// limit to last 10 recs 
						$fd = $frmr->toArray();
						$values = $this->modx->fromJSON($fd['values'], false);
						foreach($values as $k => $v) {
							if (!array_key_exists($k, $frmflds)) $frmflds[$k] = $v;				
						}
						$fc++;
					}
					ksort($frmflds);
					$ord = 0;
					foreach($frmflds as $fl => $fd) {
						$type = 'text';
						if (is_array($fd)) $type = 'textarea';
						$data[] = array('id' => $ord,'order' => $ord,'label' => $fl,'type' => $type,'include' => 1,'coltitle' => $fl,'default' => '');
						$ord++;
					}
				}	
			} 
			else {
				// get latest formz fields and use for new layout
				$packageName = "formz";
				$packagepath = $this->modx->getOption('core_path') . 'components/' . $packageName . '/';
				$modelpath = $packagepath . 'model/';
				if (is_dir($modelpath)) {
					$this->modx->addPackage($packageName, $modelpath);
					$classname = 'fmzFormsFields';
					$c = $this->modx->newQuery($classname);
					$c->select($this->modx->getSelectColumns($classname, $classname));
					$c->where(array('form_id' => $formid));
					$count = $this->modx->getCount($classname, $c);
					$c->sortby('`order`','ASC');
					$frmflds = $this->modx->getCollection($classname, $c);
					$ord = 0;
					foreach($frmflds as $frmfld) {
						$fd = $frmfld->toArray();
						$settings = $this->modx->fromJSON($fd['settings'], false);
						$data[] = array('id' => $fd['id'],'order' => $ord,'label' => $settings->label,'type' => $fd['type'],'include' => 1,'coltitle' => $settings->label,'default' => '');
						$ord++;
					}
				}
			}
		}
			
		return $this->outputArray($data,count($data));
    }
	
	/**
	* @param $type
	* @return string
	*/
    public function getFieldType($type){
        if (preg_match('/(blob|text|enum|set)/i',$type)) {
            $type = 'string';
        } elseif (preg_match('/(int|float|double|decimal|dec|bool)/i',$type)) {
            $type = 'number';
        } else {
            $type = 'auto';
        }
        return $type;
    }
}
return 'FormDataManagerGetFldDataProcessor';