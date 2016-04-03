<?php
/**
 * Create a layout
 *
 * @package formdatamanager
 * @subpackage processors
 */
if (empty($scriptProperties['id'])) return $modx->error->failure($modx->lexicon('formdatamanager_layout_err_ns_name'));

$layout = $modx->getObject('FdmLayouts',array('formid' => $scriptProperties['id']));
if ($layout) return $modx->error->failure($modx->lexicon('formdatamanager_layout_err_ae'));

$layout = $modx->newObject('FdmLayouts');
$data = $_POST['data'];
$layout->set('formid',$scriptProperties['id']);
$layout->set('formtype','formz');
$layout->set('formfld_data',$data);
$layout->set('createdon',date('Y-m-d H:i:s',time()));
$layout->set('createdby',$modx->user->get('id'));

if ($layout->save() === false) {
    return $modx->error->failure($modx->lexicon('formdatamanager_layout_err_save'));
}

return $modx->error->success('',$layout);
