<?php

/**
 * Class FormDataManagerGetMapFldDataProcessor
 *
 * For FormDataManager Layout Grid.
 */
 
class FormDataManagerGetMapFldDataProcessor extends modProcessor
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
		$limit = (isset($scriptProperties['limit'])) ? $scriptProperties['limit'] : 20;
		if ($limit == 0) $limit = 999;
		$start = (isset($scriptProperties['start'])) ? $scriptProperties['start'] : 0;

		$tpl = (isset($scriptProperties['tpl'])) ? $scriptProperties['tpl'] : 0;
		$newtpl = (isset($scriptProperties['newtpl'])) ? $scriptProperties['newtpl'] : false;
		
		$data = array();
		$tpldata = array();
		$layout = array();
		$count = 0;
		
		$classname = 'FdmLayouts';

		$c = $this->modx->newQuery($classname);
		$c->select($this->modx->getSelectColumns($classname, $classname));
		// note formid = formid or formname
		switch ($formid) {
			case "formit":
			case "table":
				// custom table
				$c->where(array('formtype' => $formid,'formname' => $formname));
				break;
			default:
				// formz
				$c->where(array('formtype' => 'formz','formid' => $formid));
		}
		$fcount = $this->modx->getCount($classname, $c);
		$fdmdata = $this->modx->getCollection($classname, $c);
		if (!empty($fdmdata)) $layout = $fdmdata;

		if (count($layout)) {
			// Format for grid
			foreach($layout as $fdmd) {
				$fd = $fdmd->toArray();
				if ( ($fd["formtype"] == "table") && (empty($fd['formfld_data']) ) ) {
					// first time so build fields from table
					$query = "SHOW COLUMNS FROM ".$formname;
					$result = $this->modx->query($query);
					if (!is_object($result)) return $this->failure($this->modx->lexicon('formdatamanager_tables_sqlfail'));
					$flddata = $result->fetchAll(PDO::FETCH_ASSOC);
					$ord = 0;
					$ic = 0;
					$count = count($flddata);
					if ($limit != 999) $limit += $start;
					foreach ($flddata as &$field) {
						if ($ic >= $limit) break;
						if ($ic < $start) {
							$ic++;
							continue;
						}
						$fl = $field['Field'];
						//$type = $this->getFieldType($field['Type']);
						$type = 'text';
						$type = $field['Type'];
						$extracted_columnspec = $this->extractColumnSpec($type);
						$type = strtolower($extracted_columnspec['type']);
						$spec = $extracted_columnspec['spec_in_brackets'];
						$ft = $this->getTypeClass($type);
						$inc = 1;
						if ($ft == "spatial") $inc = 0;
						if ($inc) $data[] = array('id' => $ord,'label' => $fl);
						$ord++;
						$ic++;						
					}				
				}
				else {
					$flddata = json_decode($fd['formfld_data']);
					foreach($flddata as $ro) {
						$rows = json_decode($ro,TRUE);
						$ic = 0;
						$count = count($rows);
						if ($limit != 999) $limit += $start;
						foreach($rows as $r) {
							if ($ic >= $limit) break;
							if ($ic < $start) {
								$ic++;
								continue;
							}
							$tpldata[] = $r;
							$ic++;
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
					$c->limit($limit, $start); 
					$c->sortby('`id`','DESC');
					$frmrecs = $this->modx->getCollection($classname, $c);
					$frmflds = array();
					// Add fields for Id, date & IP
					//$frmflds['id'] = 0;
					//$frmflds['sent on'] = 0;
					//$frmflds['ip'] = 0;					
					$fc = 0;
					$fihc = $this->modx->getOption('fdm_formit_lookup_history_count',null,10);
					foreach($frmrecs as $frmr) {
						if ($fc > $fihc) break;	// limit to last n recs (default 10) - see system setting fdm_formit_lookup_history_count
						$fd = $frmr->toArray();
						$values = $this->modx->fromJSON($fd['values'], false);
						foreach($values as $k => $v) {
							if (!array_key_exists($k, $frmflds)) $frmflds[$k] = $v;				
						}
						$fc++;
					}
					//ksort($frmflds);
					$ord = 0;
					foreach($frmflds as $fl => $fd) {
						$type = 'text';
						if (is_array($fd)) $type = 'textarea';
						$data[] = array('id' => $ord,'label' => $fl);
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
					$c->limit($limit, $start); 					
					$c->sortby('`order`','ASC');				
					$frmflds = $this->modx->getCollection($classname, $c);
					$ord = 0;
					foreach($frmflds as $frmfld) {
						$fd = $frmfld->toArray();
						$settings = $this->modx->fromJSON($fd['settings'], false);
						$type = $fd['type'];
						switch ($type) {
							case "date":
							case "number":
							case "textarea":
								break;
							default:
								$type = "text";
								break;
						}
						$data[] = array('id' => $fd['id'],'label' => $settings->label);
						$ord++;
					}
				}
			}
		}
		
		if ( ($newtpl) || ( ($tpl) && (count($tpldata) == 0) ) ) {
			unset($c);
			unset($flddata);	
			$classname = 'FdmLayouts';
			$c = $this->modx->newQuery($classname);
			$c->select($this->modx->getSelectColumns($classname, $classname));
			$c->where(array('id' => $tpl));
			$tcount = $this->modx->getCount($classname, $c);
			if ($tcount<1) return $this->failure($this->modx->lexicon('formdatamanager_maptemplatelayout_tplerror'));
			$tpldata = $this->modx->getCollection($classname, $c);
			if (!empty($tpldata)) $layout = $tpldata;
			$tpldata = array();
			$tplmap = array();	// mapping data
			if (count($layout)) {
				// Format for grid
				foreach($layout as $fdmd) {
					$fd = $fdmd->toArray();
					$flddata = json_decode($fd['formfld_data']);
					foreach($flddata as $ro) {
						$rows = json_decode($ro,TRUE);
						//$ord = 0;
						foreach($rows as $r) {
							$r["include"] = 1;
							$r["mapfield"] = "";
							$r["tplfield"] = 1;
							$r["ofn"] = "";
							$tpldata[] = $r;
							//$ord++;
						}
					}
					$fx = $fd['formfld_extra'];
					if (!empty($fx)) $tplmap = json_decode($fx,true);	
				}
			}
			// compare data again template fields - and set any matches
			foreach ($data as $r) {
				$lbl = $r["label"];
				$found = false;
				for ($i=0; $i<count($tpldata); $i++) {
					if ($tpldata[$i]['label'] == $lbl) {
						$tpldata[$i]['mapfield'] = $lbl;
						$found = true;
						break;
					}
				}
				// if mapping data - then try using it
				if ( (!$found) && (count($tplmap)) ) {
					$wlbl = strtolower($lbl);
					if (isset($tplmap[$wlbl])) $tlbl = $tplmap[$wlbl];
					for ($i=0; $i<count($tplmap); $i++) {
						if ($tpldata[$i]['label'] == $tlbl) {
							if (empty($tpldata[$i]['mapfield'])) $tpldata[$i]['mapfield'] = $lbl;
							break;
						}
					}				
				}
			}
		}
			
		return $this->outputArray($tpldata,count($tpldata));
    }
	
	/**
	* @param $type
	* @return string
	*/
    private function getFieldType($type){
        if (preg_match('/(blob|text|enum|set)/i',$type)) {
            $type = 'string';
        } elseif (preg_match('/(int|float|double|decimal|dec|bool)/i',$type)) {
            $type = 'number';
        } else {
            $type = 'auto';
        }
        return $type;
    }

   /**
     * Parses ENUM/SET values
     *
     * @param string $definition The definition of the column
     *                           for which to parse the values
     * @param bool   $escapeHtml Whether to escape html entitites
     *
     * @return array
     */
    private function parseEnumSetValues($definition, $escapeHtml = true)
    {
        $values_string = htmlentities($definition, ENT_COMPAT, "UTF-8");
        // There is a JS port of the below parser in functions.js
        // If you are fixing something here,
        // you need to also update the JS port.
        $values = array();
        $in_string = false;
        $buffer = '';

        for ($i=0; $i<strlen($values_string); $i++) {

            $curr = $values_string[$i];
            $next = ($i == strlen($values_string)-1) ? '' : $values_string[$i+1];

            if (! $in_string && $curr == "'") {
                $in_string = true;
            } else if (($in_string && $curr == "\\") && $next == "\\") {
                $buffer .= "&#92;";
                $i++;
            } else if (($in_string && $next == "'")
                && ($curr == "'" || $curr == "\\")
            ) {
                $buffer .= "&#39;";
                $i++;
            } else if ($in_string && $curr == "'") {
                $in_string = false;
                $values[] = $buffer;
                $buffer = '';
            } else if ($in_string) {
                 $buffer .= $curr;
            }

        }

        if (strlen($buffer) > 0) {
            // The leftovers in the buffer are the last value (if any)
            $values[] = $buffer;
        }

        if (! $escapeHtml) {
            foreach ($values as $key => $value) {
                $values[$key] = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
            }
        }

        return $values;
    }
	
	/**
     * Extracts the various parts from a column spec
     *
     * @param string $columnspec Column specification
     *
     * @return array associative array containing type, spec_in_brackets
     *          and possibly enum_set_values (another array)
     */
    private function extractColumnSpec($columnspec)
    {
        $first_bracket_pos = strpos($columnspec, '(');
        if ($first_bracket_pos) {
            $spec_in_brackets = chop(
                substr(
                    $columnspec,
                    $first_bracket_pos + 1,
                    (strrpos($columnspec, ')') - $first_bracket_pos - 1)
                )
            );
            // convert to lowercase just to be sure
            $type = strtolower(chop(substr($columnspec, 0, $first_bracket_pos)));
        } else {
            $type = strtolower($columnspec);
            $spec_in_brackets = '';
        }

        if ('enum' == $type || 'set' == $type) {
            // Define our working vars
            $enum_set_values = $this->parseEnumSetValues($columnspec, false);
            $printtype = $type
                . '(' .  str_replace("','", "', '", $spec_in_brackets) . ')';
            $binary = false;
            $unsigned = false;
            $zerofill = false;
        } else {
            $enum_set_values = array();

            /* Create printable type name */
            $printtype = strtolower($columnspec);

            // Strip the "BINARY" attribute, except if we find "BINARY(" because
            // this would be a BINARY or VARBINARY column type;
            // by the way, a BLOB should not show the BINARY attribute
            // because this is not accepted in MySQL syntax.
            if (preg_match('@binary@', $printtype)
                && ! preg_match('@binary[\(]@', $printtype)
            ) {
                $printtype = preg_replace('@binary@', '', $printtype);
                $binary = true;
            } else {
                $binary = false;
            }

            $printtype = preg_replace(
                '@zerofill@', '', $printtype, -1, $zerofill_cnt
            );
            $zerofill = ($zerofill_cnt > 0);
            $printtype = preg_replace(
                '@unsigned@', '', $printtype, -1, $unsigned_cnt
            );
            $unsigned = ($unsigned_cnt > 0);
            $printtype = trim($printtype);
        }

        $attribute     = ' ';
        if ($binary) {
            $attribute = 'BINARY';
        }
        if ($unsigned) {
            $attribute = 'UNSIGNED';
        }
        if ($zerofill) {
            $attribute = 'UNSIGNED ZEROFILL';
        }

        $can_contain_collation = false;
        if (! $binary
            && preg_match(
                "@^(char|varchar|text|tinytext|mediumtext|longtext|set|enum)@", $type
            )
        ) {
            $can_contain_collation = true;
        }

        $displayed_type = htmlspecialchars($printtype);

        return array(
            'type' => $type,
            'spec_in_brackets' => $spec_in_brackets,
            'enum_set_values'  => $enum_set_values,
            'print_type' => $printtype,
            'binary' => $binary,
            'unsigned' => $unsigned,
            'zerofill' => $zerofill,
            'attribute' => $attribute,
            'can_contain_collation' => $can_contain_collation,
            'displayed_type' => $displayed_type
        );
    }
	
	/**
     * Returns class of a type, used for functions available for type
     * or default values.
     *
     * @param string $type The data type to get a class.
     *
     * @return string
     *
     */
    private function getTypeClass($type)
    {
        $type = strtoupper($type);
        switch ($type) {
        case 'TINYINT':
        case 'SMALLINT':
        case 'MEDIUMINT':
        case 'INT':
        case 'BIGINT':
        case 'DECIMAL':
        case 'FLOAT':
        case 'DOUBLE':
        case 'REAL':
        case 'BIT':
        case 'BOOLEAN':
        case 'SERIAL':
            return 'number';

        case 'DATE':
        case 'DATETIME':
        case 'TIMESTAMP':
        case 'TIME':
        case 'YEAR':
            return 'date';

        case 'CHAR':
        case 'VARCHAR':
        case 'TINYTEXT':	
			return 'text';
			
        case 'TEXT':
        case 'MEDIUMTEXT':			
        case 'LONGTEXT':
        case 'BINARY':
        case 'VARBINARY':
        case 'TINYBLOB':
        case 'MEDIUMBLOB':
        case 'BLOB':
        case 'LONGBLOB':
        case 'ENUM':
        case 'SET':
            return 'textarea';

        case 'GEOMETRY':
        case 'POINT':
        case 'LINESTRING':
        case 'POLYGON':
        case 'MULTIPOINT':
        case 'MULTILINESTRING':
        case 'MULTIPOLYGON':
        case 'GEOMETRYCOLLECTION':
            return 'spatial';
        }

        return '';
    }	
	
}
return 'FormDataManagerGetMapFldDataProcessor';