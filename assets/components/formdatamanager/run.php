<?php
/**
 * FormDataManager
 *
 * @package formdatamanager
 * @var modX $modx
 */
require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config.core.php';
require_once MODX_CORE_PATH.'config/'.MODX_CONFIG_KEY.'.inc.php';
require_once MODX_CONNECTORS_PATH.'index.php';

$fdmRunOpts = (isset($argv[1])) ? $argv[1] : "";

$corePath = $modx->getOption('formdatamanager.core_path',null,$modx->getOption('core_path').'components/formdatamanager/');
require_once $corePath.'elements/snippets/snippet.fdmExportTask.php';

//$modx->log(modX::LOG_LEVEL_INFO, '[FormDataManager] Done!');
