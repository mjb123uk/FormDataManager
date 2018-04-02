<?php
/**
 * Create a layout
 *
 * @package formdatamanager
 * @subpackage processors
 */
$formid = $scriptProperties['id'];
$formname = $scriptProperties['formname'];
if ($formid == "formit") {
	$id = $formname;
	$lf = "formname";
	$formtype = "formit";
	$formid = 0;
}
else {
	$id = $formid;
	$lf = "formid";
	$formtype = "formz";
}
if (empty($id)) return $modx->error->failure($modx->lexicon('formdatamanager_layout_err_ns_name'));

$layout = $modx->getObject('FdmLayouts',array($lf => $id));
if ($layout) return $modx->error->failure($modx->lexicon('formdatamanager_layout_err_ae'));

$layout = $modx->newObject('FdmLayouts');
$data = $_POST['data'];
$exdata = (isset($_POST['exdata'])) ? $_POST['exdata'] : null;
$layout->set('formid',$formid);
$layout->set('formtype',$formtype);
$layout->set('formname',$formname);
$layout->set('formfld_data',$data);
$layout->set('formfld_extra',$exdata);
$layout->set('createdon',date('Y-m-d H:i:s',time()));
$layout->set('createdby',$modx->user->get('id'));

if ($layout->save() === false) {
    return $modx->error->failure($modx->lexicon('formdatamanager_layout_err_save'));
}

return $modx->error->success('',$layout);
