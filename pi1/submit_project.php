<?php
		
		
		/*
		echo "<br>submit record_uid=".$record_uid."<br>";
		echo "this->session['globalAction']=".$this->session['globalAction']."<br>";
		echo "action=".$action."<br>";
		*/
		
		//retreiving project info
		$project = t3lib_div::makeInstance('tx_paeproject_project');
		
		//load existing projects
		if($record_uid!=""){
			$project->load($record_uid);
		}
		//else init new project
		else {
			$project->init($startingPointsArray[0],$GLOBALS['TSFE']->fe_user->user['uid']);
			//save object once so it gets an uid
			$record_uid = $project->store();
		}
		
		$documents 		= explode(",",$project->data['documents']);
		
		//print_r($this->piVars);
		
		//Processing form data
		
		//project title
		if(isset($this->piVars['title'])){
			$project->data['title'] = $this->piVars['title'];
		}
		
		//project description
		if(isset($this->piVars['description'])){
			$project->data['description'] = $this->piVars['description'];
		}
		
		//document uploaded
		if($this->piVars['action'] == "uploadDoc"){
			$fileName="";
			$uploadFormFieldName = $this->prefixId;
			
			//retreive document
			if (move_uploaded_file($_FILES[$uploadFormFieldName]['tmp_name']['documents_upload'], $this->uploaddir . $_FILES[$uploadFormFieldName]['name']['documents_upload'])) {
				$newDocument=$_FILES[$uploadFormFieldName]['name']['documents_upload'];    
			}
			//add it to the project
			if(!in_array($newDocument,$documents)){
				$documents[]=$newDocument;
				$project->data['documents'] = implode(",",$documents);
			}
			
			//revert to main action
			$this->piVars['action'] = $this->session['globalAction'];
			$action = $this->session['globalAction'];
		}
		
		
		//document deletion
		if($this->piVars['action'] == "deleteDoc"){
			
			//deleting file in uploads folder
			$delDocument = 			($this->piVars['documents']) ? 	$this->piVars['documents']:'';
		
			if(unlink(PATH_site.$this->uploaddir.$delDocument)){
				//removing value in project object
				$key = array_search($delDocument, $documents);
				array_splice ($documents, $key, 1);
				$project->data['documents'] = implode(",",$documents);
			}
			
			//revert to main action
			$this->piVars['action'] = $this->session['globalAction'];
			$action = $this->session['globalAction'];
		}
		
		
		//worked days
		if(isset($this->piVars['worked_days_final_selection']) && $this->piVars['worked_days_final_selection'] != ""){
			$project->data['worked_days'] = $this->piVars['worked_days_final_selection'];
		}
		else{
			//if there are no worked days, set 1,2,3,4,5 by default
			$project->data['worked_days'] = "1,2,3,4,5";
		}
		
		//estimated start date
		if(isset($this->piVars['estimated_start_date_d'])){
			
			$timestamp = mktime (0, 0, 0, intval($this->piVars['estimated_start_date_m']),  intval($this->piVars['estimated_start_date_d']), intval($this->piVars['estimated_start_date_Y']));
			$project->data['estimated_start_date'] = $timestamp;
			
			//debug
			//echo "timestamp=".$timestamp." ".date($this->dateFormat,$timestamp)."<br>";
			
		}
		
		//estimated duration
		if(isset($this->piVars['estimated_duration'])){
			$project->data['estimated_duration'] = $this->piVars['estimated_duration'];
		}
		
		//estimated cots
		if(isset($this->piVars['estimated_cost'])){
			$project->data['estimated_cost'] = $this->piVars['estimated_cost'];
		}
		
		//real start date
		if(isset($this->piVars['real_start_date_d'])){
			
			$timestamp = mktime (0, 0, 0, intval($this->piVars['real_start_date_m']),  intval($this->piVars['real_start_date_d']), intval($this->piVars['real_start_date_Y']));
			$project->data['real_start_date'] = $timestamp;
			
			//debug
			//echo "timestamp=".$timestamp." ".date($this->dateFormat,$timestamp)."<br>";
		}
		
		//Real Duration
		if(isset($this->piVars['real_duration'])){
			$project->data['real_duration'] = $this->piVars['real_duration'];
		}
		
		
		//Cost per day
		if(isset($this->piVars['cost_per_day'])){
			$project->data['cost_per_day'] = $this->piVars['cost_per_day'];
		}
		
		//progress
		if(isset($this->piVars['progress'])){
			$project->data['progress'] = $this->piVars['progress'];
		}
		
		//parent project
		if(isset($this->piVars['parent'])){
			
			if($this->piVars['parent'] != ""){
			
				$parent = intval($this->piVars['parent']);
								
				//delete any existing parent
				$sql='delete from tx_paeproject_projectelement_parent_mm where uid_local='.$project->data['uid'];
				if($GLOBALS['TYPO3_DB']->sql(TYPO3_db,$sql)){
					$project->data['parent']=0;
				}
				else{
					die("ERROR: tx_paeproject.submit_project processing 'parent' record(req1): " .$GLOBALS['TYPO3_DB']->sql_error());
				}
				
				//parent set
				$sql='INSERT INTO tx_paeproject_projectelement_parent_mm SET uid_foreign='.$parent.', uid_local='.$project->data['uid'];
				if($GLOBALS['TYPO3_DB']->sql(TYPO3_db,$sql)){
					$project->data['parent']=1;
				}
				else{
					die("ERROR: tx_paeproject.submit_project processing 'parent' record(req2): " .$GLOBALS['TYPO3_DB']->sql_error());
				}
			
			}
			else{
				//delete any existing parent
				$sql='delete from tx_paeproject_projectelement_parent_mm where uid_local='.$project->data['uid'];
				if($GLOBALS['TYPO3_DB']->sql(TYPO3_db,$sql)){
					$project->data['parent']=0;
				}
				else{
					die("ERROR: tx_paeproject.submit_project processing 'parent' record(req3): " .$GLOBALS['TYPO3_DB']->sql_error());
				}
			}
		}
		
		
		
		//dependencies
		if(isset($this->piVars['dependencies_final_selection'])){
			
			if($this->piVars['dependencies_final_selection'] != ""){
				$dependencies = explode(',',$this->piVars['dependencies_final_selection']);
						
				if(sizeof($dependencies) > 0){
					//delete old dependencies
					$sql='delete from tx_paeproject_projectelement_dependencies_mm where uid_local='.$project->data['uid'];
					if($GLOBALS['TYPO3_DB']->sql(TYPO3_db,$sql)){
						$project->data['dependencies']=0;
					}
					else{
						die("ERROR: tx_paeproject.submit_project processing 'dependencies' record(req1): " .$GLOBALS['TYPO3_DB']->sql_error());
					}
					
					//retreiving higher sorting value already stored in table
					$sorting=1;
					$sql='select max(sorting) from tx_paeproject_projectelement_dependencies_mm where 1';
					$result = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$sql);
					$row = $GLOBALS['TYPO3_DB']->sql_fetch_row($result);
					$sorting=$row[0];
					
					foreach($dependencies as $index => $depUid){
						//dependencies set
						$sorting = $sorting*2;
						
						$sql='insert into tx_paeproject_projectelement_dependencies_mm set uid_foreign='.intval($depUid).', uid_local='.$project->data['uid'].', sorting='.$sorting;
						if($GLOBALS['TYPO3_DB']->sql(TYPO3_db,$sql)){
							//echo "set $depUid=".$depUid."<br>";
						}
						else{
							die("ERROR: tx_paeproject.submit_project processing 'dependencies' record(req2): " .$GLOBALS['TYPO3_DB']->sql_error());
						}
					}
					$project->data['dependencies']=sizeof($dependencies);
				}
			}
			else{
				//delete any existing dependency
				$sql='delete from tx_paeproject_projectelement_dependencies_mm where uid_local='.$project->data['uid'];
				if($GLOBALS['TYPO3_DB']->sql(TYPO3_db,$sql)){
					$project->data['dependencies']=0;
				}
				else{
					die("ERROR: tx_paeproject.submit_project processing 'dependencies' record(req3): " .$GLOBALS['TYPO3_DB']->sql_error());
				}
			}
		}
		
		
		//exceptions
		//WARNING : THIS CODE IS NOT TESTED AND IS NOT EXPECTED TO WORK YET
		// KEEP FOR LATER VERSION OF THE EXTENSION
		/*if($this->piVars['exceptions_final_selection']){
			
			if($this->piVars['exceptions_final_selection'] != ""){
				$exceptions = explode(',',$this->piVars['exceptions_final_selection']);
						
				if(sizeof($exceptions) > 0){
					//delete old exceptions
					$sql='delete from tx_paeproject_exception_project_elements_mm where uid_foreign='.$project->data['uid'];
					if($GLOBALS['TYPO3_DB']->sql(TYPO3_db,$sql)){
						
					}
					else{
						die("ERROR: tx_paeproject.submit_project processing 'exceptions' record(req1): " .$GLOBALS['TYPO3_DB']->sql_error());
					}
					
					//retreiving higher sorting value already stored in table
					$sorting=1;
					$sql='select max(sorting) from tx_paeproject_exception_project_elements_mm where 1';
					$result = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$sql);
					$row = $GLOBALS['TYPO3_DB']->sql_fetch_row($result);
					$sorting=$row[0];
					
					foreach($exceptions as $index => $depUid){
						//exceptions set
						$sorting = $sorting*2;
						
						$sql='insert into tx_paeproject_exception_project_elements_mm set uid_local='.intval($depUid).', uid_foreign='.$project->data['uid'].', sorting='.$sorting;
						if($GLOBALS['TYPO3_DB']->sql(TYPO3_db,$sql)){
							//echo "set $depUid=".$depUid."<br>";
						}
						else{
							die("ERROR: tx_paeproject.submit_project processing 'exceptions' record(req2): " .$GLOBALS['TYPO3_DB']->sql_error());
						}
					}
					$project->data['exceptions']=sizeof($exceptions);
				}
			}
			else{
				//delete any existing exception
				$sql='delete from tx_paeproject_exception_project_elements_mm where uid_foreign='.$project->data['uid'];
				if($GLOBALS['TYPO3_DB']->sql(TYPO3_db,$sql)){
					$project->data['exceptions']=0;
				}
				else{
					die("ERROR: tx_paeproject.submit_project processing exceptions' record(req3): " .$GLOBALS['TYPO3_DB']->sql_error());
				}
			}
		}*/
		
		
		//administrators
		if(isset($this->piVars['administrators_final_selection'])){
			
			if($this->piVars['administrators_final_selection'] != ""){
				$administrators = explode(',',$this->piVars['administrators_final_selection']);
						
				if(sizeof($administrators) > 0){
					//delete old administrators
					$sql='delete from tx_paeproject_projectelement_administrators_mm where uid_local='.$project->data['uid'];
					if($GLOBALS['TYPO3_DB']->sql(TYPO3_db,$sql)){
						$project->data['administrators']=0;
					}
					else{
						die("ERROR: tx_paeproject.submit_project processing 'administrators' record(req1): " .$GLOBALS['TYPO3_DB']->sql_error());
					}
					
					//retreiving higher sorting value already stored in table
					$sorting=1;
					$sql='select max(sorting) from tx_paeproject_projectelement_administrators_mm where 1';
					$result = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$sql);
					$row = $GLOBALS['TYPO3_DB']->sql_fetch_row($result);
					$sorting=$row[0];
					
					foreach($administrators as $index => $depUid){
						//administrators set
						$sorting = $sorting*2;
						
						$sql='insert into tx_paeproject_projectelement_administrators_mm set uid_foreign='.intval($depUid).', uid_local='.$project->data['uid'].', sorting='.$sorting;
						if($GLOBALS['TYPO3_DB']->sql(TYPO3_db,$sql)){
							//echo "set $depUid=".$depUid."<br>";
						}
						else{
							die("ERROR: tx_paeproject.submit_project processing 'administrators' record(req2): " .$GLOBALS['TYPO3_DB']->sql_error());
						}
					}
					$project->data['administrators']=sizeof($administrators);
				}
			}
			else{
				//delete any existing dependency
				$sql='delete from tx_paeproject_projectelement_administrators_mm where uid_local='.$project->data['uid'];
				if($GLOBALS['TYPO3_DB']->sql(TYPO3_db,$sql)){
					$project->data['administrators']=0;
				}
				else{
					die("ERROR: tx_paeproject.submit_project processing 'administrators' record(req3): " .$GLOBALS['TYPO3_DB']->sql_error());
				}
			}
		}
		
		
		//workers
		if(isset($this->piVars['workers_final_selection'])){
			
			if($this->piVars['workers_final_selection'] != ""){
				$workers = explode(',',$this->piVars['workers_final_selection']);
						
				if(sizeof($workers) > 0){
					//delete old workers
					$sql='delete from tx_paeproject_projectelement_workers_mm where uid_local='.$project->data['uid'];
					if($GLOBALS['TYPO3_DB']->sql(TYPO3_db,$sql)){
						$project->data['workers']=0;
					}
					else{
						die("ERROR: tx_paeproject.submit_project processing 'workers' record(req1): " .$GLOBALS['TYPO3_DB']->sql_error());
					}
					
					//retreiving higher sorting value already stored in table
					$sorting=1;
					$sql='select max(sorting) from tx_paeproject_projectelement_workers_mm where 1';
					$result = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$sql);
					$row = $GLOBALS['TYPO3_DB']->sql_fetch_row($result);
					$sorting=$row[0];
					
					foreach($workers as $index => $depUid){
						//workers set
						$sorting = $sorting*2;
						
						$sql='insert into tx_paeproject_projectelement_workers_mm set uid_foreign='.intval($depUid).', uid_local='.$project->data['uid'].', sorting='.$sorting;
						if($GLOBALS['TYPO3_DB']->sql(TYPO3_db,$sql)){
							//echo "set $depUid=".$depUid."<br>";
						}
						else{
							die("ERROR: tx_paeproject.submit_project processing 'workers' record(req2): " .$GLOBALS['TYPO3_DB']->sql_error());
						}
					}
					$project->data['workers']=sizeof($workers);
				}
			}
			else{
				//delete any existing dependency
				$sql='delete from tx_paeproject_projectelement_workers_mm where uid_local='.$project->data['uid'];
				if($GLOBALS['TYPO3_DB']->sql(TYPO3_db,$sql)){
					$project->data['workers']=0;
				}
				else{
					die("ERROR: tx_paeproject.submit_project processing 'workers' record(req3): " .$GLOBALS['TYPO3_DB']->sql_error());
				}
			}
		}
		
		/*
		echo "this->session['globalAction']=".$this->session['globalAction']."<br>";
		echo "action=".$action."<br>";
		*/	
		
		//saving project object
		$project->store();
		
?>