<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Communauté d'agglomération du pays d'Aubagne et de l'Etoile, Mind2Machine <ace@mind2machine.com>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(PATH_tslib.'class.tslib_pibase.php');

require_once(t3lib_extMgm::extPath('pae_project','class.tx_paeproject_project.php'));
require_once(t3lib_extMgm::extPath('pae_project','class.tx_paeproject_exception.php'));
require_once(t3lib_extMgm::extPath('pae_project','date_tools.php'));
require_once(t3lib_extMgm::extPath('pae_project','form_tools.php'));
require_once(t3lib_extMgm::extPath('pae_project','project_tools.php'));

/**
 * Plugin 'PAE Project manager' for the 'pae_project' extension.
 *
 * @author	Communauté d'agglomération du pays d'Aubagne et de l'Etoile, Mind2Machine <ace@mind2machine.com>
 * @package	TYPO3
 * @subpackage	tx_paeproject
 */
class tx_paeproject_pi1 extends tslib_pibase {

	//Important extension variables
	var $prefixId      = 'tx_paeproject_pi1';		// Same as class name
	var $sessionName   = 'tx_paeproject';			// Name for browser session
	var $scriptRelPath = 'pi1/class.tx_paeproject_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'pae_project';	// The extension key.
	var $uploaddir 	   = 'uploads/tx_paeproject/';
		
	
	//custom vars
	var $lConf;
	var $session;
	var $textAreaID=0;
	
	// editing powers description for current user
	// 0 = view only
	// 1 = worker (project level)
	// 2 = local admin (project level)
	// 3 = global admin (all projects)
	var $editingRole=0;
	
	var $dateFormat = 'm.d.Y'; //default dateFormat
	
	/**
	 * initializes the flexform and all config options 
	 */
	function init(){
	 $this->pi_initPIflexForm(); // Init and get the flexform data of the plugin
	 $this->lConf = array(); // Setup our storage array...
	 // Assign the flexform data to a local variable for easier access
	 $piFlexForm = $this->cObj->data['pi_flexform'];
	 // Traverse the entire array based on the language...
	 // and assign each configuration option to $this->lConf array...
	 foreach ( $piFlexForm['data'] as $sheet => $data )
	  foreach ( $data as $lang => $value )
	   foreach ( $value as $key => $val )
		$this->lConf[$key] = $this->pi_getFFvalue($piFlexForm, $key, $sheet);
	}
	
