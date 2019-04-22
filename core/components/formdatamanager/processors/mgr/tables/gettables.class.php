<?php

/**
 * Class FormDataManagerGetTablesProcessor
 *
 * For FormDataManager New Tables.
 */
 
class FormDataManagerGetTablesProcessor extends modProcessor
{

    public function initialize() {
        return parent::initialize();
    }
    
    public function checkPermissions() { return true; }

    public function process()
    {
		$scriptProperties = $this->getProperties();
		// get existing tables
		$xtbls = explode("~",$scriptProperties['tbldata']);
		$tables = $tableList = array();
        $q = "SHOW TABLE STATUS";
        $result = $this->modx->query($q);
        if (is_object($result)) {
            $tables = $result->fetchAll(PDO::FETCH_ASSOC);
        }
		// get modx table names
		$modxtables = $this->getmodxtables();
		$tc = 0;
        foreach ($tables as $table) {
			$tn = trim($table['Name']);
			if (in_array($tn,$xtbls)) continue;
			$notmx = true;
			foreach ($modxtables as $mxt) {
				$w = 0-strlen($mxt);
				$ww = substr($tn,$w);
				if ($ww == $mxt) {
					$notmx = false;
					break;
				}
			}	
			if ($notmx) {
				$tableList[] = array(
					'table' => $tn,
					'id' => $tc++,
					'name' => $tn,
					'rows'=>$table['Rows']
				);
			}
        }
        unset($tables);

		return $this->outputArray($tableList,count($tableList));
    }
	
	private function getmodxtables()
	{
		$tbls = array();
		// add the table used by FormDataManager to hide it
		$tbls[] = "fdm_layouts";	
		// 'modx';
		$this->parseSchema(MODX_CORE_PATH . "/model/schema/modx.mysql.schema.xml",$tbls);
		// 'modx.transport';
		$this->parseSchema(MODX_CORE_PATH . "/model/schema/modx.transport.mysql.schema.xml",$tbls);
		// 'modx.registry.db';
		$this->parseSchema(MODX_CORE_PATH . "/model/schema/modx.registry.db.mysql.schema.xml",$tbls);
		// 'modx.sources';
		$this->parseSchema(MODX_CORE_PATH . "/model/schema/modx.sources.mysql.schema.xml",$tbls);
		natcasesort($tbls);
		return $tbls;
	}

	private function parseSchema($file,&$tbls) {
		if (file_exists($file)) {
			$xml = simplexml_load_file($file);
			foreach($xml->children() as $obj) {
				$tn = $obj->attributes()->table;
				if (!empty($tn)) $tbls[] = trim($tn);
			}
		}
	}

}
return 'FormDataManagerGetTablesProcessor';