<?php

defined ('TYPO3_MODE') || die ('Access denied.');

$TCA["tx_paeproject_projectelement"] = array (
	"ctrl" => $TCA["tx_paeproject_projectelement"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,title,description,worked_days,estimated_start_date,estimated_duration,estimated_cost,real_start_date,real_duration,cost_per_day,progress,parent,dependencies,administrators,workers,documents"
	),
	"feInterface" => $TCA["tx_paeproject_projectelement"]["feInterface"],
	"columns" => array (
		't3ver_label' => array (		
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'max'  => '30',
			)
		),
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_projectelement.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"description" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_projectelement.description",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"worked_days" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_projectelement.worked_days",        
            "config" => Array (
                "type" => "select",
                "items" => Array (
                    Array("LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_projectelement.worked_days.I.0", "1"),
                    Array("LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_projectelement.worked_days.I.1", "2"),
                    Array("LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_projectelement.worked_days.I.2", "3"),
                    Array("LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_projectelement.worked_days.I.3", "4"),
                    Array("LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_projectelement.worked_days.I.4", "5"),
                    Array("LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_projectelement.worked_days.I.5", "6"),
                    Array("LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_projectelement.worked_days.I.6", "7"),
                ),
                "size" => 7,    
                "maxitems" => 7,
				"default"  => "1,2,3,4,5"
            )
        ),
		"estimated_start_date" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_projectelement.estimated_start_date",		
			"config" => Array (
				"type"     => "input",
				"size"     => "8",
				"max"      => "20",
				"eval"     => "date,required",
				"checkbox" => "0",
				"default"  => strtotime('now')
			)
		),
		"estimated_duration" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_projectelement.estimated_duration",		
			"config" => Array (
				"type"     => "input",
				"size"     => "4",
				"max"      => "8",
				"eval"     => "float,required",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "100000",
					"lower" => "0"
				),
				"default" => 1
			)
		),
		"estimated_cost" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_projectelement.estimated_cost",		
			"config" => Array (
				"type"     => "input",
				"size"     => "4",
				"max"      => "8",
				"eval"     => "float,required",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "100000",
					"lower" => "0"
				),
				"default" => 0
			)
		),
		"real_start_date" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_projectelement.real_start_date",		
			"config" => Array (
				"type"     => "input",
				"size"     => "8",
				"max"      => "20",
				"eval"     => "date,required",
				"checkbox" => "0",
				"default"  => strtotime('now')
			)
		),
		"real_duration" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_projectelement.real_duration",		
			"config" => Array (
				"type"     => "input",
				"size"     => "4",
				"max"      => "8",
				"eval"     => "float,required",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "100000",
					"lower" => "0"
				),
				"default" => 1
			)
		),
		"cost_per_day" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_projectelement.cost_per_day",		
			"config" => Array (
				"type"     => "input",
				"size"     => "4",
				"max"      => "8",
				"eval"     => "float",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "100000",
					"lower" => "0"
				),
				"default" => 0
			)
		),
		"progress" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_projectelement.progress",		
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_projectelement.progress.I.0", "0"),
					Array("LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_projectelement.progress.I.1", "10"),
					Array("LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_projectelement.progress.I.2", "20"),
					Array("LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_projectelement.progress.I.3", "30"),
					Array("LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_projectelement.progress.I.4", "40"),
					Array("LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_projectelement.progress.I.5", "50"),
					Array("LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_projectelement.progress.I.6", "60"),
					Array("LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_projectelement.progress.I.7", "70"),
					Array("LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_projectelement.progress.I.8", "80"),
					Array("LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_projectelement.progress.I.9", "90"),
					Array("LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_projectelement.progress.I.10", "100"),
				),
				"size" => 1,	
				"maxitems" => 1,
			)
		),
		"parent" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_projectelement.parent",        
            "config" => Array (
                "type" => "select",    
				"items" => Array (
                    Array("",0),
                ),
                "foreign_table" => "tx_paeproject_projectelement",    
                "foreign_table_where" => "AND tx_paeproject_projectelement.pid=###CURRENT_PID### AND tx_paeproject_projectelement.uid!=###THIS_UID### ORDER BY tx_paeproject_projectelement.uid",    
                "size" => 1,    
                "minitems" => 0,
                "maxitems" => 1,    
                "MM" => "tx_paeproject_projectelement_parent_mm",
            )
        ),
		"dependencies" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_projectelement.dependencies",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_paeproject_projectelement",	
				"foreign_table_where" => "AND tx_paeproject_projectelement.pid=###CURRENT_PID### AND tx_paeproject_projectelement.uid!=###THIS_UID### ORDER BY tx_paeproject_projectelement.uid",	
				"size" => 5,	
				"minitems" => 0,
				"maxitems" => 100,	
				"MM" => "tx_paeproject_projectelement_dependencies_mm",	
				"wizards" => Array(
					"_PADDING" => 2,
					"_VERTICAL" => 1,
					"add" => Array(
						"type" => "script",
						"title" => "Create new record",
						"icon" => "add.gif",
						"params" => Array(
							"table"=>"tx_paeproject_projectelement",
							"pid" => "###CURRENT_PID###",
							"setValue" => "prepend"
						),
						"script" => "wizard_add.php",
					),
					"list" => Array(
						"type" => "script",
						"title" => "List",
						"icon" => "list.gif",
						"params" => Array(
							"table"=>"tx_paeproject_projectelement",
							"pid" => "###CURRENT_PID###",
						),
						"script" => "wizard_list.php",
					),
					"edit" => Array(
						"type" => "popup",
						"title" => "Edit",
						"script" => "wizard_edit.php",
						"popup_onlyOpenIfSelected" => 1,
						"icon" => "edit2.gif",
						"JSopenParams" => "height=350,width=580,status=0,menubar=0,scrollbars=1",
					),
				),
			)
		),
		"administrators" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_projectelement.administrators",        
            "config" => Array (
                "type" => "group",    
                "internal_type" => "db",    
                "allowed" => "fe_users",    
                "size" => 5,    
                "minitems" => 0,
                "maxitems" => 100,    
                "MM" => "tx_paeproject_projectelement_administrators_mm",
            )
        ),
        "workers" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_projectelement.workers",        
            "config" => Array (
                "type" => "group",    
                "internal_type" => "db",    
                "allowed" => "fe_users",    
                "size" => 5,    
                "minitems" => 0,
                "maxitems" => 100,    
                "MM" => "tx_paeproject_projectelement_workers_mm",
            )
        ),
		"documents" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_projectelement.documents",        
            "config" => Array (
                "type" => "group",
                "internal_type" => "file",
                "allowed" => "",    
                "disallowed" => "php,php3",    
                "max_size" => 1000,    
                "uploadfolder" => "uploads/tx_paeproject",
                "size" => 6,    
                "minitems" => 0,
                "maxitems" => 50,
            )
        ),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2, description;;;richtext[*];3-3-3, worked_days, estimated_start_date, estimated_duration, estimated_cost, real_start_date, real_duration, cost_per_day, progress, parent, dependencies, administrators, workers,documents")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);


