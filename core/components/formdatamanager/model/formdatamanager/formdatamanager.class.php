<?php
/**
 * FormDataManager.
 *
 * Copyright 2016 by Mike Barton <mjb@mjb123.uk>
 *
 * This file is part of FormDataManager.
 *
 * @package formdatamanager.
 */

class FormDataManager
{
    /**
     * @var \modX $modx
     */
    public $modx;
    /**
     * Array of configuration options, primarily paths.
     *
     * @var array
     */
    public $config = array();

    /**
     * @param \modX $modx
     * @param array $config
     */
    public function __construct(modX &$modx, array $config = array())
    {
        $this->modx =& $modx;

        $basePath = $this->modx->getOption('formdatamanager.core_path', $config, $this->modx->getOption('core_path') . 'components/formdatamanager/');
        $assetsUrl = $this->modx->getOption('formdatamanager.assets_url', $config, $this->modx->getOption('assets_url') . 'components/formdatamanager/');
        $assetsPath = $this->modx->getOption('formdatamanager.assets_path', $config, $this->modx->getOption('assets_path') . 'components/formdatamanager/');
        $managerUrl = $this->modx->getOption('manager_url', $config, $this->modx->getOption('base_url') . 'manager/');

        $this->config = array_merge(array(
            'basePath' => $basePath,
            'corePath' => $basePath,
            'modelPath' => $basePath . 'model/',
            'processorsPath' => $basePath . 'processors/',
            'elementsPath' => $basePath . 'elements/',
            'templatesPath' => $basePath . 'templates/',
            'assetsPath' => $assetsPath,
            'assetsUrl' => $assetsUrl,
            'jsUrl' => $assetsUrl . 'js/',
            'cssUrl' => $assetsUrl . 'css/',
            'connectorUrl' => $assetsUrl . 'connector.php',
            'managerUrl' => $managerUrl,
        ), $config);
        $this->modx->addPackage('formdatamanager', $this->config['modelPath']);

    }
}