	/**
	 * Main method of your PlugIn
	 *
	 * @param	string		$content: The content of the PlugIn
	 * @param	array		$conf: The PlugIn Configuration
	 * @return	The content that should be displayed on the website
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		
		$this->pi_initPIflexform(); // get the flexform data of the plugin
		$this->init(); //parse the flexform data and put it in $this->lConf array
		$this->pi_USER_INT_obj=1; //disable caching
		
		//HTML output
		global $htmlCode; 
		$htmlCode= array();
		
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['pae_project']);
		
		
		//initializing session
		$this->session = $GLOBALS["TSFE"]->fe_user->getKey('ses',$this->sessionName);
		
		$this->session['prefixId']=$this->prefixId;
		
		//STARTING POINTS
		//Retreiving startingpoint
	 	$startingPoints = $this->lConf['pages'];
				
		//Retreiving recursivity
		$recursive = $this->lConf['recursive'];
		if(!isset($this->lConf['recursive']) | $this->lConf['recursive']=="")$recursive = 0;
		
		//establishing list of all pids to be looked for records according to recursivity settings
		$this->session['selectedPids'] = "";
		$startingPointsArray = explode(",",$startingPoints);
		foreach($startingPointsArray as $index => $root)
		 	$this->session['selectedPids'] .= (($index == 0)?"":",").$this->pi_getPidList($this->lConf['pages'],$this->lConf['recursive']);
		
		//$htmlCode[] .= "session=".$this->session['selectedPids'];
		 
		//current language displayed in the frontend
		$this->session['current_sys_language_uid'] = $GLOBALS['TSFE']->sys_language_uid;
		
		//sys_language_uid for the default language
		$this->session['default_sys_language_uid'] = $extConf['default_sys_language_uid'];
		
		// records for the default language are stored in the database with sys_language_uid == 0
		// thus we have to correct the current_sys_language_uid value to 0 if it matches the language currently displayed
		if($this->session['current_sys_language_uid'] == $this->session['default_sys_language_uid']) $this->session['current_sys_language_uid'] = 0;
				
		//extracting the &L parameter from piVars as well
		parse_str($GLOBALS['TSFE']->linkVars);
		$this->session['L'] = $L; //now the variable named $L has been initialized by parse_str 
		
		// PLUGIN INTERNAL SETTINGS
		// Forging this player unique identifier.
		$pluginName = $this->prefixId;
		$contentUID = $this->cObj->data['uid'];	
		$this->session['pluginPageUID'] =  $GLOBALS['TSFE']->id;
		
		//ISO2 code for extension current language (eg: fr, us, es...)
		$langKey = $GLOBALS['TSFE']->tmpl->setup['config.']['language'];
		
		
		
		
		//TYPOSCRIPT CONFIGURATION PROCESSING 
		
		//verify which fields should be displayed
		$projectEnabledFields = array(
			"title" => 0, 
			"description" => 0, 
			"documents" => 0, 
			"worked_days" => 0, 
			"estimated_start_date" => 0, 
			"estimated_end_date" => 0, 
			"estimated_duration" => 0, 
			"estimated_days_project_duration" => 0, 
			"estimated_cost_per_day" => 0, 
			"estimated_cost" => 0, 
			"real_start_date" => 0, 
			"real_end_date" => 0, 
			"real_duration" => 0, 
			"cost_per_day" => 0, 
			"real_cost" => 0, 
			"real_cost_per_day" => 0, 
			"computed_estimated_data" => 0, 
			"computed_real_data" => 0, 
			"real_days_project_duration" => 0, 
			"progress" => 0, 
			"parent" => 0, 
			"children" => 0, 
			"dependencies" => 0, 
			"exceptions" => 0, 
			"administrators" => 0, 
			"workers" => 0
		);
			
		if(isset($this->conf['projectDisplayFields'])){
			$ts_fields = explode(",", trim($this->conf['projectDisplayFields']));
			foreach($ts_fields as $field => $value){
				$projectEnabledFields[trim($ts_fields[$field])] = 1;
			}
			
		};
		
		$defaultValues = array(
			"title" =>  $this->conf['defaultValues.']['title'],
			"description" =>  $this->conf['defaultValues.']['description'],
			"worked_days" =>  trim($this->conf['defaultValues.']['worked_days']),
			"estimated_start_date" =>  $this->conf['defaultValues.']['estimated_start_date'],
			"estimated_duration" =>  intval(trim($this->conf['defaultValues.']['estimated_duration'])),
			"estimated_cost" =>  intval(trim($this->conf['defaultValues.']['estimated_cost'])),
			"estimated_cost_per_day" =>  intval(trim($this->conf['defaultValues.']['estimated_cost_per_day'])),
			"real_start_date" =>  $this->conf['defaultValues.']['real_start_date'],
			"real_duration" =>  intval(trim($this->conf['defaultValues.']['real_duration'])),
			"cost_per_day" =>  intval(trim($this->conf['defaultValues.']['cost_per_day'])),
			"progress" =>  intval(trim($this->conf['defaultValues.']['progress'])),
			"parent" =>  intval(trim($this->conf['defaultValues.']['parent'])),  
			"dependencies" =>  trim($this->conf['defaultValues.']['dependencies']),  
			"exceptions" =>  trim($this->conf['defaultValues.']['exceptions']),  
			"administrators" =>  trim($this->conf['defaultValues.']['administrators']), 
			"workers" =>  trim($this->conf['defaultValues.']['workers'])  
		);
		
		$adminButtonsOverride = array(
			"listAllProjects" =>  $this->conf['adminButtonsOverride.']['listAllProjects'],
			"createProject" =>  $this->conf['adminButtonsOverride.']['createProject'],
			"listAllExceptions" =>  $this->conf['adminButtonsOverride.']['listAllExceptions'],
			"createException" =>  $this->conf['adminButtonsOverride.']['createException']
		);
		
		$editButtonsOverride = array(
			"view" =>  $this->conf['editButtonsOverride.']['view'],
			"edit" =>  $this->conf['editButtonsOverride.']['edit'],
			"delete" =>  $this->conf['editButtonsOverride.']['delete'],
		);
		
		$useExceptions = intval($this->conf['useExceptions']);
		
		$cssStylesheet = trim($this->conf['cssStylesheet']);
		
		
		$stylesheetPath = ($cssStylesheet != "")?$cssStylesheet : t3lib_extMgm::siteRelPath($this->extKey).'res/'.$this->extKey.'.css';
		
		//Javascripts & stylesheets
		$GLOBALS['TSFE']->additionalHeaderData[$prefixId ] ='
		<link rel="stylesheet" href="'.$stylesheetPath.'" type="text/css" media="screen" />
		<script src="'.t3lib_extMgm::siteRelPath($this->extKey).'res/swfobject.js"></script>
		<script type="text/javascript">
		   _editor_url = "'.t3lib_extMgm::siteRelPath($this->extKey).'res/htmlarea/";
		   _editor_lang = "'.$langKey.'";
		</script>
		<script type="text/javascript" src="'.t3lib_extMgm::siteRelPath($this->extKey).'res/htmlarea/htmlarea.js"></script>
		';
		
		//print_r($GLOBALS['TSFE']); die();
		
		//URL parameters 
		$action = 			($this->piVars['action']) ? 	$this->piVars['action']:'default';
		$record_uid = 		($this->piVars['uid']) ? 		intval($this->piVars['uid']):'';
		//$book_uid = 		(t3lib_div::_GP('book_uid')) ? 	intval(t3lib_div::_GP('book_uid')):'';
		
		//Define the flash file to be used
		$swfPath = t3lib_extMgm::siteRelPath($this->extKey)."res/gantt.swf";
					
		//relative path to the images related to the website root directory
		$imgSiteRelPath = t3lib_extMgm::siteRelPath($this->extKey)."res/img/";
		
		//Dates
		$this->dateFormat = $extConf['dateFormat'];
		$oneDayTimestampValue=86400;
		$defaultWorkedDays='1,2,3,4,5';
		
		$flashCode = array();
		
		// Flash code
		$flashCode[] = '
			<!-- PROJECTS GANTT DISPLAY -->
			<div id="pae_flashcontent_container">
			<div id="pae_flashcontent">';
		//$htmlCode = array_merge($htmlCode,$this->listCategoriesAsHTML());
		$flashCode[] = '
			</div></div>
		
			<script type="text/javascript">
				// <![CDATA[
				
				var so = new SWFObject("'.$swfPath .'", "gantt", "670", "300", "8", "#FFFFFF");';
								
				$flashCode[] = '				so.addVariable("pluginPageUID", "'.$this->session['pluginPageUID'].'");';
				$flashCode[] = '				so.addVariable("xmlType", "'.$extConf['typeNum'].'");';
				$flashCode[] = '				so.addVariable("extPath", "'.t3lib_extMgm::siteRelPath($this->extKey).'");';
				$flashCode[] = '				so.addVariable("langKey", "'.$langKey.'");';
				
				$flashCode[] = '				so.addVariable("selectedPids", "'.$this->session['selectedPids'].'");';
				$flashCode[] = '				so.addVariable("current_sys_language_uid", "'.$this->session['current_sys_language_uid'].'");';
				$flashCode[] = '				so.addVariable("default_sys_language_uid", "'.$this->session['default_sys_language_uid'].'");';
				$flashCode[] = '				so.addVariable("L", "'.$this->session['L'].'");';
				$flashCode[] = '				so.addVariable("prefixId", "'.$this->prefixId.'");';
				$flashCode[]  = '				so.addParam("wmode", "transparent");';
				//$htmlCode[] = 'so.addParam("'.$name.'", "'.$value.'");';
				
				$flashCode[] = '
				so.write("pae_flashcontent");
				
				// ]]>
			</script>
			<!-- END PROJECTS GANTT DISPLAY -->';
		
		
		
		
		
		// retreive user editing rights 
		
					
		
		if( isset($startingPointsArray) && sizeof($startingPointsArray)>0 && trim($startingPointsArray[0])!=""){
		
			//retreive global administrators list from plugin configuration list
			$globalAdministrators = explode(',',$this->lConf['administrators']);	
			
			if(isset($globalAdministrators) && sizeof($globalAdministrators)>0 && trim($globalAdministrators[0])!=""){
				if(in_array($GLOBALS['TSFE']->fe_user->user['uid'], $globalAdministrators)){
					$this->editingRole = 3; 
				}
			}
			else {
				//if no global admin is defined, display warning
				$htmlCode[] = '<p><strong>'.$this->pi_getLL('adminWarning').'</strong></p>';
			}
			
			
			//allow project listing for everybody
			$htmlCode[] = "<div class='navButtons'>";
			//List all projects link
			$urlParameters = array(
			); 
			$viewLink = $this->pi_getPageLink($this->session['pluginPageUID'],'',$urlParameters) ;
			
			if(trim($adminButtonsOverride["listAllProjects"])==""){
				$htmlCode[] = (($authorIndex>0)?', ':'').'<a href="'.$viewLink.'" class="adminBtnLink">'.$this->pi_getLL('listAllProjects').'</a>';
			}
			else{
				$htmlCode[] = (($authorIndex>0)?', ':'').'<a href="'.$viewLink.'" class="adminBtnLink">'.$adminButtonsOverride["listAllProjects"].'</a>';
			}
				
				
				
			
			//if current user is super administrator, display all creation links
			if($this->editingRole == 3){
				
				
			
				//create project link
				$urlParameters = array(
					$this->prefixId.'[action]' => 'create_project'
				); 
				$viewLink = $this->pi_getPageLink($this->session['pluginPageUID'],'',$urlParameters) ;
				
				if(trim($adminButtonsOverride["createProject"])==""){
					$htmlCode[] = (($authorIndex>0)?', ':'').'<a href="'.$viewLink.'" class="adminBtnLink">'.$this->pi_getLL('createProject').'</a>';
				}
				else{
					$htmlCode[] = (($authorIndex>0)?', ':'').'<a href="'.$viewLink.'" class="adminBtnLink">'.$adminButtonsOverride["createProject"].'</a>';
				}
				
				if($useExceptions){
					//List all exceptions link
					$urlParameters = array(
						$this->prefixId.'[action]' => 'list_exceptions'
					); 
					$viewLink = $this->pi_getPageLink($this->session['pluginPageUID'],'',$urlParameters) ;
					
					if(trim($adminButtonsOverride["listAllExceptions"])==""){
						$htmlCode[] = (($authorIndex>0)?', ':'').'<a href="'.$viewLink.'" class="adminBtnLink">'.$this->pi_getLL('listAllExceptions').'</a>';
					}
					else{
						$htmlCode[] = (($authorIndex>0)?', ':'').'<a href="'.$viewLink.'" class="adminBtnLink">'.$adminButtonsOverride["listAllExceptions"].'</a>';
					}
					
					//create exception link
					$urlParameters = array(
						$this->prefixId.'[action]' => 'create_exception'
					); 
					$viewLink = $this->pi_getPageLink($this->session['pluginPageUID'],'',$urlParameters) ;
					
					if(trim($adminButtonsOverride["createException"])==""){
						$htmlCode[] = (($authorIndex>0)?', ':'').'<a href="'.$viewLink.'" class="adminBtnLink">'.$this->pi_getLL('createException').'</a>';
					}
					else{
						$htmlCode[] = (($authorIndex>0)?', ':'').'<a href="'.$viewLink.'" class="adminBtnLink">'.$adminButtonsOverride["createException"].'</a>';
					}
				}
			
			}
			$htmlCode[] = "</div>";
			
		
			if( ($action == "move_up")){
			
				//$this->session['selectedPids']
				$project = t3lib_div::makeInstance('tx_paeproject_project');
				
				$project->init($startingPointsArray[0], $GLOBALS['TSFE']->fe_user->user['uid']);
				$project->load($record_uid);
				$project->moveUp();
				
				$action = "default"; //listing projects
				
			}
			else if( ($action == "move_down")){
			
				//$this->session['selectedPids']
				$project = t3lib_div::makeInstance('tx_paeproject_project');
				
				$project->init($startingPointsArray[0], $GLOBALS['TSFE']->fe_user->user['uid']);
				$project->load($record_uid);
				$project->moveDown();
				
				$action = "default"; //listing projects
			}
			else if( ($action == "delete_project")){
			
				//$this->session['selectedPids']
				$project = t3lib_div::makeInstance('tx_paeproject_project');
				
				$project->init($startingPointsArray[0], $GLOBALS['TSFE']->fe_user->user['uid']);
				$project->load($record_uid);
				$project->delete();
				
				$htmlCode[] = '<div class="notify">'.$this->pi_getLL('projectDeleted').'</div>';
				
				$action = "default"; //listing projects
				
			}
			if( ($action == "move_up_exception")){
			
				//$this->session['selectedPids']
				$exception = t3lib_div::makeInstance('tx_paeproject_exception');
				
				$exception->init($startingPointsArray[0], $GLOBALS['TSFE']->fe_user->user['uid']);
				$exception->load($record_uid);
				$exception->moveUp();
				
				$action = "list_exceptions"; //listing exceptions
				
			}
			else if( ($action == "move_down_exception")){
			
				//$this->session['selectedPids']
				$exception = t3lib_div::makeInstance('tx_paeproject_exception');
				
				$exception->init($startingPointsArray[0], $GLOBALS['TSFE']->fe_user->user['uid']);
				$exception->load($record_uid);
				$exception->moveDown();
				
				$action = "list_exceptions"; //listing exceptions
				
			}
			else if( ($action == "delete_exception")){
			
				//$this->session['selectedPids']
				$exception = t3lib_div::makeInstance('tx_paeproject_exception');
				
				$exception->init($startingPointsArray[0], $GLOBALS['TSFE']->fe_user->user['uid']);
				$exception->load($record_uid);
				$exception->delete();
				
				$htmlCode[] = '<div class="notify">'.$this->pi_getLL('exceptionDeleted').'</div>';
				
				$action = "list_exceptions"; //listing exceptions
				
			}
			
			if( ($action == "submit-project")){
			
				include 'submit_project.php';
				
				$htmlCode[] = '<div class="notify">'.$this->pi_getLL('projectSaveSuccess').'</div>';
				
				$action = "view_project";
			
			}
			
			if( ($action == "uploadDoc")){
			
				include 'submit_project.php';
				$htmlCode[] = '<div class="notify">'.$this->pi_getLL('documentSaveSuccess').'</div>';		
			
			}
			
			if(($action == "deleteDoc")){
			
				include 'submit_project.php';
				$htmlCode[] = '<div class="notify">'.$this->pi_getLL('documentDelSuccess').'</div>';		
			
			}
			
			if( ($action == "submit-exception")){
			
				include 'submit_exception.php';
				
				$htmlCode[] = '<div class="notify">'.$this->pi_getLL('exceptionSaveSuccess').'</div>';
				
				$action = "view_exception";
			
			}
			
			if( ($action == "view_project")){
			
				include 'browse_project.php';
			
			}
			else if ( ($action == "create_project")){
			
				include 'browse_project.php';
			
			}
			else if( ($action == "edit_project")){
			
				include 'browse_project.php';
			
			}
			else if( ($action == "list_exceptions")){
			
				$htmlCode[] = '<div class="project-list">';
				$htmlCode = array_merge($htmlCode, listAllExceptions($this, $editButtonsOverride));	
				$htmlCode[] = '</div>';
			}
			else if( ($action == "view_exception")){
			
				include 'browse_exception.php';
			
			}
			else if( ($action == "edit_exception")){
			
				include 'browse_exception.php';
			
			}
			else if ( ($action == "create_exception")){
			
				include 'browse_exception.php';
			
			}
			
			if( ($action == "default") ||($action == "list_projects") )
			{
				$htmlCode[] = '<div class="project-list">';
				$htmlCode = array_merge($htmlCode, listAllProjects(NULL, $this, $editButtonsOverride));	
				$htmlCode[] = '</div>';
			}
		
		}
		else {
			//if no starting point is defined, display warning
			$htmlCode[] = '<strong>'.$this->pi_getLL('startingPointWarning').'</strong>';
		
		}
		
		if( $action != "default" ){
			//do not display graph in edit mode if option is set
			if($this->lConf['displayEdit'] == "graphListOnly" || $this->lConf['displayEdit'] == "graphNever"){
				$flashCode = array(); //empty flash array
			}
		}
		
		
		//never display graph if option is set
		if($this->lConf['displayEdit'] == "graphNever"){
			$flashCode = array(); //empty flash array
		}
		
		
		$htmlOutput = array();
		//assembling Flash and HTML code according to display order setting
		if($this->lConf['displayOrder'] == "graphFirst"){
			$htmlOutput = $flashCode;
			$htmlOutput = array_merge($htmlOutput, $htmlCode);
		}
		else{
			$htmlOutput = $htmlCode;
			$htmlOutput = array_merge($htmlOutput,$flashCode);
		}
		
		
		//saving session data
		
		/*echo "<br>saving session=".$this->session['globalAction']."<br>";
		echo "action=".$action."<br>";
		echo "sessioname=".$this->sessionName."<br>";
		print_r($this->session);*/
		
		
		$GLOBALS["TSFE"]->fe_user->setKey('ses',$this->sessionName,$this->session);
		$GLOBALS["TSFE"]->storeSessionData();

		return $this->pi_wrapInBaseClass(implode(chr(10),$htmlOutput));
	}
	
	
	
	
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pae_project/pi1/class.tx_paeproject_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pae_project/pi1/class.tx_paeproject_pi1.php']);
}

?>