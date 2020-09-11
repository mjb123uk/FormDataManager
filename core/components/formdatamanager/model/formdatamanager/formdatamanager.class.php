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
		$this->namespace = $this->modx->getOption('namespace', $options, $this->namespace);

        $basePath = $this->modx->getOption('formdatamanager.core_path', $config, $this->modx->getOption('core_path') . 'components/formdatamanager/');
        $assetsUrl = $this->modx->getOption('formdatamanager.assets_url', $config, $this->modx->getOption('assets_url') . 'components/formdatamanager/');
        $assetsPath = $this->modx->getOption('formdatamanager.assets_path', $config, $this->modx->getOption('assets_path') . 'components/formdatamanager/');
        $managerUrl = $this->modx->getOption('manager_url', $config, $this->modx->getOption('base_url') . 'manager/');

        $this->config = array_merge(array(
			'namespace' => $this->namespace,
            'version' => $this->version,
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
	
	public function CreateZip($folder="",$remove=1) {
		
		//$this->modx->log( modX::LOG_LEVEL_ERROR, 'Starting fdmExport Zip');
		$p = $this->modx->getOption('fdm_export_folder_path', null, '');
		if (empty($p)) $p = $this->modx->getOption('core_path', null, MODX_CORE_PATH).'export/FormDataManager/';
		
		if (empty($folder)) $folder = date('Ymd',time());
		
		$d = "";
		$dir = opendir($p);
		while ($file = readdir($dir))
		{
			if ($file == '.' || $file == '..') continue;
			if ( (is_dir($p.$file)) && (substr($file,0,strlen($folder)) == $folder) ) {
				$d = $file;
				break;
			}
		}
		if (!empty($d)) {
			$z = $this->_createzip($p,$d);
			// if wanted remove folder if succesful zip
			if ( ($remove) && ($z > 0) ) {
				$fp = $p.$d;
				array_map('unlink', glob("$fp/*.*"));
				rmdir($fp);
			}
			
		}
	}
		
	private function _createzip($path,$dir) {
		
		// Get real path for our folder
		$rootPath = realpath($path.$dir);

		// Initialize archive object
		$zip = new ZipArchive();
		$zip->open($path.$dir.'.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

		// Create recursive directory iterator
		/** @var SplFileInfo[] $files */
		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($rootPath),
			RecursiveIteratorIterator::LEAVES_ONLY
		);
		
		$zi = 0;
		foreach ($files as $name => $file)
		{
			// Skip directories (they would be added automatically)
			if (!$file->isDir())
			{
				// Get real and relative path for current file
				$filePath = $file->getRealPath();
				$relativePath = substr($filePath, strlen($rootPath) + 1);

				// Add current file to archive
				$zip->addFile($filePath, $relativePath);
				$zi++;
			}
		}

		// Zip archive will be created only after closing object
		$zip->close();
		return $zi;
	}
	
	public function CreateRar() {
		
		//$this->modx->log( modX::LOG_LEVEL_ERROR, 'Starting fdmExport Rar');
		
	}
	
	public function getBEPath() {
		
		// Get the path for Bulk Exports
		$p = $this->modx->getOption('fdm_export_folder_path', null, '');
		if (empty($p)) $p = $this->modx->getOption('core_path', null, MODX_CORE_PATH).'export/FormDataManager/';
		
		return $p;
		
	}
}

