<?php

/**
 * Class FormDataManagerGetTableFieldsProcessor
 *
 * For FormDataManager New Tables.
 */
 
class FormDataManagerGetTableFieldsProcessor extends modProcessor
{

    public function initialize() {
        return parent::initialize();
    }
    
    public function checkPermissions() { return true; }

    public function process()
    {
		$scriptProperties = $this->getProperties();
		$formname = $scriptProperties['formname'];
		$fieldList = array();
		$fieldList[] = array('id' => 0, 'name' => "N/A");
		
		$query = "SHOW COLUMNS FROM ".$formname;
		$result = $this->modx->query($query);
		if (is_object($result)) {
			$flddata = $result->fetchAll(PDO::FETCH_ASSOC);
			$ord = 1;

			foreach ($flddata as &$field) {
				$fl = $field['Field'];
				//$ft = $this->getFieldType($field['Type']);
				$type = $field['Type'];
				$extracted_columnspec = $this->extractColumnSpec($type);
				$type = strtolower($extracted_columnspec['type']);
				$spec = $extracted_columnspec['spec_in_brackets'];
				$ft = $this->getTypeClass($type);
				$inc = false;
				if ($ft == "DATE") $inc = true;
				else {
					switch ($type) {
						case "int":
						case "char":
						case "varchar":
							if ($spec < 8) break;
							$inc = true;
							break;
					}
				}
				if ($inc) {
					$fieldList[] = array('id' => $ord, 'name' => $fl);
					$ord++;
				}
			}
		}
		return $this->outputArray($fieldList,count($fieldList));
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
            // MJB - $enum_set_values = self::parseEnumSetValues($columnspec, false);
			$enum_set_values = array();
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
            return 'NUMBER';

        case 'DATE':
        case 'DATETIME':
        case 'TIMESTAMP':
        case 'TIME':
        case 'YEAR':
            return 'DATE';

        case 'CHAR':
        case 'VARCHAR':
        case 'TINYTEXT':
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
            return 'CHAR';

        case 'GEOMETRY':
        case 'POINT':
        case 'LINESTRING':
        case 'POLYGON':
        case 'MULTIPOINT':
        case 'MULTILINESTRING':
        case 'MULTIPOLYGON':
        case 'GEOMETRYCOLLECTION':
            return 'SPATIAL';
        }

        return '';
    }
	
}
return 'FormDataManagerGetTableFieldsProcessor';