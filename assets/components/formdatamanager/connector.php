<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config.core.php';
require_once MODX_CORE_PATH.'config/'.MODX_CONFIG_KEY.'.inc.php';
require_once MODX_CONNECTORS_PATH.'index.php';

$corePath = $modx->getOption('formdatamanager.core_path', null, $modx->getOption('core_path') . 'components/formdatamanager/');
require_once $corePath . 'model/formdatamanager/formdatamanager.class.php';
$modx->formdatamanager = new formdatamanager($modx);

$modx->lexicon->load('formdatamanager:default');

$processor_path = $modx->getOption('formdatamanager.core_path', null, $modx->getOption('core_path').'components/formdatamanager/').'processors/mgr/';
$modx->lexicon->load('formdatamanager:default');
$modx->request->handleRequest(array(
    'processors_path' => $processor_path, 
    'location' => ''
));
