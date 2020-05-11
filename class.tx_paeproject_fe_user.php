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


require_once(t3lib_extMgm::extPath('pae_project','class.tx_paeproject_dbrecord.php'));

/**
 * Class that manages project objects.
 *
 * @author	Communauté d'agglomération du pays d'Aubagne et de l'Etoile, Mind2Machine <ace@mind2machine.com>
 * @package	TYPO3
 * @subpackage	tx_paeproject
 */
class tx_paeproject_fe_user extends tx_paeproject_dbrecord {

		var $tableName = "fe_users";
		
		/**
		 * Constructor
		 *
		 * @param	
		 * @return	
		 */
		function tx_paeproject_fe_user(){
			
		}
		
		
		/**
		 * initializes basic values for a new record
		 *
		 * @param	pid			sets the parent record (ex:sysfolder uid) where the record is contained
		 * @param	user_uid	uid of the backend user who has created the record
		 */
		function init($pid,$user_uid){
		
			$this->data = array();
			$this->data['uid'] = 			""; 				//unique identifier
			$this->data['pid'] = 			$pid; 				//parent identifier
			$this->data['tstamp'] = 		time(); 			//last modification timestamp
			$this->data['crdate'] = 		time(); 			//creation date timestamp
			$this->data['cruser_id'] = 		$user_uid; 			//user id of the backend user who created the record
			$this->data['username'] = 		""; 				//login
			$this->data['password'] = 		"%$15$µ";
			$this->data['usergroup'] = 		0; 	
			$this->data['disable'] = 		0; 				
			$this->data['starttime'] = 		0; 					//user auto activation
			$this->data['endtime'] = 		0; 					//user auto deactivation
							
			$this->data['name'] = 			""; 				//real name
			$this->data['address'] = 		""; 			
			$this->data['telephone'] = 		""; 				
			$this->data['fax'] = 			""; 				
			$this->data['email'] = 			""; 				
			$this->data['lockToDomain'] = 	"";  				
			
			$this->data['deleted'] = 		0;
			$this->data['uc'] = 			"";
			$this->data['title'] = 			"";
			$this->data['zip'] = 			"";
			$this->data['city'] = 			"";
			$this->data['country'] = 		"";
			$this->data['www'] = 			"";
			$this->data['company'] = 		"";
			$this->data['image'] = 			"";
			$this->data['TSconfig'] = 		"";
					
			$this->data['fe_cruser_id'] = 	0;
			$this->data['lastlogin'] = 		0;
			$this->data['is_online'] = 		0;
						
		}
		
		
	}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pae_project/class.tx_paeproject_fe_user.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pae_project/class.tx_paeproject_fe_user.php']);
}

?>