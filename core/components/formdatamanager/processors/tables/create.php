<?php
/**
 * Create a layout record for a table
 *
 * @package formdatamanager
 * @subpackage processors
 */
$formid = 0;
$formname = $scriptProperties['dbtables'];	// tablename
$id = $formname;
$lf = "formname";
$formtype = "table";
if (empty($id)) return $modx->error->failure($modx->lexicon('formdatamanager_layout_err_ns_name'));

$layout = $modx->getObject('FdmLayouts',array($lf => $id));
if ($layout) return $modx->error->failure($modx->lexicon('formdatamanager_layout_err_ae'));

$layout = $modx->newObject('FdmLayouts');
$data = $_POST['data'];
$layout->set('formid',$formid);
$layout->set('formtype',$formtype);
$layout->set('formname',$formname);
if ($layout->save() === false) {
    return $modx->error->failure($modx->lexicon('formdatamanager_layout_err_save'));
}

return $modx->error->success('',$layout);
