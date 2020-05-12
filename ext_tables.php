<?php

defined ('TYPO3_MODE') || die ('Access denied.');

$TCA["tx_paeproject_projectelement"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_projectelement',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => TRUE, 
		'origUid' => 't3_origuid',
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY) . 'icon_tx_paeproject_projectelement.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, title, description, estimated_start_date, estimated_duration, estimated_cost, real_start_date, real_duration, cost_per_day, progress, parent, dependencies, administrators, editors, readers",
	)
);

$TCA["tx_paeproject_exception"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_exception',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY) . 'icon_tx_paeproject_exception.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "title, disable, start_date, end_date, project_elements, affect_estimation, affect_real",
	)
);

t3lib_div::loadTCA('tt_content');

$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1'] = 'layout,select_key,pages';
// you add pi_flexform to be renderd when your plugin is shown
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1'] = 'pi_flexform';          // new!

t3lib_extMgm::addPlugin(array(
    'LLL:EXT:pae_project/locallang_db.xml:tt_content.list_type_pi1',
    $_EXTKEY . '_pi1'),
    'list_type'
);

t3lib_extMgm::addStaticFile($_EXTKEY, 'pi1/static/', 'PAE Project manager');

// NOTE: Be sure to change sampleflex to the correct directory name of your extension!             // new!
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:pae_project/flexform_ds_pi1.xml');     // new!

if (TYPO3_MODE=="BE") {
    $TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_paeproject_pi1_wizicon"] =
        t3lib_extMgm::extPath($_EXTKEY) . 'pi1/class.tx_paeproject_pi1_wizicon.php';
}
