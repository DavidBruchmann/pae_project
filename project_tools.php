<?php

	/**
	 * Computes the data of all projects specified by traversing their children and aggreagatin / summing / averaging data according to data types.
	 *
	 * @param projectList an array of tx_paeproject_project objects, children not included (they are traversed automatically)
	 * @param level is for internal use, please ignore. It helps locate first levell of recursion were statistics should be compiled
	 * @return	an array of statistics
	 */
	function computeProjectData($projectList, $level=0)
    {
		if (isset($projectList) && sizeof($projectList)>0) {
			global $data;
			$statistics = array();

			if ($level == 0) {
				$data = array();
				$data['estimatedMap'] = array();
				$data['estimatedCost'] = array();
				$data['estimatedCostPerDay'] = array();

				$data['realMap'] = array();
				$data['real_duration'] = array();
				$data['real_cost'] = array();
				$data['progress'] = array();

			}

			//recursive data computing for children from bottom to top of the project tree
			foreach ($projectList as $index => $project) {
				if ($project->hasChildren()) {
					//we are interested only in children data
					computeProjectData($project->getChildren(), 1);
				}
				else{
					//computing current project data

					$exceptions = $project->getExceptions();

					//echo "project uid=".$project->data['uid']."<br>" ;
					//echo "estimated_start_date=".date('d/m/Y',$project->data['estimated_start_date'])."<br>" ;
					$data['estimatedMap'][] = mapWorkedDays(
                        $project->data['estimated_start_date'],
                        $project->data['estimated_duration'],
                        $project->data['worked_days'],
                        $exceptions,
					    0
                    );

					$data['estimatedCost'][] = $project->data['estimated_cost'];
					if ($project->data['estimated_duration']>0) {
						$data['estimatedCostPerDay'][] = $project->data['estimated_cost'] / $project->data['estimated_duration'];
					}
					else {
						$data['estimatedCostPerDay'][] = 0;
					}
					//echo "real_start_date=".date('d/m/Y',$project->data['real_start_date'])."<br>" ;
					$data['realMap'][] = mapWorkedDays(
                        $project->data['real_start_date'],
                        $project->data['real_duration'],
                        $project->data['worked_days'],
                        $exceptions,
                        1
                    );
					$data['real_duration'][] = $project->data['real_duration'];
					$data['real_cost'][] = $project->data['cost_per_day'] * $project->data['real_duration'];
					$data['progress'][] = $project->data['progress'];
				}
			}

			if($level == 0){
				//estimated data
				$estimatedMapFusioned = mapsFusion($data['estimatedMap']);

				$computedEstimatedStartDate = getStartDate($estimatedMapFusioned);
				$computedEstimatedEndDate = getEndDate($estimatedMapFusioned);

				if ($computedEstimatedEndDate == 0) {
                    $computedEstimatedEndDate = $computedEstimatedStartDate;
				}
                if ($computedRealEndDate == 0) {
                    $computedRealEndDate = $computedRealStartDate;
                }
				//echo "computedEstimatedEndDate=".$computedEstimatedEndDate."<br>";
				//echo "computedRealEndDate=".$computedRealEndDate;
				//real data
				$realMapFusioned = mapsFusion($data['realMap']);

				$computedRealStartDate = getStartDate($realMapFusioned);
				$computedRealEndDate = getEndDate($realMapFusioned);

				//Counting days worked
				$totalEstimatedWorkedDays = 0; //sum of days which are being worked
				$countEstimatedWorkedDays = 0; //total number of days per project worked

				processMap($estimatedMapFusioned, $totalEstimatedWorkedDays, $countEstimatedWorkedDays, $computedEstimatedStartDate,$computedEstimatedEndDate);


				//Counting days worked
				$totalRealWorkedDays = 0; //sum of days which are being worked
				$countRealWorkedDays = 0; //total number of days per project worked

				processMap($realMapFusioned, $totalRealWorkedDays, $countRealWorkedDays, $computedRealStartDate,$computedRealEndDate);


				//adding estimated costs
				$totalEstimatedCost = array_sum($data['estimatedCost']);

				//averageEstimatedCostPerDay
				if ( sizeof($data['estimatedCostPerDay']) > 0) {
					$averageEstimatedCostPerDay = array_sum($data['estimatedCostPerDay']) / sizeof($data['estimatedCostPerDay']);
				}
				else {
					$averageEstimatedCostPerDay = 0;
				}
				//adding real costs
				$totalRealCost = array_sum($data['real_cost']);

				$totalRealProgress = 0;

				//computing weighted progress
				if ($countRealWorkedDays >0) {
					for ($i=0;$i<sizeof($data);$i++) {
						$totalRealProgress += $data['progress'][$i] * $data['real_duration'][$i] / $countRealWorkedDays;
					}
					//averageRealCostPerDay
					$averageRealCostPerDay = $totalRealCost / $countRealWorkedDays;
				}
				else {
					$totalRealProgress = 0;
					$averageRealCostPerDay = 0;
				}



				//saving statistics
				$statistics['estimatedMapFusioned'] = $estimatedMapFusioned;
				$statistics['computedEstimatedStartDate'] = $computedEstimatedStartDate;

				$statistics['computedEstimatedEndDate'] = $computedEstimatedEndDate;
				$statistics['totalEstimatedWorkedDays'] = $totalEstimatedWorkedDays;
				$statistics['countEstimatedWorkedDays'] = $countEstimatedWorkedDays;
				$statistics['totalEstimatedCost'] = $totalEstimatedCost;
				$statistics['averageEstimatedCostPerDay'] = $averageEstimatedCostPerDay;

				$statistics['realMapFusioned'] = $realMapFusioned;
				$statistics['computedRealStartDate'] = $computedRealStartDate;

				$statistics['computedRealEndDate'] = $computedRealEndDate;
				$statistics['totalRealWorkedDays'] = $totalRealWorkedDays;
				$statistics['countRealWorkedDays'] = $countRealWorkedDays;
				$statistics['totalRealCost'] = $totalRealCost;
				$statistics['totalRealProgress'] = $totalRealProgress;
				$statistics['averageRealCostPerDay'] = $averageRealCostPerDay;

				return $statistics;
			}
		}
		return false;

	}

	/**
	 * Computes some statistics and fills the holes in the map with zeros
	 */
	function processMap(&$dayMap, &$totalWorkedDays, &$countWorkedDays, &$mapStartDate, &$mapEndDate)
    {
		foreach ($dayMap as $year => $monthMap) {
				//foreach($monthMap as $month => $dayMap){
				for ($month=1; $month<13; $month++) {

					$thisMonthStartTimestamp = strtotime($year . "-" . $month . "-1");
					$thisMonthEndTimestamp = strtotime($year . "-" . $month . "-" . get_days_in_month($year, $month));

					//if currentMonth is within map
					if (($thisMonthEndTimestamp >= $mapStartDate) & ($thisMonthStartTimestamp <= $mapEndDate)) {

						if (!isset($dayMap[$year][$month])) {
							//filling the holes !
							$dayMap[$year][$month] = array();
						}
						$daysInMonth = get_days_in_month($year, $month);

						for($day=1; $day<$daysInMonth+1; $day++){
							$thisDayTimestamp = strtotime($year . "-" . $month . "-" . $day);
							//echo "y=$year m=$month d=$day (".$thisDayTimestamp." >= ".$mapStartDate.") & (".$thisDayTimestamp." <= ".$mapStartDate.")<br>";

							//if currentDay is within map

							if (($thisDayTimestamp >= $mapStartDate) & ($thisDayTimestamp <= $mapEndDate)) {
								//filling the holes !
								if (!isset($dayMap[$year][$month][$day])) {
									$dayMap[$year][$month][$day] = 0;
								}
							}
							if ($dayMap[$year][$month][$day] > 0) {
								$totalWorkedDays += 1;
								$countWorkedDays += $dayMap[$year][$month][$day];
							}
						}
						ksort($dayMap[$year][$month],SORT_NUMERIC); //sort array
					}
				}
				ksort($dayMap[$year],SORT_NUMERIC);
			}
			ksort($dayMap,SORT_NUMERIC);
	}

	/**
	 * Outputs a project map as XML
	 *
	 * @param datamap the worked days mapping of the projet
	 * @return	an array of strings
	 */
	function projectMapToXML($dataMap,$decay, $absStartDate){
		$xmlCode = array();
		$absNum = 0;
		$firstDay = 1;
		foreach($dataMap as $year => $monthMap){
			$xmlCode[] = $decay.'	<year num="' . $year . '">';
			foreach($monthMap as $month => $dayMap){
				$xmlCode[] = $decay . '		<month num="' . $month . '" numDays="' . get_days_in_month($year, $month) . '">';
				foreach ($dayMap as $day => $value) {
					if ($firstDay==1) {
						//count days since the begining of the project
						$startDate = date('Y-m-d',$absStartDate);

						if ($month<10) $month="0" . $month;
						if ($day<10) $day="0" . $day;
						$endDate = $year . '-' . $month . '-' . $day;

						if ($absStartDate > 0 && mktime(0, 0, 0, $month, $day, $year)>0) {
							//number of days in range
							/*
							$sql="SELECT DATEDIFF('".$endDate."','".$startDate."');";
							//echo $sql."<br>";
							$result = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$sql);
							$row = $GLOBALS['TYPO3_DB']->sql_fetch_row($result);
							$absNum=$row[0];
							*/

							$absNum = calculate_day_between(date('Ymd', $absStartDate), $year . $month . $day);
							//echo "absNum=".$absNum."<br>";
						}
						else $absNum = 0;
						$firstDay = 0;
					}
					$xmlCode[] = $decay.'			<day num="' . $day . '" absNum="' . ++$absNum . '">' . $value . '</day>';
				}
				$xmlCode[] = $decay . '		</month>';
			}
			$xmlCode[] = $decay . '	</year>';
		}
		return $xmlCode;
	}

	/**
     *  Merges an array of worked day maps into a single worked day map
     *
     *  @param array $array   an array of worked day maps
     *  @return the resulting fusioned worked day map
     */
	function mapsFusion($maps)
    {
		$command = "\$result =  array_merge_n(";
		for($i=0; $i<sizeof($maps); $i++) {
			$command .= "\$maps[" . $i . "]";
			if ($i<sizeof($maps) - 1) $command .= ", ";
		}
		$command .= ");";

		//effectively mergin arrays
		eval($command);
		return $result;
	}

	/**
     *  Merges two arrays of any dimension and add overlapping values. Return arrays sorted by their indices
     *
     *  This is the process' core!
     *  Here each array is merged with the current resulting one
     *
     *  @param array $array   Resulting array - passed by reference
     *  @param array $array_i Array to be merged - passed by reference
     */
    function array_merge_2(&$array, &$array_i)
    {
        // For each element of the array (key => value):
        foreach ($array_i as $k => $v) {
            // If the value itself is an array, the process repeats recursively:
            if (is_array($v)) {
                if (!isset($array[$k])) {
                    $array[$k] = array();
                }
                array_merge_2($array[$k], $v);

            // Else, the value is assigned to the current element of the resulting array:
            } else {
                if (isset($array[$k]) && is_array($array[$k])) {
                    $array[$k][0] = $v;
                } else {
                    if (isset($array) && !is_array($array)) {
                        $temp = $array;
                        $array = array();
                        $array[0] = $temp;
                    }
                    $array[$k] += $v;
                }
            }
        }
		ksort($array, SORT_NUMERIC); //sort array

    }

    /**
     *  Merges any number of arrays of any dimension
     *
     *  The arrays to be merged are passed as arguments to the function,
     *  which uses an external function (array_merge_2) to merge each of them
     *  with the resulting one as it's being constructed
     *
     *  @return array Resulting array, once all have been merged
     */
    function array_merge_n()
    {
        // Initialization of the resulting array:
        $array = array();

        // Arrays to be merged (function's arguments):
        $arrays =& func_get_args();

        // Merging of each array with the resulting one:
        foreach ($arrays as $array_i) {
            if (is_array($array_i)) {
                array_merge_2($array, $array_i);
            }
        }
        return $array;
    }

	/**
	 * Returns all other projects in the same sysfolder as project with uid=currentProjectUid.
	 *
	 * @param int localpid	a comma separated list of folder uids to search for
	 * @param int currentProjectUid	uid of the current project. (use value -1 if you really want any existing projects).
	 * @return	an array of tx_paeproject_project objects
	 */
	function listOtherProjects($localpid, $currentProjectUid)
    {
		$projects=array();

		if ($currentProjectUid != "") {

			$queryParts = array(
				'SELECT' => "*",
				'FROM' => "tx_paeproject_projectelement",
				'WHERE' => "tx_paeproject_projectelement.pid IN (" . $localpid . ") AND tx_paeproject_projectelement.uid!=" . $currentProjectUid . " AND deleted=0",
				'ORDER BY'=> "tx_paeproject_projectelement.uid"
			);

			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				$queryParts['SELECT'],
				$queryParts['FROM'],
				$queryParts['WHERE'],
				'',
				$queryParts['ORDER BY']
			) or die("ERROR: tx_paeproject.form_tools->listOtherProjects(req1): " . $GLOBALS['TYPO3_DB']->sql_error());

			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_row($result)) {
				$project = t3lib_div::makeInstance('tx_paeproject_project');
				$project->load($row[0]);
				$projects[] = $project;
			}
		}
		return $projects;

	}

	/**
	 * Returns all exceptions in the sysfolder where uid=localPid.
	 *
	 * @param int localpid	uid of the sysfolder
	 * @return	an array of tx_paeproject_exception objects
	 */
	function listExceptions($sysFolderUid)
    {
		$queryParts = array(
			'SELECT' => "*",
			'FROM' => "tx_paeproject_exception",
			'WHERE' => "pid=" . $sysFolderUid . " AND deleted=0",
			'ORDER BY'=> "uid"
		);

		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			$queryParts['SELECT'],
			$queryParts['FROM'],
			$queryParts['WHERE'],
			'',
			$queryParts['ORDER BY']
		) or die("ERROR: tx_paeproject.form_tools->listExceptions(req1): " . $GLOBALS['TYPO3_DB']->sql_error());

		$exceptions = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_row($result)) {
			$exception = t3lib_div::makeInstance('tx_paeproject_exception');
			$exception->load($row[0]);
			$exceptions[] = $exception;
		}
		return $exceptions;
	}

	/**
	 * Returns all available users on the typo3 installation.
	 *
	 * @return	the parent tx_paeproject_project object, false if no parent exists
	 */
	function listAllUsers()
    {
		$queryParts = array(
			'SELECT' => "*",
			'FROM' => "fe_users",
			'WHERE' => "deleted=0",
			'ORDER BY'=> "name"
		);

		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			$queryParts['SELECT'],
			$queryParts['FROM'],
			$queryParts['WHERE'],
			'',
			$queryParts['ORDER BY']
		) or die("ERROR: tx_paeproject.form_tools->listAllUsers(req1): " . $GLOBALS['TYPO3_DB']->sql_error());

		$users=array();
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_row($result)){
			$user = t3lib_div::makeInstance('tx_paeproject_fe_user');
			$user->load($row[0]);
			$users[] = $user;
		}
		return $users;

	}

	/**
	 * Returns an array of HTML formated list of projects .
	 *
	 * @return	an array of HTML formated list of projects. Use implode(chr(10),$returndHTML) if you want to convert the array to string
	 */
	function listAllProjects($parent, $plugin, $editButtonsOverride)
    {
		$htmlCode = array();

		if (isset($parent)) {
			if ($plugin->lConf['listModeDisplay']=="showChildren") {
				$children = $parent->getChildren();

				foreach ($children as $childIndex=>$child) {
					$htmlCode[] = '<div class="decay">';
					$htmlCode = array_merge($htmlCode, outputProjectSummup($child, $plugin, $editButtonsOverride));
					$htmlCode = array_merge($htmlCode, listAllProjects($child, $plugin, $editButtonsOverride));
					$htmlCode[] = '</div>';
				}
			}
		}
		else {
			//listing starting with root projects

			$queryParts = array(
				'SELECT' => "uid",
				'FROM' => "tx_paeproject_projectelement",
				'WHERE' => "pid IN (".$plugin->session['selectedPids'].") AND parent=0 AND hidden=0 AND deleted=0",
				'ORDER BY' => "sorting",
			);

			// Get ressource records:
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery (
				$queryParts['SELECT'],
				$queryParts['FROM'],
				$queryParts['WHERE'],
				'',
				$queryParts['ORDER BY']
			);

			$projectUids = array();
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$projectUids[] = $row['uid'];
			}

			//display projects
			foreach ($projectUids as $index => $uid) {
				$project = t3lib_div::makeInstance('tx_paeproject_project');
				$project->load($uid);

				$htmlCode = array_merge($htmlCode, outputProjectSummup($project, $plugin, $editButtonsOverride));

				$htmlCode = array_merge($htmlCode, listAllProjects($project, $plugin, $editButtonsOverride));
			}
		}
		return $htmlCode;
	}

	/**
	 * Returns an array of HTML formated project .
	 *
	 * @return	an array of HTML formated projects. Use implode(chr(10),$returndHTML) if you want to convert the array to string
	 */
	function outputProjectSummup($project, $plugin, $editButtonsOverride)
    {
		$htmlCode = array();

		if (isset($project)) {
			$htmlCode[] = '<div class="project-summup">';
			$htmlCode[] = '<div class="project-title"><div class="right">';

			//projet view link
			$urlParameters = array(
				$plugin->prefixId.'[action]' => 'view_project',
				$plugin->prefixId.'[uid]' => $project->data['uid'],
			);
			$viewLink = $plugin->pi_getPageLink($plugin->session['pluginPageUID'],'',$urlParameters) ;

			if (trim($editButtonsOverride["view"]) == "") {
				$htmlCode[] = (($authorIndex>0) ? ', ' : '') . '<a href="' . $viewLink . '" class="btnLink">' . $plugin->pi_getLL('view') . '</a>';
			}
			else {
				$htmlCode[] = (($authorIndex>0) ? ', ' : '') . '<a href="' . $viewLink . '" class="btnLink">' . $editButtonsOverride["view"].'</a>';
			}

			if ($plugin->editingRole >=2) {
				//edit link
				$urlParameters = array(
					$plugin->prefixId . '[action]' => 'edit_project',
					$plugin->prefixId . '[uid]' => $project->data['uid'],
				);
				$viewLink = $plugin->pi_getPageLink($plugin->session['pluginPageUID'], '', $urlParameters);

				if (trim($editButtonsOverride["edit"]) == "") {
					$htmlCode[] = (($authorIndex>0) ? ', ' : '') . '<a href="' . $viewLink . '" class="btnLink">' . $plugin->pi_getLL('edit') . '</a>';
				}
				else {
					$htmlCode[] = (($authorIndex>0) ? ', ' : '') . '<a href="' . $viewLink . '" class="btnLink">' . $editButtonsOverride["edit"] . '</a>';
				}

				//delete link
				$urlParameters = array(
					$plugin->prefixId.'[action]' => 'delete_project',
					$plugin->prefixId.'[uid]' => $project->data['uid'],
				);
				$viewLink = $plugin->pi_getPageLink($plugin->session['pluginPageUID'], '', $urlParameters) ;

				if(trim($editButtonsOverride["delete"]) == ""){
					$htmlCode[] = (($authorIndex>0) ? ', ' : '') . '<a href="' . $viewLink . '" class="btnLink">' . $plugin->pi_getLL('delete') . '</a>';
				}
				else{
					$htmlCode[] = (($authorIndex>0) ? ', ' : '') . '<a href="' . $viewLink . '" class="btnLink">' . $editButtonsOverride["delete"] . '</a>';
				}

				/*
				//move up link
				$urlParameters = array(
					$plugin->prefixId.'[action]' => 'move_up',
					$plugin->prefixId.'[uid]' => $project->data['uid'],
				);
				$upLink = $plugin->pi_getPageLink($plugin->session['pluginPageUID'], '', $urlParameters) ;
				$htmlCode[] = (($authorIndex>0) ? ', ' : '') . '<a href="' . $upLink . '" class="btnLink"><strong>&nbsp;&uarr;&nbsp;</strong></a>';

				//move down link
				$urlParameters = array(
					$plugin->prefixId . '[action]' => 'move_down',
					$plugin->prefixId . '[uid]' => $project->data['uid'],
				);
				$upLink = $plugin->pi_getPageLink($plugin->session['pluginPageUID'], '', $urlParameters) ;
				$htmlCode[] = (($authorIndex>0) ? ', ' : '') . '<a href="' . $upLink . '" class="btnLink"><strong>&nbsp;&darr;&nbsp;</strong></a>';
				*/
			}

			$htmlCode[] = '</div>' . $project->data['title'];
			$htmlCode[] = '</div>';
			$htmlCode[] = '<div class="project-text">' . substr(strip_tags($project->data['description']), 0, 300) . ((strlen($project->data['description'])>300) ? '[...]' : '') . '</div>';
			$htmlCode[] = '</div>';
		}
		else $htmlCode[] = '<div>' . $plugin->pi_getLL('void') . '</div>';

		return $htmlCode;
	}

	/**
	 * Returns an array of XML formated list of projects .
	 *
	 * @return	an array of XML formated list of projects. Use implode(chr(10),$returndHTML) if you want to convert the array to string
	 */
	function listAllProjectsXML($parent,$session, $decay='', $absStartDate=0)
    {
		$xmlCode=array();

		if( isset($parent)) {
			$children = $parent->getChildren();
			$decay .= "	";
			foreach ($children as $childIndex => $child) {
				$xmlCode[] = $decay.'<project>';
				$xmlCode = array_merge($xmlCode, outputProjectSummupXML($child, $session, $decay, $absStartDate));
				$xmlCode = array_merge($xmlCode, listAllProjectsXML($child, $session, $decay, $absStartDate));
				$xmlCode[] = $decay.'</project>';
			}
		}
		else {
			$decay .= "	";
			//listing starting with root projects

			$queryParts = array(
				'SELECT' => "uid",
				'FROM' => "tx_paeproject_projectelement",
				'WHERE' => "pid IN (" . $session['selectedPids'] . ") AND parent=0 AND hidden=0 AND deleted=0",
				'ORDER BY' => "sorting",
			);

			// Get ressource records:
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery (
				$queryParts['SELECT'],
				$queryParts['FROM'],
				$queryParts['WHERE'],
				'',
				$queryParts['ORDER BY']
			);

			$projectUids = array();
			if (isset($res)) {
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
					$projectUids[] = $row['uid'];
				}
			}

			if (sizeof($projectUids)>0) {
				$rootProjects=array();

				//create root project instances
				foreach ($projectUids as $index => $uid) {
					$rootProjects[$index] = t3lib_div::makeInstance('tx_paeproject_project');
					$rootProjects[$index]->load($uid);
				}

				//find global date range
				$statistics = computeProjectData($rootProjects, 0);

				//find smallest start date
				if ($statistics['computedEstimatedStartDate'] < $statistics['computedRealStartDate']) {
					$rangeStartDate = $statistics['computedEstimatedStartDate'];
				}
				else {
					$rangeStartDate = $statistics['computedRealStartDate'];
				}

				//find biggest end date
				if ($statistics['computedEstimatedEndDate'] > $statistics['computedRealEndDate']) {
					$rangeEndDate = $statistics['computedEstimatedEndDate'];
				}
				else {
					$rangeEndDate = $statistics['computedRealEndDate'];
				}

				$xmlCode[] = $decay . '<global>';

				//rangeStartDate
				$curDate = $rangeStartDate;
				$day = date('d', $curDate);
				$month = date('m', $curDate);
				$year = date('Y', $curDate);
				$startDate = date('Y-m-d', $curDate);
				$xmlCode[] = $decay.'	<rangeStartDate day="' . $day . '" month="' . $month . '" year="' . $year . '" />';

				//rangeEndDate
				$curDate = $rangeEndDate;
				$day = date('d', $curDate);
				$month = date('m', $curDate);
				$year = date('Y', $curDate);
				$endDate = date('Y-m-d', $curDate);
				$xmlCode[] = $decay . '	<rangeEndDate day="' . $day . '" month="' . $month . '" year="' . $year . '" />';

				/*
				//as timestamp. Cannot use to to bug in flash date object constructor from timestamp
				$xmlCode[] = $decay . '	<rangeStartDate timestamp="' . $rangeStartDate . '" />';
				$xmlCode[] = $decay . '	<rangeEndDate timestamp="' . $rangeEndDate . '" />';
				*/

				//number of days in range
				if ($rangeStartDate>0 && $rangeEndDate >0) {

					/*
                    $sql = "SELECT DATEDIFF('".$endDate."','".$startDate."');";

					$result = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$sql);
					$row = $GLOBALS['TYPO3_DB']->sql_fetch_row($result);
					$numDays = $row[0];
                    */

					$numDays = calculate_day_between(date('Ymd', $rangeStartDate), date('Ymd', $rangeEndDate));
				}
				else $numDays = 0;
				$xmlCode[] = $decay . '	<rangeLength daysCount="' . $numDays . '" />';

				$xmlCode[] = $decay . '</global>';


				//display projects
				foreach($projectUids as $index => $uid){

					$xmlCode[] = $decay . '<project>';
					$xmlCode = array_merge($xmlCode, outputProjectSummupXML($rootProjects[$index], $session, $decay, $rangeStartDate));
					$xmlCode = array_merge($xmlCode, listAllProjectsXML($rootProjects[$index], $session, $decay, $rangeStartDate));
					$xmlCode[] = $decay . '</project>';
				}

			}
			else{
				$xmlCode[] = '<error>No project found</error>';
			}
		}
		return $xmlCode;
	}

	/**
	 * Returns an array of XML formated project .
	 *
	 * @return	an array of XML formated projects. Use implode(chr(10),$returndHTML) if you want to convert the array to string
	 */
	function outputProjectSummupXML($project, $session, $decay, $absStartDate)
    {
		$xmlCode=array();

		if (isset($project)) {

			//projet view link
			$urlParameters = $session['prefixId'] . '[action]=view_project&' . $session['prefixId'] . '[uid]=' . $project->data['uid'];

			$viewLink = 'index.php?id=' . $session['pluginPageUID'] . '&' . $urlParameters ;
			$xmlCode[] = $decay.'	<link>' . $viewLink.'</link>';
			$xmlCode[] = $decay.'	<uid>' . $project->data['uid'] . '</uid>';
			$xmlCode[] = $decay.'	<pid>' . $project->data['pid'] . '</pid>';
			$xmlCode[] = $decay.'	<title>' . $project->data['title'] . '</title>';

			/*
			$curDate = $project->data['estimated_start_date'];
			$day = date('d', $curDate);
			$month = date('m', $curDate);
			$year = date('Y', $curDate);
			$xmlCode[] = $decay . '	<estimatedStartDate day="' . $day . '" month="' . $month . '" year="' . $year . '" />';


			$curDate = $project->data['estimated_end_date'];
			$day = date('d', $curDate);
			$month = date('m', $curDate);
			$year = date('Y', $curDate);
			$xmlCode[] = $decay . '	<estimated_end_date day="' . $day . '" month="' . $month . '" year="' . $year . '" />';
			*/

			$statistics = computeProjectData(array($project), 0);
			/*
			$statistics['estimatedMapFusioned'] = $estimatedMapFusioned;
			$statistics['computedEstimatedStartDate'] = $computedEstimatedStartDate;
			$statistics['computedEstimatedEndDate'] = $computedEstimatedEndDate;
			$statistics['totalEstimatedWorkedDays'] = $totalEstimatedWorkedDays;
			$statistics['countEstimatedWorkedDays'] = $countEstimatedWorkedDays;
			$statistics['totalEstimatedCost'] = $totalEstimatedCost;
			$statistics['averageEstimatedCostPerDay'] = $averageEstimatedCostPerDay;

			$statistics['realMapFusioned'] = $realMapFusioned;
			$statistics['computedRealStartDate'] = $computedRealStartDate;
			$statistics['computedRealEndDate'] = $computedRealEndDate;
			$statistics['totalRealWorkedDays'] = $totalRealWorkedDays;
			$statistics['countRealWorkedDays'] = $countRealWorkedDays;
			$statistics['totalRealCost'] = $totalRealCost;
			$statistics['totalRealProgress'] = $totalRealProgress;
			$statistics['averageRealCostPerDay'] = $averageRealCostPerDay;
			*/

			//Computed estimated data

			$curDate = $statistics['computedEstimatedStartDate'];
			$day = date('d',$curDate);
			$month = date('m',$curDate);
			$year = date('Y',$curDate);
			$xmlCode[] = $decay . '	<computedEstimatedStartDate day="' . $day . '" month="' . $month . '" year="' . $year . '" />';


			$curDate=$statistics['computedEstimatedEndDate'];
			$day = date('d',$curDate);
			$month = date('m',$curDate);
			$year = date('Y',$curDate);
			$xmlCode[] = $decay . '	<computedEstimatedEndDate day="' . $day . '" month="' . $month . '" year="' . $year . '" />';

			/*
			//as timestamp
			$xmlCode[] = $decay . '	<computedEstimatedStartDate timestamp="' . $statistics['computedEstimatedStartDate'] . '" />';
			$xmlCode[] = $decay . '	<computedEstimatedEndDate timestamp="' . $statistics['computedEstimatedEndDate'] . '" />';
			*/

			$xmlCode[] = $decay . '	<estimatedMapFusioned>';
			$xmlCode = array_merge($xmlCode, projectMapToXML($statistics['estimatedMapFusioned'], $decay . "	", $absStartDate));
			$xmlCode[] = $decay . '	</estimatedMapFusioned>';

			//computed real data
			$curDate = $statistics['computedRealStartDate'];
			$day = date('d', $curDate);
			$month = date('m', $curDate);
			$year = date('Y', $curDate);
			$xmlCode[] = $decay . '	<computedRealStartDate day="' . $day . '" month="' . $month . '" year="' . $year . '" />';

			$curDate = $statistics['computedRealEndDate'];
			$day = date('d', $curDate);
			$month = date('m', $curDate);
			$year = date('Y', $curDate);
			$xmlCode[] = $decay . '	<computedRealEndDate day="' . $day . '" month="' . $month . '" year="' . $year . '" />';

			$xmlCode[] = $decay . '	<realMapFusioned>';
			$xmlCode = array_merge($xmlCode, projectMapToXML($statistics['realMapFusioned'], $decay . "	", $absStartDate));
			$xmlCode[] = $decay . '	</realMapFusioned>';

			//$xmlCode[] = '<description>' . substr(strip_tags($project->data['description']), 0, 300) . ((strlen($project->data['description'])>300) ? '[...]' : '') . '</description>';
		}
		return $xmlCode;
	}

	/**
	 * Returns an array of HTML formated list of exceptions.
	 *
	 * @return	an array of HTML formated list of exceptions. Use implode(chr(10),$returndHTML) if you want to convert the array to string
	 */
	function listAllExceptions($plugin, $editButtonsOverride)
    {
		$htmlCode = array();

		$queryParts = array(
			'SELECT' => "uid",
			'FROM' => "tx_paeproject_exception",
			'WHERE' => "pid IN (" . $plugin->session['selectedPids'] . ") AND deleted=0",
			'ORDER BY' => "sorting",
		);

		// Get ressource records:
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery (
			$queryParts['SELECT'],
			$queryParts['FROM'],
			$queryParts['WHERE'],
			'',
			$queryParts['ORDER BY']
		);

		$exceptionUids = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$exceptionUids[] = $row['uid'];
		}

		//display exceptions
		foreach ($exceptionUids as $index => $uid) {
			$exception = t3lib_div::makeInstance('tx_paeproject_exception');
			$exception->load($uid);
			$htmlCode = array_merge($htmlCode, outputExceptionSummup($exception, $plugin, $editButtonsOverride));
		}
		return $htmlCode;
	}

	/**
	 * Returns an array of HTML formated exception.
	 *
	 * @return	an array of HTML formated exceptions. Use implode(chr(10),$returndHTML) if you want to convert the array to string
	 */
	function outputExceptionSummup($exception,$plugin, $editButtonsOverride)
    {
		$htmlCode = array();

		if (isset($exception)) {

			$htmlCode[] = '<div class="project-summup">';
			$htmlCode[] = '<div class="project-title"><div class="right">';

			//exception view link
			$urlParameters = array(
				$plugin->prefixId . '[action]' => 'view_exception',
				$plugin->prefixId . '[uid]' => $exception->data['uid'],
			);
			$viewLink = $plugin->pi_getPageLink($plugin->session['pluginPageUID'], '', $urlParameters);

			if (trim($editButtonsOverride["view"]) == "") {
				$htmlCode[] = (($authorIndex>0) ? ', ' : '') . '<a href="' . $viewLink . '" class="btnLink">' . $plugin->pi_getLL('view') . '</a>';
			}
			else {
				$htmlCode[] = (($authorIndex>0) ? ', ' : '') . '<a href="' . $viewLink . '" class="btnLink">' . $editButtonsOverride["view"] . '</a>';
			}

			if ($plugin->editingRole >= 2) {
				//edit link
				$urlParameters = array(
					$plugin->prefixId . '[action]' => 'edit_exception',
					$plugin->prefixId . '[uid]' => $exception->data['uid'],
				);
				$viewLink = $plugin->pi_getPageLink($plugin->session['pluginPageUID'], '', $urlParameters);

				if (trim($editButtonsOverride["edit"]) == "") {
					$htmlCode[] = (($authorIndex>0) ? ', ' : '') . '<a href="' . $viewLink . '" class="btnLink">' . $plugin->pi_getLL('edit') . '</a>';
				}
				else{
					$htmlCode[] = (($authorIndex>0) ? ', ' : '') . '<a href="' . $viewLink . '" class="btnLink">' . $editButtonsOverride["edit"] . '</a>';
				}

				//delete link
				$urlParameters = array(
					$plugin->prefixId . '[action]' => 'delete_exception',
					$plugin->prefixId . '[uid]' => $exception->data['uid'],
				);
				$viewLink = $plugin->pi_getPageLink($plugin->session['pluginPageUID'], '', $urlParameters);

				if (trim($editButtonsOverride["delete"]) == "") {
					$htmlCode[] = (($authorIndex>0) ? ', ' : '') . '<a href="' . $viewLink . '" class="btnLink">' . $plugin->pi_getLL('delete') . '</a>';
				}
				else{
					$htmlCode[] = (($authorIndex>0) ? ', ' : '') . '<a href="' . $viewLink . '" class="btnLink">' . $editButtonsOverride["delete"] . '</a>';
				}

				/*
				//move up link
				$urlParameters = array(
					$plugin->prefixId.'[action]' => 'move_up_exception',
					$plugin->prefixId.'[uid]' => $exception->data['uid'],
				);
				$upLink = $plugin->pi_getPageLink($plugin->session['pluginPageUID'],'',$urlParameters) ;
				$htmlCode[] = (($authorIndex>0)?', ':'').'<a href="'.$upLink.'" class="btnLink"><strong>&nbsp;&uarr;&nbsp;</strong></a>';

				//move down link
				$urlParameters = array(
					$plugin->prefixId.'[action]' => 'move_exception',
					$plugin->prefixId.'[uid]' => $exception->data['uid'],
				);
				$upLink = $plugin->pi_getPageLink($plugin->session['pluginPageUID'],'',$urlParameters) ;
				$htmlCode[] = (($authorIndex>0)?', ':'').'<a href="'.$upLink.'" class="btnLink"><strong>&nbsp;&darr;&nbsp;</strong></a>';
				*/
			}

			$htmlCode[] = '</div>' . $exception->data['title'];
			$htmlCode[] = '</div>';
			$htmlCode[] = '<div>' . $plugin->pi_getLL('ex_start_date') . ' '.date($plugin->dateFormat, $exception->data['start_date']) . '</div>';
			$htmlCode[] = '<div>' . $plugin->pi_getLL('ex_end_date') . ' ' . date($plugin->dateFormat, $exception->data['end_date']) . '</div>';

			$htmlCode[] = '</div>';

		}
		else $htmlCode[] = '<div>' . $plugin->pi_getLL('void') . '</div>';

		return $htmlCode;
	}

	/**
	 * Returns a comma separated list of day names from a comma separated list of day numbers
	 *
	 * @param	tslib_pibase the plugin object in which the form element is written
	 * @param	workedDaysList		day numbers from 1 to 7 separated by commas
	 * @return	Day names separated by commas in the current language
	 */
	function listWorkedDays($plugin, $workedDaysList)
    {
		$dayNumbers = explode(',', $workedDaysList);
		$dayWords = array();
		foreach ($dayNumbers as $key => $value) {
			$dayWords[] = $plugin->pi_getLL('day_' . $value);
		}
		return $dayWords;
	}
