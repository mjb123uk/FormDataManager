<?php
/**
 * Remove a table
 *
 * @package formdatamanager
 * @subpackage processors
 */
if (empty($scriptProperties['id'])) return $modx->error->failure($modx->lexicon('formdatamanager_table_err_ns'));

$layout = $modx->getObject('FdmLayouts',$scriptProperties['id']);
if (empty($layout)) return $modx->error->failure($modx->lexicon('formdatamanager_table_err_nf'));

$wtype = $layout->get('formtype');
if ($wtype != "table") return $modx->error->failure($modx->lexicon('formdatamanager_table_err_nf'));

if ($layout->remove() === false) {
	return $modx->error->failure($modx->lexicon('formdatamanager_table_err_remove'));
}

return $modx->error->success('',$layout);
