<?php

		//retreiving exception info
		if ( ($action == "create_exception") & ($this->editingRole == 3) & ($record_uid=="")){
			$exception = t3lib_div::makeInstance('tx_paeproject_exception');
			$exception->init($startingPointsArray[0],$GLOBALS['TSFE']->fe_user->user['uid']);
			$affectedProjects = array();
		}
		else if ( ($action == "create_exception") & ($this->editingRole < 3)){
			//attempt to force exception creation by forged url
			//swicth to view_mode with no creation possibility
			$exception = t3lib_div::makeInstance('tx_paeproject_exception');
			$exception->init($startingPointsArray[0],$GLOBALS['TSFE']->fe_user->user['uid']);
			$exception = "view_exception";
		}
		//view or edit project
		else{	
			//retreiving exception info
			$exception = t3lib_div::makeInstance('tx_paeproject_exception');
			$exception->load($record_uid);
			$affectedProjects = $exception->listAffectedProjects();
		}
		
		
		
		
		
		//displaying exception
		$htmlCode[] = '<div class="project-display">';
		
		$htmlCode[] = '<form name="exceptionEdit" enctype="multipart/form-data" action="'.$this->pi_getPageLink($GLOBALS['TSFE']->id).'" method="POST">'; 
		
		$htmlCode[] = '<input type="hidden" name="'.$this->prefixId.'[action]" value="submit-exception">';
		$htmlCode[] = '<input type="hidden" name="'.$this->prefixId.'[uid]" value="'.$exception->data['uid'].'">';
		
		
		$htmlCode[] = '<div class="project-header">';		
		$htmlCode[] = '<h2>'.$this->pi_getLL('exception')." ".$exception->data['title'].'</h2>'; 
		
			
		$availableProjects = listOtherProjects($this->session['selectedPids'],-1);
		
		
		//Edit link
		if($GLOBALS['TSFE']->loginUser){
			if(($this->editingRole == 3) & ($action !='edit_exception') & ($action !='create_exception') ){
				$urlParameters = array(
					$this->prefixId.'[action]' => 'edit_exception',
					$this->prefixId.'[uid]' => $exception->data['uid'],
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
			
		$htmlCode[] = '</div>';
		
		//title
		//edit for admins only
		if( (($action == "edit_exception")|($action == "create_exception")) & $this->editingRole == 3){	
			//title
			$htmlCode[] = writeInput($this,'title','title', $exception->data['title'], 'inputLong');
		}
		
		//enabled
		if( (($action == "edit_exception")|($action == "create_exception")) & $this->editingRole == 3){
			$htmlCode[] = writeCheckBox($this,'ex_enabled','ex_enabled', !$exception->data['disable']);
		}
		else{
			$htmlCode[] = '<p><label for="ex_enabled">'.$this->pi_getLL('ex_enabled').'</label>'.(($exception->data['disable']==1)?$this->pi_getLL('no'):$this->pi_getLL('yes')).'</p>';	
		}
		
		//start date
		if( (($action == "edit_exception")|($action == "create_exception")) & $this->editingRole == 3){
			$htmlCode[] = writeDate($this,'ex_start_date','ex_start_date', $exception->data['start_date']);
		}
		else{
			$htmlCode[] = '<p><label for="ex_start_date">'.$this->pi_getLL('ex_start_date').'</label>'.date($this->dateFormat,$exception->data['start_date']).'</p>';		
		}
		
		
		//end date
		if( (($action == "edit_exception")|($action == "create_exception")) & $this->editingRole == 3){
			$htmlCode[] = writeDate($this,'ex_end_date','ex_end_date', $exception->data['end_date']);
		}
		else{
			$htmlCode[] = '<p><label for="ex_end_date">'.$this->pi_getLL('ex_end_date').'</label>'.date($this->dateFormat,$exception->data['end_date']).'</p>';		
		}
		
		$htmlCode[] = '<br />';
		
		//affected projects
		//edit for admins only
		if( (($action == "edit_exception")|($action == "create_exception")) & $this->editingRole == 3){
			$selectValues 	= array();
			$selectLabels 	= array();
					
			foreach($affectedProjects as $index=>$affected){
				$selectValues[] = $affected->data['uid'];
				$selectLabels[] = $affected->data['title'];
			}
			
			
			//void value at first for selecting no parent
			$allProjectValues =array();
			$allProjectLabels =array();
			
			
			foreach($availableProjects as $index=>$possibleProject){
				$allProjectValues[] = $possibleProject->data['uid'];
				$allProjectLabels[] = $possibleProject->data['title'];
			}
				
			$htmlCode[] = writeDoubleList( $this, 'ex_affected_proj','ex_affected_proj', $selectValues, $selectLabels, $allProjectValues, $allProjectLabels, 'docMulti', "exceptionEdit");		
		}
		//show for others
		else{
			$htmlCode[] = '<p><label for="ex_affected_proj">'.$this->pi_getLL('ex_affected_proj').'</label>';
		
			//if we have affected projects, display their titles and links
			$htmlCode[] = '<table class="linkList">';
			foreach($affectedProjects as $key=>$affectedProject){
				//link to affected project
				$urlParameters = array(
					$this->prefixId.'[action]' => 'view_project',
					$this->prefixId.'[uid]' => $affectedProject->data['uid'],
				); 
				$link = $this->pi_getPageLink($this->session['pluginPageUID'],'',$urlParameters) ;
				
				$htmlCode[] = '<tr><td class="linkLocal"><a href="'.$link.'">'.$affectedProject->data['title'].'</a></td></tr>';
				
			}
			$htmlCode[] = '</table></p>';
		}
		$htmlCode[] = "<br />";
		
		
		
		
		
		
		
		
		//affects estimations
		if( (($action == "edit_exception")|($action == "create_exception")) & $this->editingRole == 3){
			$htmlCode[] = writeCheckBox($this,'ex_affects_estimation','ex_affects_estimation', $exception->data['affect_estimation']);
		}
		else{
			$htmlCode[] = '<p><label for="ex_affects_estimation">'.$this->pi_getLL('ex_affects_estimation').'</label>'.(($exception->data['affect_estimation']==1)?$this->pi_getLL('yes'):$this->pi_getLL('no')).'</p>';	
		}
		
		//affects real
		if( (($action == "edit_exception")|($action == "create_exception")) & $this->editingRole == 3){
			$htmlCode[] = writeCheckBox($this,'ex_affects_real','ex_affects_real', $exception->data['affect_real']);
		}
		else{
			$htmlCode[] = '<p><label for="ex_affects_real">'.$this->pi_getLL('ex_affects_real').'</label>'.(($exception->data['affect_real']==1)?$this->pi_getLL('yes'):$this->pi_getLL('no')).'</p>';	
		}
		
		//edit for admin & workers
		if( (($action == "edit_exception")|($action == "create_exception")) & $this->editingRole == 3){	
			//submit button
			$htmlCode[] = '<input type="submit" class="submit" value="'.$this->pi_getLL('submit').'">&nbsp;';
				
			//cancel link, reverts to view mode
			$urlParameters = array(
				$this->prefixId.'[action]' => 'list_exceptions'
			); 
			$link = $this->pi_getPageLink($this->session['pluginPageUID'],'',$urlParameters) ;
			$htmlCode[] = '<input type="button" onclick="document.location=\''.$link.'\';" value="'.$this->pi_getLL('cancel').'">';
		}
		
		$htmlCode[] = '</div>';
		$htmlCode[] = '</form>';
		$htmlCode[] = '</div>';
		

?>