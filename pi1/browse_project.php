<?php

		/*echo "<br>browse record_uid=".$record_uid."<br>";
		echo "this->session['globalAction']=".$this->session['globalAction']."<br>";
		echo "action=".$action."<br>";*/
		
		
		
		if( ($action != "uploadDoc") & ($action != "deleteDoc") & ($action != "submit-project")){
			// record global action
			// globalAction describes the current process we're in, even if action has been switched to a different order
			// such as uploadDoc
			$this->session['globalAction'] = $action;
		}
		//create project
		if ( ($action == "create_project") & ($this->editingRole == 3) & ($record_uid=="")){
			$project = t3lib_div::makeInstance('tx_paeproject_project');
			$project->init($startingPointsArray[0],$GLOBALS['TSFE']->fe_user->user['uid']);
			$availableProjects = listOtherProjects($startingPointsArray[0],-1);
		}
		else if ( ($action == "create_project") & ($this->editingRole < 3)){
			//attempt to force project creation by forged url
			//switch to view_mode with no creation possibility
			$project = t3lib_div::makeInstance('tx_paeproject_project');
			$project->init($startingPointsArray[0],$GLOBALS['TSFE']->fe_user->user['uid']);
			$action = "view_project";
			$availableProjects = listOtherProjects($project->data['pid'],$project->data['uid']);
		}
		//view or edit project
		else{	
			//retreiving project info
			$project = t3lib_div::makeInstance('tx_paeproject_project');
			$project->load($record_uid);
			$availableProjects = listOtherProjects($project->data['pid'],$project->data['uid']);
			
			//if the project has children
			//compile available information from children
			if($project->hasChildren()){
				$statistics = computeProjectData(array($project));
			}
			
			if($this->editingRole < 3) //if current user is not know as a global administrator
			{
				if($GLOBALS['TSFE']->loginUser){
					//retreive his local rights
					$this->editingRole = $project->isEditingEnabled($GLOBALS['TSFE']->fe_user->user['uid']);
				}			
			}
		}
		
		
		
		$exceptions 	= $project->getExceptions();
		$dependencies	= $project->getDependencies();
		$parent			= $project->getParent();
		$children		= $project->getChildren();
		$administrators = $project->getAdministrators();
		$workers 		= $project->getWorkers();
		$documents 		= explode(",",$project->data['documents']);
		
		
		
		//Mapping worked days for estimated and real data
		
				
		$estimatedMap = mapWorkedDays(
				$project->data['estimated_start_date'], 
				$project->data['estimated_duration'], 
				$project->data['worked_days'], 
				$exceptions, 
				0);	
		
		$realMap = mapWorkedDays(
				$project->data['real_start_date'], 
				$project->data['real_duration'], 
				$project->data['worked_days'], 
				$exceptions, 
				1);
		
		
		
		if($this->editingRole < 3) //if current user is not know as a global administrator
		{
			if($GLOBALS['TSFE']->loginUser){
				//retreive his local rights
				$this->editingRole = $project->isEditingEnabled($GLOBALS['TSFE']->fe_user->user['uid']);
			}			
		}
			
		
		//displaying project
		$htmlCode[] = '<div class="project-display">';
		
		$htmlCode[] = '<form name="projectEdit" enctype="multipart/form-data" action="'.$this->pi_getPageLink($GLOBALS['TSFE']->id).'" method="POST">'; 
		
		$htmlCode[] = '<input type="hidden" name="'.$this->prefixId.'[action]" value="submit-project">';
		$htmlCode[] = '<input type="hidden" name="'.$this->prefixId.'[uid]" value="'.$project->data['uid'].'">';
		
		
		$htmlCode[] = '<div class="project-header">';
		
		$htmlCode[] = '<h2>'.$project->data['title'].'</h2>'; //$this->pi_getLL('project')." ".
		
		//show edit project link in view mode for enabled users
		if( ($action == "view_project") & $this->editingRole >0){
			if( $project->isEditingEnabled($GLOBALS['TSFE']->fe_user->user['uid']) | ($this->editingRole == 3) ){
				$urlParameters = array(
					$this->prefixId.'[action]' => 'edit_project',
					$this->prefixId.'[uid]' => $project->data['uid'],
				); 
				$link = $this->pi_getPageLink($this->session['pluginPageUID'],'',$urlParameters) ;
				
				if(trim($editButtonsOverride["edit"])==""){
					$htmlCode[] = ' <a href="'.$link.'" class="btnLink">'.$this->pi_getLL('edit').'</a>';			
				}
				else{
					$htmlCode[] = ' <a href="'.$link.'" class="btnLink">'.$editButtonsOverride["edit"].'</a>';	
				}
				
				
			}
		}
		else if( ($action == "edit_project") & $this->editingRole >0){
			if( $project->isEditingEnabled($GLOBALS['TSFE']->fe_user->user['uid']) | ($this->editingRole == 3) ){
				$urlParameters = array(
					$this->prefixId.'[action]' => 'view_project',
					$this->prefixId.'[uid]' => $project->data['uid'],
				); 
				$link = $this->pi_getPageLink($this->session['pluginPageUID'],'',$urlParameters) ;
				
				if(trim($editButtonsOverride["view"])==""){
					$htmlCode[] = ' <a href="'.$link.'" class="btnLink">'.$this->pi_getLL('view').'</a>';			
				}
				else{
					$htmlCode[] = ' <a href="'.$link.'" class="btnLink">'.$editButtonsOverride["view"].'</a>';	
				}
				
			}
		}
		
		$htmlCode[] = '<br/><br/>';
		
		
		//title
		//edit for admins only
		($projectEnabledFields['title'] == 0)?$showclass="hide":$showclass="show-margin-below";  //visibility TS control
		$htmlCode[] = '<div class="'.$showclass.'">';
		
			if( (($action == "edit_project")|($action == "create_project")) & $this->editingRole >=2){	
				if($action == "create_project"){
					$data = $defaultValues["title"];
				}else {
					$data = $project->data['title'];
				}
				//title
				$htmlCode[] = writeInput($this,'title','title', $data, 'inputLong');
			}
			
		$htmlCode[] = '</div>';
		
		
		//description
		//edit for admins only
		($projectEnabledFields['description'] == 0)?$showclass="hide":$showclass="show-margin-below";  //visibility TS control
		$htmlCode[] = '<div class="'.$showclass.'">';
		
			if( (($action == "edit_project")|($action == "create_project")) & $this->editingRole >=2){
				if($action == "create_project"){
					$data = $defaultValues["description"];
				}else {
					$data = $project->data['description'];
				}
				$htmlCode[] = writeRTE($this, 'description','description', "projectEdit", $data);
			}
			//show for others
			else {
				$htmlCode[] = '<p class="bodytext">'.$project->data['description'].'</p>';
			}
			
		$htmlCode[] = '</div>';
		
		
		//documents
		//edit for admins and workers
		($projectEnabledFields['documents'] == 0)?$showclass="hide":$showclass="show-margin-below";  //visibility TS control
		$htmlCode[] = '<div class="'.$showclass.'">';
		
			if( (($action == "edit_project")|($action == "create_project")) & $this->editingRole >=1){
				$htmlCode[] = writeDocumentList($this, 'documents','documents', $documents, 'docMulti', "projectEdit");	
			}
			//show for others
			else {
				
				$htmlCode[] = '
				<p><label for="documents">'.$this->pi_getLL('documents').'</label>';
							
				$htmlCode[] = '<table class="">';
				foreach($documents as $key=>$document){
					//link to document
					$htmlCode[] = '<tr><td class="linkLocal"><a href="'.$this->uploaddir.$document.'" target="_blank">'.stripslashes($document).'</a></td></tr>';
				}
				$htmlCode[] = '</table';
				$htmlCode[] = '</p>';
			}
			
		$htmlCode[] = '</div>';
		
		
		//Editing / browsing of numerical values are allowed if the project has no children
		if(!$project->hasChildren()){
			
			//worked days
			//edit for admins only
			($projectEnabledFields['worked_days'] == 0)?$showclass="hide":$showclass="show-margin-below";  //visibility TS control
			$htmlCode[] = '<div class="'.$showclass.'">';
		
				if( (($action == "edit_project")|($action == "create_project")) & $this->editingRole >=2){
					
					if($action == "create_project"){
						//new project record
						$selectValues 	= explode(",", $defaultValues["worked_days"]);
						$selectLabels 	= listWorkedDays($this, $defaultValues["worked_days"]);
					}
					else{
						//existing project record
						$selectValues 	= explode(',',$project->data['worked_days']);
						$selectLabels 	= listWorkedDays($this, $project->data['worked_days']);
					}
					
					$allValues 	= 	array(1,2,3,4,5,6,7);
					$allLabels 	= listWorkedDays($this, "1,2,3,4,5,6,7");
					
					$htmlCode[] = writeDoubleList( $this, 'worked_days','worked_days', $selectValues, $selectLabels, $allValues, $allLabels, 'docDouble', "projectEdit",7);		
					$htmlCode[] = "<br />";
				}
				//show for others
				else {
					$htmlCode[] = '<p><label for="worked_days">'.$this->pi_getLL('worked_days').'</label>'.implode(', ',listWorkedDays($this,$project->data['worked_days'])).'</p>';			
				}
			
			$htmlCode[] = '</div>';
		
			
			
			
			
			
			
			
			
			//ESTIMATIONS
			($projectEnabledFields['estimated_data'] == 0)?$showclass="hide":$showclass="show-margin-below";  //visibility TS control
			$htmlCode[] = '<div class="'.$showclass.'"><div class="estimated">';
				
			
			//estimated start date
			//edit for admins only
			($projectEnabledFields['estimated_start_date'] == 0)?$showclass="hide":$showclass="show";  //visibility TS control
			$htmlCode[] = '<div class="'.$showclass.'">';
		
				if( (($action == "edit_project")|($action == "create_project")) & $this->editingRole >=2){
					
					if($action == "create_project"){
						$data = strtotime($defaultValues["estimated_start_date"]);
					}else {
						$data = $project->data['estimated_start_date'];
					}
					
					$htmlCode[] = writeDate($this,'estimated_start_date','estimated_start_date', $data);
				}
				//show for others
				else {
					$htmlCode[] = '<p><label for="estimated_start_date">'.$this->pi_getLL('estimated_start_date').'</label>'.date($this->dateFormat,$project->data['estimated_start_date']).'</p>';			
				}
				
			$htmlCode[] = '</div>';
			
			
			//estimated Duration
			//edit for admins only
			($projectEnabledFields['estimated_duration'] == 0)?$showclass="hide":$showclass="show";  //visibility TS control
			$htmlCode[] = '<div class="'.$showclass.'">';
		
				if( (($action == "edit_project")|($action == "create_project")) & $this->editingRole >=2){
					
					if($action == "create_project"){
						$data = $defaultValues["estimated_duration"];
					}else {
						$data = $project->data['estimated_duration'];
					}
					
					$htmlCode[] = writeInput($this,'estimated_duration','estimated_duration', $data, 'inputTiny');
				}
				//show for others
				else {
					$htmlCode[] = '<p><label for="estimated_duration">'.$this->pi_getLL('estimated_duration').'</label>'.$project->data['estimated_duration'].'</p>	';
				}
				
			$htmlCode[] = '</div>';
			
			
			
			//estimated end date
			//show "auto" in edit mode
			($projectEnabledFields['estimated_end_date'] == 0)?$showclass="hide":$showclass="show";  //visibility TS control
			$htmlCode[] = '<div class="'.$showclass.'">';
				
				if( (($action == "edit_project")|($action == "create_project")) & $this->editingRole >0){
					
					$htmlCode[] = '<p><label for="estimated_end_date">'.$this->pi_getLL('estimated_end_date').'</label>'.$this->pi_getLL('auto').'</p>';			
				}
				//show computed value in view mode
				else {
					$htmlCode[] = '<p><label for="estimated_end_date">'.$this->pi_getLL('estimated_end_date').'</label>'.date($this->dateFormat,getEndDate($estimatedMap)).'</p>';		
				}
				
			$htmlCode[] = '</div>';
			
			
			
			//estimated Cost
			//edit for admins only
			($projectEnabledFields['estimated_cost'] == 0)?$showclass="hide":$showclass="show";  //visibility TS control
			$htmlCode[] = '<div class="'.$showclass.'">';
			
				if( (($action == "edit_project")|($action == "create_project")) & $this->editingRole >=2){
					
					if($action == "create_project"){
						$data = $defaultValues["estimated_cost"];
					}else {
						$data = $project->data['estimated_cost'];
					}
					
					$htmlCode[] = writeInput($this,'estimated_cost','estimated_cost', $data, 'inputTinyPlus');
				}
				//show for others
				else {
					
					$htmlCode[] = '<p><label for="estimated_cost">'.$this->pi_getLL('estimated_cost').'</label>'.$project->data['estimated_cost'].'</p>';
					
				}
				
			$htmlCode[] = '</div>';
			
			$htmlCode[] = '</div>';
		}
		else{
			if( (($action == "edit_project")|($action == "create_project")) & $this->editingRole >0){
				//if the project has children, tell users the data is aggregated from children
				$htmlCode[] = '<br><div class="real"><strong>'.$this->pi_getLL('childrenWarning').'</strong></div><br>';
			}
		}
		
		
		
		//COMPUTED ESTIMATED DATA
		
		//if the project has children
		//compile available information from children
		if($project->hasChildren()){
		
			($projectEnabledFields['computed_estimated_data'] == 0)?$showclass="hide":$showclass="show-margin-below";  //visibility TS control
			$htmlCode[] = '<div class="'.$showclass.'"><div class="computed">';
			
						
			$htmlCode[] = '<p><strong>'.$this->pi_getLL('statistics_from_subprojects').'</strong></p>';			
			
			
			//Computed estimated start date
			
			($projectEnabledFields['estimated_start_date'] == 0)?$showclass="hide":$showclass="show";  //visibility TS control
			$htmlCode[] = '<div class="'.$showclass.'">';
			$htmlCode[] = '<p><label>'.$this->pi_getLL('estimated_start_date').'</label>'.date($this->dateFormat,$statistics['computedEstimatedStartDate']).'</p>';			
			$htmlCode[] = '</div>';
			
			//Computed estimated end date
			($projectEnabledFields['estimated_end_date'] == 0)?$showclass="hide":$showclass="show";  //visibility TS control
			$htmlCode[] = '<div class="'.$showclass.'">';
			$htmlCode[] = '<p><label>'.$this->pi_getLL('estimated_end_date').'</label>'.date($this->dateFormat,$statistics['computedEstimatedEndDate']).'</p>';			
			$htmlCode[] = '</div>';
			
			//Computed estimated Duration
			
			($projectEnabledFields['estimated_duration'] == 0)?$showclass="hide":$showclass="show";  //visibility TS control
			$htmlCode[] = '<div class="'.$showclass.'">';
			$htmlCode[] = '<p><label>'.$this->pi_getLL('estimated_duration').'</label>'.$statistics['totalEstimatedWorkedDays'].'</p>';
			$htmlCode[] = '</div>';
			
			//Computed total days/project
			
			($projectEnabledFields['estimated_days_project_duration'] == 0)?$showclass="hide":$showclass="show";  //visibility TS control
			$htmlCode[] = '<div class="'.$showclass.'">';
			$htmlCode[] = '<p><label>'.$this->pi_getLL('estimated_days_project_duration').'</label>'.$statistics['countEstimatedWorkedDays'].'</p>';
			$htmlCode[] = '</div>';
						
			
			//Computed estimated Cost per day
			
			($projectEnabledFields['estimated_cost_per_day'] == 0)?$showclass="hide":$showclass="show";  //visibility TS control
			$htmlCode[] = '<div class="'.$showclass.'">';
			$htmlCode[] = '<p><label>'.$this->pi_getLL('estimated_cost_per_day').'</label>'. round($statistics['averageEstimatedCostPerDay'],2).'</p>';			
			$htmlCode[] = '</div>';		
					
			//Computed estimated Real cost
			
			($projectEnabledFields['estimated_cost'] == 0)?$showclass="hide":$showclass="show";  //visibility TS control
			$htmlCode[] = '<div class="'.$showclass.'">';
			$htmlCode[] = '<p><label>'.$this->pi_getLL('estimated_cost').'</label>'.$statistics['totalEstimatedCost'].'</p>';			
			$htmlCode[] = '</div>';
			
			$htmlCode[] = '</div></div>';
		
		}
		
		
		$htmlCode[] = '</div>';
		
		
		
		
		
		
		
		
		
		//REAL
			
		($projectEnabledFields['real_data'] == 0)?$showclass="hide":$showclass="show-margin-below";  //visibility TS control
		$htmlCode[] = '<div class="'.$showclass.'"><div class="real">';
		
		//Editing / browsing of numerica values are allowed if the project has no children
		if(!$project->hasChildren()){
		
			($projectEnabledFields['real_start_date'] == 0)?$showclass="hide":$showclass="show";  //visibility TS control
			$htmlCode[] = '<div class="'.$showclass.'">';
			
				//Real start date
				//edit for admin & workers
				if( (($action == "edit_project")|($action == "create_project")) & $this->editingRole >0){
					
					if($action == "create_project"){
						$data = strtotime($defaultValues["real_start_date"]);
					}else {
						$data = $project->data['real_start_date'];
					}
					
					$htmlCode[] = writeDate($this,'real_start_date','real_start_date', $data);
				}
				//show for others
				else {
					
					($projectEnabledFields['real_start_date'] == 0)?$show="hide":$show="show";
				
					$htmlCode[] = '<p><label for="real_start_date">'.$this->pi_getLL('real_start_date').'</label>'.date($this->dateFormat,$project->data['real_start_date']).'</p>';			
				}
				
			$htmlCode[] = '</div>';
			
				
			//Real Duration
			//edit for admin & workers
			($projectEnabledFields['real_duration'] == 0)?$showclass="hide":$showclass="show";  //visibility TS control
			$htmlCode[] = '<div class="'.$showclass.'">';
			
				if( (($action == "edit_project")|($action == "create_project")) & $this->editingRole >0){
					
					if($action == "create_project"){
						$data = $defaultValues["real_duration"];
					}else {
						$data = $project->data['real_duration'];
					}
					
					$htmlCode[] = writeInput($this,'real_duration','real_duration', $data, 'inputTiny');
				}
				//show for others
				else{
					$htmlCode[] = '<p><label for="real_duration">'.$this->pi_getLL('real_duration').'</label>'.$project->data['real_duration'].'</p>';
				}
				
			$htmlCode[] = '</div>';
			
			
			//Real end date
			//show "auto" in edit mode
			($projectEnabledFields['real_end_date'] == 0)?$showclass="hide":$showclass="show";  //visibility TS control
			$htmlCode[] = '<div class="'.$showclass.'">';
			
				if( (($action == "edit_project")|($action == "create_project")) & $this->editingRole >0){
					$htmlCode[] = '<p><label for="real_end_date">'.$this->pi_getLL('real_end_date').'</label>'.$this->pi_getLL('auto').'</p>';			
				}
				//show computed value in view mode
				else {
					$htmlCode[] = '<p><label for="real_end_date">'.$this->pi_getLL('real_end_date').'</label>'.date($this->dateFormat,getEndDate($realMap)).'</p>';			
				}
				
			$htmlCode[] = '</div>';
			
			
			//Cost per day
			//edit for admins only
			($projectEnabledFields['cost_per_day'] == 0)?$showclass="hide":$showclass="show";  //visibility TS control
			$htmlCode[] = '<div class="'.$showclass.'">';
			
				if( (($action == "edit_project")|($action == "create_project")) & $this->editingRole >=2){
					
					if($action == "create_project"){
						$data = $defaultValues["cost_per_day"];
					}else {
						$data = $project->data['cost_per_day'];
					}
					
					$htmlCode[] = writeInput($this,'cost_per_day','cost_per_day', $data, 'inputTinyPlus');
				}
				//show for others
				else{
					($projectEnabledFields['cost_per_day'] == 0)?$show="hide":$show="show";
					
					$htmlCode[] = '<p><label for="cost_per_day">'.$this->pi_getLL('cost_per_day').'</label>'.$project->data['cost_per_day'].'</p>';			
				}
			
			$htmlCode[] = '</div>';		
			
					
			//Real cost
			//show "auto" in edit mode
			($projectEnabledFields['real_cost'] == 0)?$showclass="hide":$showclass="show";  //visibility TS control
			$htmlCode[] = '<div class="'.$showclass.'">';
			
				if( (($action == "edit_project")|($action == "create_project")) & $this->editingRole >0){
					$htmlCode[] = '<p><label for="real_cost">'.$this->pi_getLL('real_cost').'</label>'.$this->pi_getLL('auto').'</p>';			
				}
				//show computed value in view mode
				else {
					$htmlCode[] = '<p><label for="real_cost">'.$this->pi_getLL('real_cost').'</label>'.($project->data['cost_per_day']*$project->data['real_duration']).'</p>';			
				}	
			
			$htmlCode[] = '</div>';	
			
			
			//progress
			//edit for admin & workers
			($projectEnabledFields['progress'] == 0)?$showclass="hide":$showclass="show";  //visibility TS control
			$htmlCode[] = '<div class="'.$showclass.'">';
			
				if( (($action == "edit_project")|($action == "create_project")) & $this->editingRole >0){
					$optionLabels = array('0%','10%','20%','30%','40%','50%','60%','70%','80%','90%','100%');
					$optionValues = array('0','10','20','30','40','50','60','70','80','90','100');
					
					if($action == "create_project"){
						$data = $defaultValues["progress"];
					}else {
						$data = $project->data['progress'];
					}
					
					$htmlCode[] = writeSelect($this, 'progress','progress', $optionLabels, $optionValues, $data, "inputTinyPlus");
				}
				//show for others
				else{
					$htmlCode[] = '<p><label for="progress">'.$this->pi_getLL('progress').'</label>'.$project->data['progress'].'%</p>';			
				}
				
			$htmlCode[] = '</div>';	
			$htmlCode[] = '</div>';	
		}
		
		
		
		//COMPUTED REAL DATA
		
		//if the project has children
		//compile available information from children
		if($project->hasChildren()){
			
			($projectEnabledFields['computed_real_data'] == 0)?$showclass="hide":$showclass="show-margin-below";  //visibility TS control
			$htmlCode[] = '<div class="'.$showclass.'"><div class="computed">';
			
			$htmlCode[] = '<p><strong>'.$this->pi_getLL('statistics_from_subprojects').'</strong></p>';			
			
			
			//Computed estimated start date
			($projectEnabledFields['real_start_date'] == 0)?$showclass="hide":$showclass="show";  //visibility TS control
			$htmlCode[] = '<div class="'.$showclass.'">';
			$htmlCode[] = '<p><label for="real_start_date">'.$this->pi_getLL('real_start_date').'</label>'.date($this->dateFormat,$statistics['computedRealStartDate']).'</p>';			
			$htmlCode[] = '</div>';	
			
			
			//Computed estimated end date
			($projectEnabledFields['real_end_date'] == 0)?$showclass="hide":$showclass="show";  //visibility TS control
			$htmlCode[] = '<div class="'.$showclass.'">';
			$htmlCode[] = '<p><label for="real_end_date">'.$this->pi_getLL('real_end_date').'</label>'.date($this->dateFormat,$statistics['computedRealEndDate']).'</p>';			
			$htmlCode[] = '</div>';		
				
			//Computed estimated Duration
			($projectEnabledFields['real_duration'] == 0)?$showclass="hide":$showclass="show";  //visibility TS control
			$htmlCode[] = '<div class="'.$showclass.'">';
			$htmlCode[] = '<p><label for="real_duration">'.$this->pi_getLL('real_duration').'</label>'.$statistics['totalRealWorkedDays'].'</p>';
			$htmlCode[] = '</div>';
			
			//Computed total days/project
			($projectEnabledFields['real_days_project_duration'] == 0)?$showclass="hide":$showclass="show";  //visibility TS control
			$htmlCode[] = '<div class="'.$showclass.'">';
			$htmlCode[] = '<p><label for="real_days_project_duration">'.$this->pi_getLL('real_days_project_duration').'</label>'.$statistics['countRealWorkedDays'].'</p>';
			$htmlCode[] = '</div>';
						
			
			//Computed estimated Cost per day
			($projectEnabledFields['real_cost_per_day'] == 0)?$showclass="hide":$showclass="show";  //visibility TS control
			$htmlCode[] = '<div class="'.$showclass.'">';
			$htmlCode[] = '<p><label for="cost_per_day">'.$this->pi_getLL('real_cost_per_day').'</label>'. round($statistics['averageRealCostPerDay'],2).'</p>';			
			$htmlCode[] = '</div>';		
					
			//Computed estimated Real cost
			($projectEnabledFields['real_cost'] == 0)?$showclass="hide":$showclass="show";  //visibility TS control
			$htmlCode[] = '<div class="'.$showclass.'">';
			$htmlCode[] = '<p><label for="real_cost">'.$this->pi_getLL('real_cost').'</label>'.$statistics['totalRealCost'].'</p>';			
			$htmlCode[] = '</div>';					
			
			//Computed estimated progress
			($projectEnabledFields['progress'] == 0)?$showclass="hide":$showclass="show";  //visibility TS control
			$htmlCode[] = '<div class="'.$showclass.'">';
			$htmlCode[] = '<p><label for="progress">'.$this->pi_getLL('progress').'</label>'.round($statistics['totalRealProgress'],0).'%</p>';			
			$htmlCode[] = '</div>';
			
			$htmlCode[] = '</div></div>';
		
		}
		$htmlCode[] = '</div>';
		
		
		
		
		
		
		
		
		
		
		//parent project
		//edit for admins only
		($projectEnabledFields['parent'] == 0)?$showclass="hide":$showclass="show-margin-below";  //visibility TS control
		$htmlCode[] = '<div class="'.$showclass.'">';
			
		if( (($action == "edit_project")|($action == "create_project")) & $this->editingRole >=2){
		
			if($parent){
				$selectValues = array($parent->data['uid']);
				$selectLabels = array($parent->data['title']);
			}
			else{
				$selectValues = array();
				$selectLabels = array();							
			}
			$allValues = array();
			$allLabels = array();
			
			//void value at first for selecting no parent
			$allProjectValues[] ='';
			$allProjectLabels[] ='';
			
			
			foreach($availableProjects as $index=>$possibleProject){
				$allProjectValues[] = $possibleProject->data['uid'];
				$allProjectLabels[] = $possibleProject->data['title'];
			}
			
			if($action == "create_project"){
				$data = $defaultValues["parent"];
			}else {
				$data = $project->getParent()->data['uid'];
			}
			
			
			$htmlCode[] = writeSelect($this, 'parent','parent', $allProjectLabels, $allProjectValues, $data, "inputLong");
		}
		//show for others
		else{
			$htmlCode[] = '<p><label for="parent">'.$this->pi_getLL('parent').'</label>';
			//if we have a parent project, display its title and link
			if($project->data['parent'] != ""){
			
				//link to parent
				$urlParameters = array(
					$this->prefixId.'[action]' => 'view_project',
					$this->prefixId.'[uid]' => $parent->data['uid'],
				); 
				$link = $this->pi_getPageLink($this->session['pluginPageUID'],'',$urlParameters) ;
				
				$htmlCode[] = '<a href="'.$link.'">'.$parent->data['title'].'</a>';
			}
			$htmlCode[] = '</p>';
		}
		$htmlCode[] = '</div>';
		
		
		
		//child projects
		//edit for admin & workers
		($projectEnabledFields['children'] == 0)?$showclass="hide":$showclass="show-margin-below";  //visibility TS control
		$htmlCode[] = '<div class="'.$showclass.'">';
			
		if( (($action == "edit_project")|($action == "create_project")) & $this->editingRole >0){
			
			$htmlCode[] = '<p><label for="children">'.$this->pi_getLL('children').'</label>';
			
			//if we have children projects, display their titlee and linke
			$htmlCode[] = '<table class="">';
			foreach($children as $key=>$child){
				//link to child
				$urlParameters = array(
					$this->prefixId.'[action]' => 'view_project',
					$this->prefixId.'[uid]' => $child->data['uid'],
				); 
				$viewlink = $this->pi_getPageLink($this->session['pluginPageUID'],'',$urlParameters) ;
				
				$urlParameters = array(
					$this->prefixId.'[action]' => 'edit_project',
					$this->prefixId.'[uid]' => $child->data['uid'],
				); 
				$editlink = $this->pi_getPageLink($this->session['pluginPageUID'],'',$urlParameters) ;
				
				$htmlCode[] = '<tr><td class="linkLocal"><a href="'.$viewlink.'">'.$child->data['title'].'</a> <a href="'.$editlink.'" class="btnLink">'.$this->pi_getLL('edit').'</a></td></tr>';
				
			}
			$htmlCode[] = '</table></p>';
		}
		//show for others
		else{
			$htmlCode[] = '<p><label for="children">'.$this->pi_getLL('children').'</label>';
			
			//if we have children projects, display their titlee and linke
			$htmlCode[] = '<table class="">';
			foreach($children as $key=>$child){
				//link to child
				$urlParameters = array(
					$this->prefixId.'[action]' => 'view_project',
					$this->prefixId.'[uid]' => $child->data['uid'],
				); 
				$link = $this->pi_getPageLink($this->session['pluginPageUID'],'',$urlParameters) ;
				
				$htmlCode[] = '<tr><td class="linkLocal"><a href="'.$link.'">'.$child->data['title'].'</a></td></tr>';
				
			}
			$htmlCode[] = '</table></p>';
		}
		$htmlCode[] = '</div>';
		
		
		
		//dependencies
		//edit for admins only
		($projectEnabledFields['dependencies'] == 0)?$showclass="hide":$showclass="show-margin-below";  //visibility TS control
		$htmlCode[] = '<div class="'.$showclass.'">';
		
		if( (($action == "edit_project")|($action == "create_project")) & $this->editingRole >=2){
			$selectValues 	= array();
			$selectLabels 	= array();
			
			if($action == "create_project"){
				
				$defaultDeps = explode("," , $defaultValues["dependencies"]);
				
				$defaultDepsProjects = array();
				
				if($defaultDeps[0] !=""){
					for($i=0; $i <sizeof($defaultDeps) ; $i++){
						$defaultDepsProjects[$i] = t3lib_div::makeInstance('tx_paeproject_project');
						$defaultDepsProjects[$i]->load(intval($defaultDeps[$i]));
						
						$selectValues[] = $defaultDepsProjects[$i]->data['uid'];
						$selectLabels[] = $defaultDepsProjects[$i]->data['title'];
					}
				}
								
				
			}else {
				foreach($dependencies as $index=>$dependantOf){
					$selectValues[] = $dependantOf->data['uid'];
					$selectLabels[] = $dependantOf->data['title'];
				}
			}
							
			
			//void value at first for selecting no parent
			$allProjectValues =array();
			$allProjectLabels =array();
			
			
			foreach($availableProjects as $index=>$possibleProject){
				$allProjectValues[] = $possibleProject->data['uid'];
				$allProjectLabels[] = $possibleProject->data['title'];
			}
				
			$htmlCode[] = writeDoubleList( $this, 'dependencies','dependencies', $selectValues, $selectLabels, $allProjectValues, $allProjectLabels, 'docMulti', "projectEdit", 7);		
		}
		//show for others
		else{
			$htmlCode[] = '<p><label for="dependencies">'.$this->pi_getLL('dependencies').'</label>';
			
			//if we have dependencies, display title, link 
			if($project->data['dependencies'] != ""){
			
				$htmlCode[] = '<table class="">';
				foreach($dependencies as $key=>$dependent){
					//link to dependency
					$urlParameters = array(
						$this->prefixId.'[action]' => 'view_project',
						$this->prefixId.'[uid]' => $dependent->data['uid'],
					); 
					$link = $this->pi_getPageLink($this->session['pluginPageUID'],'',$urlParameters) ;
					
					$htmlCode[] = '<tr><td class="linkLocal"><a href="'.$link.'">'.$dependent->data['title'].'</a></td></tr>';
				}
				$htmlCode[] = '</table>';
			}
			$htmlCode[] = '</p>';
		}
		$htmlCode[] = '</div>';
		
		
		//exceptions
		//edit for admins only
		($projectEnabledFields['exceptions'] == 0)?$showclass="hide":$showclass="show-margin-below";  //visibility TS control
		$htmlCode[] = '<div class="'.$showclass.'">';
		
		if( (($action == "edit_project")|($action == "create_project")) & $this->editingRole >=2){
			
			
			//exceptions
			$htmlCode[] = '<p><label for="exceptions">'.$this->pi_getLL('exceptions').'</label>';
			
			//if we have exceptions, display title, link 
			
			$htmlCode[] = '<table class="">';
			foreach($exceptions as $key=>$exception){
				//link to exception
				$urlParameters = array(
					$this->prefixId.'[action]' => 'view_exception',
					$this->prefixId.'[uid]' => $exception->data['uid'],
				); 
				$viewlink = $this->pi_getPageLink($this->session['pluginPageUID'],'',$urlParameters) ;
				
				$urlParameters = array(
					$this->prefixId.'[action]' => 'edit_exception',
					$this->prefixId.'[uid]' => $exception->data['uid'],
				); 
				$editlink = $this->pi_getPageLink($this->session['pluginPageUID'],'',$urlParameters) ;
				
				$htmlCode[] = '<tr><td class="linkLocal"><a href="'.$viewlink.'">'.$exception->data['title'].'</a> <a href="'.$editlink.'" class="btnLink">'.$this->pi_getLL('edit').'</a></td></tr>';
			}
			$htmlCode[] = '</table>';
			
			$htmlCode[] = '</p>';
			/*
			// BASE CODE FOR DIRECT EXCEPTION EDITING, BUT MANAGEMENT IS COMPLICATE AND NOT FINISHED
			// KEPT FOR EVENTUAL FEATURE IN LATER VERSION OF THE EXTENSION
			$selectValues 	= array();
			$selectLabels 	= array();
					
			foreach($exceptions as $index=>$exception){
				$selectValues[] = $exception->data['uid'];
				$selectLabels[] = $exception->data['title'];
			}
			
			$allValues = array();
			$allLabels = array();
			$availableExceptions =  listExceptions($project->data['pid']);
			
			foreach($availableExceptions as $index=>$possibleException){
				$allValues[] = $possibleException->data['uid'];
				$allLabels[] = $possibleException->data['title'];
			}
			
			
			$htmlCode[] = writeDoubleList( $this, 'exceptions','exceptions', $selectValues, $selectLabels, $allValues, $allLabels, 'docMulti', "projectEdit");	
			*/	
		}
		//show for others
		else{
			
			//exceptions
			$htmlCode[] = '<p><label for="exceptions">'.$this->pi_getLL('exceptions').'</label>';
			
			//if we have exceptions, display title, link 
			
			$htmlCode[] = '<table class="">';
			foreach($exceptions as $key=>$exception){
				//link to exception
				$urlParameters = array(
					$this->prefixId.'[action]' => 'view_exception',
					$this->prefixId.'[uid]' => $exception->data['uid'],
				); 
				$link = $this->pi_getPageLink($this->session['pluginPageUID'],'',$urlParameters) ;
				
				$htmlCode[] = '<tr><td class="linkLocal"><a href="'.$link.'">'.$exception->data['title'].'</a></td></tr>';
			}
			$htmlCode[] = '</table>';
			
			$htmlCode[] = '</p>';
		}
		$htmlCode[] = '</div>';
		
		
		
		//administrators
		//edit for admins only
		($projectEnabledFields['administrators'] == 0)?$showclass="hide":$showclass="show-margin-below";  //visibility TS control
		$htmlCode[] = '<div class="'.$showclass.'">';
		
		if( (($action == "edit_project")|($action == "create_project")) & $this->editingRole >=2){
			$selectValues 	= array();
			$selectLabels 	= array();
			
			if($action == "create_project"){
				
				$defaultAdmins = explode("," , $defaultValues["administrators"]);
				
				$defaultAdminsFEUsers = array();
					
				if($defaultAdmins[0] !=""){
					for($i=0; $i <sizeof($defaultAdmins) ; $i++){
						$defaultAdminsFEUsers[$i] = t3lib_div::makeInstance('tx_paeproject_fe_user');
						$defaultAdminsFEUsers[$i]->load(intval($defaultAdmins[$i]));
						
						$selectValues[] = $defaultAdminsFEUsers[$i]->data['uid'];
						$selectLabels[] = $defaultAdminsFEUsers[$i]->data['name'].' ('.$defaultAdminsFEUsers[$i]->data['username'].')';
					}
				}
								
				
			}else {
				foreach($administrators['local'] as $key=>$admin){
					$selectValues[] = $admin->data['uid'];
					$selectLabels[] = $admin->data['name'].' ('.$admin->data['username'].')';
				}
			}
					
			
			
			$allValues = array();
			$allLabels = array();
			$availableUsers =  listAllUsers();
			
			foreach($availableUsers as $index=>$possibleUser){
				$allValues[] = $possibleUser->data['uid'];
				$allLabels[] = $possibleUser->data['name'].' ('.$possibleUser->data['username'].')';
			}
			
			
			$htmlCode[] = writeDoubleList( $this, 'administrators','administrators', $selectValues, $selectLabels, $allValues, $allLabels, 'docMulti', "projectEdit", 7);		
		}
		//show for others
		else{
			
			//Administrators
			$htmlCode[] = '
			<p><label for="administrators">'.$this->pi_getLL('administrators').'</label>';
			
			$administratorsHTMLDisplay = array();
			
			$htmlCode[] = '<table class="">';
			foreach($administrators['local'] as $key=>$admin){
				$administratorsHTMLDisplay[$admin->data['name']] = '<tr><td class="linkLocal">'.$admin->data['name'].' ('.$admin->data['username'].')</td></tr>';
			}
			foreach($administrators['global'] as $key=>$admin){
				$administratorsHTMLDisplay[$admin->data['name']] = '<tr><td class="linkLocal">'.$admin->data['name'].' ('.$admin->data['username'].')</td></tr>';
			}
			foreach($administratorsHTMLDisplay as $key=>$value){
				$htmlCode[] = $value;
			}
			$htmlCode[] = '</table></p>';
		}
		$htmlCode[] = '</div>';		
		
		
		//workers
		//edit for admins only
		
		($projectEnabledFields['workers'] == 0)?$showclass="hide":$showclass="show-margin-below";  //visibility TS control
		$htmlCode[] = '<div class="'.$showclass.'">';
		
		if( (($action == "edit_project")|($action == "create_project")) & $this->editingRole >=2){
			$selectValues 	= array();
			$selectLabels 	= array();
					
			if($action == "create_project"){
				
				$defaultWorkers = explode("," , $defaultValues["workers"]);
				
				$defaultWorkersFEUsers = array();
				
				if($defaultWorkers[0] !=""){
					for($i=0; $i <sizeof($defaultWorkers) ; $i++){
						$defaultWorkersFEUsers[$i] = t3lib_div::makeInstance('tx_paeproject_fe_user');
						$defaultWorkersFEUsers[$i]->load(intval($defaultWorkers[$i]));
						
						$selectValues[] = $defaultWorkersFEUsers[$i]->data['uid'];
						$selectLabels[] = $defaultWorkersFEUsers[$i]->data['name'].' ('.$defaultWorkersFEUsers[$i]->data['username'].')';
					}
				}
								
				
			}else {
				foreach($workers['local'] as $key=>$worker){
					$selectValues[] = $worker->data['uid'];
					$selectLabels[] = $worker->data['name'].' ('.$worker->data['username'].')';
				}
			}
			
			
					
			$htmlCode[] = writeDoubleList( $this, 'workers','workers', $selectValues, $selectLabels, $allValues, $allLabels, 'docMulti', "projectEdit", 7);		
		}
		//show for others
		else{
			
			//Workers
			$htmlCode[] = '
			<p><label for="workers">'.$this->pi_getLL('workers').'</label>';
			
			$workersHTMLDisplay = array();
			
			$htmlCode[] = '<table class="">';
			foreach($workers['local'] as $key=>$worker){
				$workersHTMLDisplay[$worker->data['name']] = '<tr><td class="linkLocal">'.$worker->data['name'].' ('.$worker->data['username'].')</td></tr>';
			}
			foreach($workers['global'] as $key=>$worker){
				$workersHTMLDisplay[$worker->data['name']] = '<tr><td class="linkglobal">'.$worker->data['name'].' ('.$worker->data['username'].')</td></tr>';
			}
			foreach($workersHTMLDisplay as $key=>$value){
				$htmlCode[] = $value;
			}
			$htmlCode[] = '</table></p>';
		}
		$htmlCode[] = '</div>';
		
		
		
		//edit for admin & workers
		if( (($action == "edit_project")|($action == "create_project")) & $this->editingRole >0){	
			//submit button
			$htmlCode[] = '<input type="submit" class="submit" value="'.$this->pi_getLL('submit').'">&nbsp;';
				
			//cancel link, reverts to view mode
			$urlParameters = array(
				$this->prefixId.'[action]' => 'list_projects'
			); 
			$link = $this->pi_getPageLink($this->session['pluginPageUID'],'',$urlParameters) ;
			$htmlCode[] = '<input type="button" onclick="document.location=\''.$link.'\';" value="'.$this->pi_getLL('cancel').'">';
		}
			
		$htmlCode[] = '</form>';					
		$htmlCode[] = '</div></div>';
			
		/*	
		echo "this->session['globalAction']=".$this->session['globalAction']."<br>";
		echo "action=".$action."<br>";	
		*/
			
		
?>
