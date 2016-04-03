<?php
$xpdo_meta_map['FdmLayouts']= array (
  'package' => 'formdatamanager',
  'version' => '1.1',
  'table' => 'fdm_layouts',
  'extends' => 'xPDOSimpleObject',
  'fields' => 
  array (
    'formtype' => NULL,
    'formid' => NULL,
    'formfld_data' => NULL,
    'formfld_extra' => NULL,
    'createdon' => NULL,
    'createdby' => 0,
    'editedon' => NULL,
    'editedby' => 0,
	'lastexportfrom' => NULL,
    'lastexportto' => NULL,
    'lastautoexpfrom' => NULL, 
    'lastautoexpto' => NULL, 
  ),
  'fieldMeta' => 
  array (
    'formtype' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '20',
      'phptype' => 'string',
      'null' => false,
      'index' => 'index',
    ),
    'formid' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
    ),
    'formfld_data' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => true,
    ),
    'formfld_extra' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => true,
    ),
    'createdon' => 
    array (
      'dbtype' => 'datetime',
      'phptype' => 'datetime',
      'null' => true,
    ),
    'createdby' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'editedon' => 
    array (
      'dbtype' => 'datetime',
      'phptype' => 'datetime',
      'null' => true,
    ),
    'editedby' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'lastexportfrom' => 
    array (
      'dbtype' => 'datetime',
      'phptype' => 'datetime',
      'null' => true,
    ),
    'lastexportto' => 
    array (
      'dbtype' => 'datetime',
      'phptype' => 'datetime',
      'null' => true,
    ),
    'lastautoexpfrom' => 
    array (
      'dbtype' => 'datetime',
      'phptype' => 'datetime',
      'null' => true,
    ),
    'lastautoexpto' => 
    array (
      'dbtype' => 'datetime',
      'phptype' => 'datetime',
      'null' => true,
    ),
  ),
  'indexes' => 
  array (
    'formtype' => 
    array (
      'alias' => 'formtype',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'formtype' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
        'formid' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
  ),
);
