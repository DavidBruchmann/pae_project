<?php
		
		
		/*
		echo "<br>submit record_uid=".$record_uid."<br>";
		echo "this->session['globalAction']=".$this->session['globalAction']."<br>";
		echo "action=".$action."<br>";
		*/
		
		//print_r($this->piVars);
		
		//retreiving exception info
		$exception = t3lib_div::makeInstance('tx_paeproject_exception');
		
		//load existing exceptions
		if($record_uid!=""){
			$exception->load($record_uid);
		}
		//else init new exception
		else {
			$exception->init($startingPointsArray[0],$GLOBALS['TSFE']->fe_user->user['uid']);
			//save object once so it gets an uid
			$record_uid = $exception->store();
		}
		
		$documents 		= explode(",",$exception->data['documents']);
		
		//print_r($this->piVars);
		
		//Processing form data
		
		//exception title
		if(isset($this->piVars['title'])){
			$exception->data['title'] = $this->piVars['title'];
		}
		
		//exception disabled
		if(isset($this->piVars['ex_enabled'])){
			$exception->data['disable'] = 0;
		}
		else{
			$exception->data['disable'] = 1;
		}
		
		
		
		
		//start date
		if(isset($this->piVars['ex_start_date_d'])){
			
			$timestamp = mktime (0, 0, 0, intval($this->piVars['ex_start_date_m']),  intval($this->piVars['ex_start_date_d']), intval($this->piVars['ex_start_date_Y']));
			$exception->data['start_date'] = $timestamp;
		}
		
		//end date
		if(isset($this->piVars['ex_end_date_d'])){
			
			$timestamp = mktime (0, 0, 0, intval($this->piVars['ex_end_date_m']),  intval($this->piVars['ex_end_date_d']), intval($this->piVars['ex_end_date_Y']));
			$exception->data['end_date'] = $timestamp;
		}
		
		
		
		
		
		
		
		//affected projects
		if(isset($this->piVars['ex_affected_proj_final_selection'])){
			
			if($this->piVars['ex_affected_proj_final_selection'] != ""){
				$dependencies = explode(',',$this->piVars['ex_affected_proj_final_selection']);
						
				if(sizeof($dependencies) > 0){
					//delete old affected projects
					$sql='delete from tx_paeproject_exception_project_elements_mm where uid_local='.$exception->data['uid'];
					if($GLOBALS['TYPO3_DB']->sql(TYPO3_db,$sql)){
						$exception->data['project_elements']=0;
					}
					else{
						die("ERROR: tx_paeproject.submit_exception processing 'affected project' record(req1): " .$GLOBALS['TYPO3_DB']->sql_error());
					}
					
					//retreiving higher sorting value already stored in table
					$sorting=1;
					$sql='select max(sorting) from tx_paeproject_exception_project_elements_mm where 1';
					$result = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$sql);
					$row = $GLOBALS['TYPO3_DB']->sql_fetch_row($result);
					$sorting=$row[0];
					
					foreach($dependencies as $index => $depUid){
						//affected projects set
						$sorting = $sorting*2;
						
						$sql='insert into tx_paeproject_exception_project_elements_mm set uid_foreign='.intval($depUid).', uid_local='.$exception->data['uid'].', sorting='.$sorting;
						if($GLOBALS['TYPO3_DB']->sql(TYPO3_db,$sql)){
							//echo "set $depUid=".$depUid."<br>";
						}
						else{
							die("ERROR: tx_paeproject.submit_exception processing 'affected project' record(req2): " .$GLOBALS['TYPO3_DB']->sql_error());
						}
					}
					$exception->data['project_elements']=sizeof($dependencies);
				}
			}
			else{
				//delete any existing affected project
				$sql='delete from tx_paeproject_exception_project_elements_mm where uid_local='.$exception->data['uid'];
				if($GLOBALS['TYPO3_DB']->sql(TYPO3_db,$sql)){
					$exception->data['project_elements']=0;
				}
				else{
					die("ERROR: tx_paeproject.submit_exception processing 'affected project' record(req3): " .$GLOBALS['TYPO3_DB']->sql_error());
				}
			}
		}
		
		//exception affects real
		if(isset($this->piVars['ex_affects_real'])){
			$exception->data['affect_real'] = 1;
		}
		else{
			$exception->data['affect_real'] = 0;
		}
		
		//exception affects estimation
		if(isset($this->piVars['ex_affects_estimation'])){
			$exception->data['affect_estimation'] = 1;
		}
		else{
			$exception->data['affect_estimation'] = 0;
		}
		
		//saving project object
		$exception->store();
		
?>