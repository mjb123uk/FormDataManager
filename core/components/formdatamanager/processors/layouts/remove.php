<?php
/**
 * Remove a layout
 *
 * @package formdatamanager
 * @subpackage processors
 */
if (empty($scriptProperties['id'])) return $modx->error->failure($modx->lexicon('formdatamanager_layout_err_ns'));

$layout = $modx->getObject('FdmLayouts',$scriptProperties['id']);
if (empty($layout)) return $modx->error->failure($modx->lexicon('formdatamanager_layout_err_nf'));

$wtype = $layout->get('formtype');
$rtbl = false;
if ($wtype == "table") {
	$layout->set('formfld_data',null);
	$layout->set('formfld_extra',null);
	$layout->set('createdon',null);
	$layout->set('createdby',0);
	$layout->set('editedon',null);
	$layout->set('editedby',0);
	$layout->set('lastexportfrom',null);
	$layout->set('lastexportto',null);
	$layout->set('lastautoexpfrom',null);
	$layout->set('lastautoexpto',null);
	if ($layout->save() === false) {
		return $modx->error->failure($modx->lexicon('formdatamanager_layout_err_remove'));
	}
}
else {
	if ($layout->remove() === false) {
		return $modx->error->failure($modx->lexicon('formdatamanager_layout_err_remove'));
	}
}

return $modx->error->success('',$layout);
