<?php
/*
 * fdmViewExportFunctions
 *
 * FormDataManager
 */

class FormDataManagerViewExportFunctions
{
 
    public function fdmfunctionlist() {
        $afns = get_class_methods($this);
        $fns = array();
        foreach($afns as $fn) {
            // get the private function names
            if (substr($fn,0,1) == "_") $fns[] = substr($fn,1);
        }
        return $fns;
        
    }
    
    public function fdmdofunction($fn,$d) {
        // get available functions 
        $afns = $this->fdmfunctionlist();
        // check the requested function is in list
        if (!in_array($fn,$afns)) return $d;
        $w = '_'.$fn;
        return ($this->$w($d));
    }
    
    private function _ShowAsY($fld) {
        if (empty($fld)) return 'N';
        else return 'Y';
    }
    
    private function _ShowAsYes($fld) {
        if (empty($fld)) return 'No';
        else return 'Yes';
    }
    
    private function _ShowAs1($fld) {
        if (empty($fld)) return 0;
        else return 1;
    }
    
    private function _ShowAsTrue($fld) {
        if (empty($fld)) return 'False';
        else return 'True';
    }
    
    private function _ShowAsUpper($fld) {
        if (empty($fld)) return '';
        else return strtoupper($fld);
    }
    
    private function _ShowAsLower($fld) {
        if (empty($fld)) return '';
        else return strtolower($fld);
    }
	
	private function _ShowAsYNBlank($fld) {
		$fld = trim($fld);
		if (empty($fv)) return "";
		$fld = strtoupper($fld);
		switch ($fld) {
			case "Y":
			case "YES":
			case "1":
				return "Y";
				break;
			case "N":
			case "NO":
			case "0":
				return "N";
				break;				
		}
		// has content so take as positive
		return "Y";
    }
    
}

$fdmVEF = new FormDataManagerViewExportFunctions();
return $fdmVEF;