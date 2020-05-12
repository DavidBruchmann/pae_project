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

require_once(t3lib_extMgm::extPath('pae_project', 'class.tx_paeproject_dbrecord.php'));

/**
 * Class that manages project objects.
 *
 * @author      Communauté d'agglomération du pays d'Aubagne et de l'Etoile, Mind2Machine <ace@mind2machine.com>
 * @package     TYPO3
 * @subpackage  tx_paeproject
 */
class tx_paeproject_exception extends tx_paeproject_dbrecor
{
    var $tableName = "tx_paeproject_exception";

    /**
     * Constructor
     */
    function tx_paeproject_exception()
    {

    }

    /**
     * initializes basic values for a new record
     *
     * @param  pid       sets the parent record (ex:sysfolder uid) where the record is contained
     * @param  user_uid  uid of the backend user who has created the record
     */
    function init($pid, $user_uid)
    {
        $this->data = array();
        $this->data['uid']               = "";                       //unique identifier
        $this->data['pid']               = $pid;                     //parent identifier
        $this->data['tstamp']            = time();                   //last modification timestamp
        $this->data['crdate']            = time();                   //creation date timestamp
        $this->data['cruser_id']         = $user_uid;                //user id of the backend user who created the record
        $this->data['sorting']           = 1000000000;               //order
        $this->data['deleted']           = 0;
        $this->data['title']             = "";                       //exception name
        $this->data['start_date']        = mktime(0, 0, 0, 01, 01);  //date set to 1st January of current year by default
        $this->data['end_date']          = mktime(0, 0, 0, 01, 01);  //date set to 1st January of current year by default
        $this->data['project_elements']  = 0;
        $this->data['affect_estimation'] = 0;
        $this->data['affect_real']       = 0;
        $this->data['disable']           = 0;
    }

    /**
     * Returns the affected project objects, if any.
     *
     * @return array of tx_paeproject_project objects
     */
    function listAffectedProjects()
    {
        $affectedProjects = array();

        $queryParts = array(
            'SELECT' => "uid_foreign",
            'FROM' => "tx_paeproject_exception_project_elements_mm",
            'WHERE' => "uid_local=" . $this->data['uid'],
        );

        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            $queryParts['SELECT'],
            $queryParts['FROM'],
            $queryParts['WHERE']
        ) or die("ERROR: tx_paeproject_exception->listAffectedProjects(req1): " . $GLOBALS['TYPO3_DB']->sql_error());

        while($row = $GLOBALS['TYPO3_DB']->sql_fetch_row($result)){
            $affectedProject = t3lib_div::makeInstance('tx_paeproject_project');
            $affectedProject->load($row[0]);
            $affectedProjects[] = $affectedProject;
        }

        return $affectedProjects;
    }

    function delete()
    {
        $sql = "DELETE FROM tx_paeproject_exception WHERE uid=".$this->data['uid'];
        if ($GLOBALS['TYPO3_DB']->sql(TYPO3_db, $sql)) {
            // nothing to do
        }
        else die("ERROR: class.tx_paeproject_exception->delete(req1): " . $GLOBALS['TYPO3_DB']->sql_error());

        $sql = "DELETE FROM tx_paeproject_exception_project_elements_mm WHERE uid_local=".$this->data['uid'];
        if ($GLOBALS['TYPO3_DB']->sql(TYPO3_db, $sql)) {
            // nothing to do
        }
        else die("ERROR: class.tx_paeproject_exception->delete(req2): " . $GLOBALS['TYPO3_DB']->sql_error());
    }

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pae_project/class.tx_paeproject_exception.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pae_project/class.tx_paeproject_exception.php']);
}
