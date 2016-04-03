<?php
/**
 * Remove a layout
 *
 * @package formdatamanager
 * @subpackage processors
 */
if (empty($scriptProperties['id'])) return $modx->error->failure($modx->lexicon('formdatamanager.layout_err_ns'));

$layout = $modx->getObject('FdmLayouts',$scriptProperties['id']);
if (empty($layout)) return $modx->error->failure($modx->lexicon('formdatamanager.layout_err_nf'));

if ($layout->remove() === false) {
    return $modx->error->failure($modx->lexicon('formdatamanager.layout_err_remove'));
}

return $modx->error->success('',$layout);