$TCA["tx_paeproject_exception"] = array (
	"ctrl" => $TCA["tx_paeproject_exception"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "title,disable,start_date,end_date,project_elements,affect_estimation,affect_real"
	),
	"feInterface" => $TCA["tx_paeproject_exception"]["feInterface"],
	"columns" => array (
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_exception.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"disable" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_exception.disable",		
			"config" => Array (
				"type" => "check",
			)
		),
		"start_date" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_exception.start_date",		
			"config" => Array (
				"type"     => "input",
				"size"     => "8",
				"max"      => "20",
				"eval"     => "date",
				"checkbox" => "0",
				"default"  => strtotime('now')
			)
		),
		"end_date" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_exception.end_date",		
			"config" => Array (
				"type"     => "input",
				"size"     => "8",
				"max"      => "20",
				"eval"     => "date",
				"checkbox" => "0",
				"default"  => strtotime('now')
			)
		),
		"project_elements" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_exception.project_elements",        
            "config" => Array (
                "type" => "select",    
                "foreign_table" => "tx_paeproject_projectelement",    
                "foreign_table_where" => "AND tx_paeproject_projectelement.pid=###CURRENT_PID### ORDER BY tx_paeproject_projectelement.uid",    
                "size" => 5,    
                "minitems" => 0,
                "maxitems" => 100,    
                "MM" => "tx_paeproject_exception_project_elements_mm",
            )
        ),
		"affect_estimation" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_exception.affect_estimation",		
			"config" => Array (
				"type" => "check",
				"default" => 1,
			)
		),
		"affect_real" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pae_project/locallang_db.xml:tx_paeproject_exception.affect_real",		
			"config" => Array (
				"type" => "check",
				"default" => 1,
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "title;;;;2-2-2, disable;;;;3-3-3, start_date, end_date, project_elements, affect_estimation, affect_real")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);
