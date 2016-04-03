<?php
/**
 * Update a layout
 *
 * @package formdatamanager
 * @subpackage processors
 */
if (empty($scriptProperties['id'])) return $modx->error->failure($modx->lexicon('formdatamanager_layout_err_ns'));

$layout = $modx->getObject('FdmLayouts',$scriptProperties['id']);
if (empty($layout)) return $modx->error->failure($modx->lexicon('formdatamanager_layout_err_nf'));

$data = $_POST['data'];
$layout->set('formfld_data',$data);
$layout->set('editedon',date('Y-m-d H:i:s',time()));
$layout->set('editedby',$modx->user->get('id'));

if ($layout->save() === false) {
    return $modx->error->failure($modx->lexicon('formdatamanager_layout_err_save'));
}

return $modx->error->success('',$layout);
