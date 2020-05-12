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
require_once(t3lib_extMgm::extPath('pae_project', 'class.tx_paeproject_exception.php'));
require_once(t3lib_extMgm::extPath('pae_project', 'class.tx_paeproject_fe_user.php'));

/**
 * Class that manages project objects.
 *
 * @author      Communauté d'agglomération du pays d'Aubagne et de l'Etoile, Mind2Machine <ace@mind2machine.com>
 * @package     TYPO3
 * @subpackage  tx_paeproject
 */
class tx_paeproject_project extends tx_paeproject_dbrecord
{
    var $tableName = "tx_paeproject_projectelement";

    /**
     * Constructor
     */
    function tx_paeproject_project()
    {

    }

    /**
     * initializes basic values for a new record
     *
     * @param  int  pid       sets the parent record (ex:sysfolder uid) where the record is contained
     * @param  int  user_uid  uid of the backend user who has created the record
     */
    function init($pid,$user_uid){
        $this->data                         = array();
        $this->data['uid']                  = "";                      // unique identifier
        $this->data['pid']                  = $pid;                    // parent identifier
        $this->data['tstamp']               = time();                  // last modification timestamp
        $this->data['crdate']               = time();                  // creation date timestamp
        $this->data['cruser_id']            = $user_uid;               // user id of the backend user who created the record
        $this->data['t3ver_oid']            = 0;                       // versioning data
        $this->data['t3ver_id']             = 0;                       // versioning data
        $this->data['t3ver_wsid']           = 0;                       // versioning data
        $this->data['t3ver_label']          = "";                      // versioning data
        $this->data['t3ver_state']          = 0;                       // versioning data
        $this->data['t3ver_stage']          = 0;                       // versioning data
        $this->data['t3ver_count']          = 0;                       // versioning data
        $this->data['t3ver_tstamp']         = 0;                       // versioning data
        $this->data['t3_origuid']           = 0;
        $this->data['sorting']              = 1000000000;              // order
        $this->data['deleted']              = 0;
        $this->data['hidden']               = 0;
        $this->data['title']                = "";                      // project title
        $this->data['description']          = "";                      // project description
        $this->data['worked_days']          = "";                      // comma separated list of worked days 0=monday, 1=tuesday etc...
        $this->data['estimated_start_date'] = mktime(0, 0, 0, 01, 01); // date set to 1st January of current year by default
        $this->data['estimated_duration']   = 0;
        $this->data['estimated_cost']       = 0;
        $this->data['real_start_date']      = mktime(0, 0, 0, 01, 01); // date set to 1st January of current year by default
        $this->data['real_duration']        = 0;
        $this->data['cost_per_day']         = 0;
        $this->data['progress']             = 0;                       // project progress in percent
        $this->data['parent']               = 0;                       // parent project
        $this->data['dependencies']         = 0;                       // projects which must be completed before starting this one
        $this->data['administrators']       = 0;                       // uids of people with full edit rights on the project
        $this->data['workers']              = 0;
        $this->data['documents']            = "";
    }

    /**
     * Returns an array containing a list of exceptions affecting current project. Project must be loaded prior using this function.
     *
     * @return  array of tx_paeproject_exception objects
     */
    function getExceptions(){

            $exceptions=array();

            if ($this->data['uid'] != "") {
                $queryParts = array(
                    'SELECT' => "uid_local",
                    'FROM' => "tx_paeproject_exception_project_elements_mm",
                    'WHERE' => "uid_foreign=" . intval($this->data['uid']),
                );

                $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                    $queryParts['SELECT'],
                    $queryParts['FROM'],
                    $queryParts['WHERE']
                ) or die("ERROR: tx_paeproject_project->getExceptionsForProject(req1): " .$GLOBALS['TYPO3_DB']->sql_error());

                while($row = $GLOBALS['TYPO3_DB']->sql_fetch_row($result)){
                    $newException = t3lib_div::makeInstance('tx_paeproject_exception');
                    $newException->load($row[0]);
                    $exceptions[]=$newException;
                }
            }
            return $exceptions;
    }

    /**
     * Returns the parent project object, if any.
     *
     * @return  object  the parent tx_paeproject_project object, false if no parent exists
     */
    function getParent(){

        if ($this->data['uid'] != "") {
            $queryParts = array(
                'SELECT' => "uid_foreign",
                'FROM' => "tx_paeproject_projectelement_parent_mm",
                'WHERE' => "uid_local=" . intval($this->data['uid']),
            );

            $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                $queryParts['SELECT'],
                $queryParts['FROM'],
                $queryParts['WHERE']
            ) or die("ERROR: tx_paeproject_project->getParent(req1): " . $GLOBALS['TYPO3_DB']->sql_error());

            while($row = $GLOBALS['TYPO3_DB']->sql_fetch_row($result)){
                $parent = t3lib_div::makeInstance('tx_paeproject_project');
                $parent->load($row[0]);
            }
        }
        if (isset($parent)) return $parent;
        else return false;
    }


    /**
     * Returns the children project objects, if any.
     *
     * @return  array of tx_paeproject_project objects
     */
    function getChildren()
    {
        $children=array();

        if ($this->data['uid'] != "") {
            $queryParts = array(
                'SELECT' => "uid_local",
                'FROM' => "tx_paeproject_projectelement_parent_mm",
                'WHERE' => "uid_foreign=" . intval($this->data['uid']),
            );

            $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                $queryParts['SELECT'],
                $queryParts['FROM'],
                $queryParts['WHERE']
            ) or die("ERROR: tx_paeproject_project->getChildren(req1): " . $GLOBALS['TYPO3_DB']->sql_error());

            while($row = $GLOBALS['TYPO3_DB']->sql_fetch_row($result)){
                $child = t3lib_div::makeInstance('tx_paeproject_project');
                $child->load($row[0]);
                $children[]=$child;
            }
        }
        return $children;
    }

    /**
     * Tells if the project has children.
     *
     * @return  int  0 if no children, else children count.
     */
    function hasChildren()
    {
        return sizeof($this->getChildren());
    }

    /**
     * Returns an array containing a list of projects this project depends on. Project must be loaded prior using this function.
     *
     * @return  array of tx_paeproject_project objects
     */
    function getDependencies()
    {
        $dependencies=array();

        if ($this->data['uid'] != "") {
            $queryParts = array(
                'SELECT' => "uid_foreign",
                'FROM' => "tx_paeproject_projectelement_dependencies_mm",
                'WHERE' => "uid_local=" . intval($this->data['uid']),
            );

            $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                $queryParts['SELECT'],
                $queryParts['FROM'],
                $queryParts['WHERE']
            ) or die("ERROR: tx_paeproject_project->getDependencies(req1): " . $GLOBALS['TYPO3_DB']->sql_error());

            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_row($result)) {
                $newProject = t3lib_div::makeInstance('tx_paeproject_project');
                $newProject->load($row[0]);
                $dependencies[]=$newProject;
            }
        }
        return $dependencies;
    }

    /**
     * Returns a multidimensional array containing a list of fe_users allowed to admin this project. Project must be loaded prior using this function.
     *
     * @return  array  administrators['local'] : array of administrators explicitely assigned to this project
     * @return  array  administrators['global'] : array of administrators inherited from parents
     */
    function getAdministrators($defaultValues)
    {
        $administrators=array();
        $administrators['local']=array();
        $administrators['global']=array();

        if ($this->data['uid'] != "") {
            //processing local administrators
            $queryParts = array(
                'SELECT' => "uid_foreign",
                'FROM' => "tx_paeproject_projectelement_administrators_mm",
                'WHERE' => "uid_local=" . intval($this->data['uid']),
            );

            $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                $queryParts['SELECT'],
                $queryParts['FROM'],
                $queryParts['WHERE']
            ) or die("ERROR: tx_paeproject_project->getAdministrators(req1): " .$GLOBALS['TYPO3_DB']->sql_error());

            while($row = $GLOBALS['TYPO3_DB']->sql_fetch_row($result)){
                $newUser = t3lib_div::makeInstance('tx_paeproject_fe_user');
                $newUser->load($row[0]);
                $administrators['local'][]=$newUser;
            }

            //processing parent administrators
            $parent = $this->getParent();
            if ($parent != false) {
                $parentAdmins = $parent->getAdministrators($defaultValues);
                $administrators['global'] = array_merge($administrators['global'],$parentAdmins['local']);
                $administrators['global'] = array_merge($administrators['global'],$parentAdmins['global']);
            }
            else {
                //adding typoscript default administrators to the recursive list of administrators
                $TS_Admins = explode(',',$defaultValues['administrators']);
                foreach ($TS_Admins as $uid) {
                    $newUser = t3lib_div::makeInstance('tx_paeproject_fe_user');
                    $newUser->load($uid);
                    $administrators['global'][]=$newUser;
                }
            }
        }
        return $administrators;
    }

    function delete()
    {
        $sql = "DELETE FROM tx_paeproject_projectelement WHERE uid=" . $this->data['uid'];
        if ($GLOBALS['TYPO3_DB']->sql(TYPO3_db,$sql)) {
            // nothing to do
        }
        else die("ERROR: class.tx_paeproject_project->delete(req1): " . $GLOBALS['TYPO3_DB']->sql_error());

        $sql = "DELETE FROM tx_paeproject_exception_project_elements_mm WHERE uid_foreign=" . intval($this->data['uid']);
        if ($GLOBALS['TYPO3_DB']->sql(TYPO3_db,$sql)) {
            // nothing to do
        }
        else die("ERROR: class.tx_paeproject_project->delete(req2): " . $GLOBALS['TYPO3_DB']->sql_error());

        $sql = "DELETE FROM tx_paeproject_projectelement_administrators_mm WHERE uid_local=" . intval($this->data['uid']);
        if ($GLOBALS['TYPO3_DB']->sql(TYPO3_db,$sql)) {
            // nothing to do
        }
        else die("ERROR: class.tx_paeproject_project->delete(req3): " . $GLOBALS['TYPO3_DB']->sql_error());

        $sql = "DELETE FROM tx_paeproject_projectelement_dependencies_mm WHERE uid_local=" . intval($this->data['uid']);
        if ($GLOBALS['TYPO3_DB']->sql(TYPO3_db,$sql)) {
            // nothing to do
        }
        else die("ERROR: class.tx_paeproject_project->delete(req4): " . $GLOBALS['TYPO3_DB']->sql_error());

        $sql = "DELETE FROM tx_paeproject_projectelement_workers_mm WHERE uid_local=" . intval($this->data['uid']);
        if ($GLOBALS['TYPO3_DB']->sql(TYPO3_db,$sql)) {
            // nothing to do
        }
        else die("ERROR: class.tx_paeproject_project->delete(req5): " . $GLOBALS['TYPO3_DB']->sql_error());

        $sql = "DELETE FROM tx_paeproject_projectelement_parent_mm WHERE uid_local=" . intval($this->data['uid']);
        if ($GLOBALS['TYPO3_DB']->sql(TYPO3_db,$sql)) {
            // nothing to do
        }
        else die("ERROR: class.tx_paeproject_project->delete(req6): " . $GLOBALS['TYPO3_DB']->sql_error());
    }

    /**
     * Returns a multidimensional array containing a list of fe_users allowed to alter progress settings of this project. Project must be loaded prior using this function.
     *
     * @return  array  workers['local'] : array of workers explicitely assigned to this project
     * @return  array  workers['global'] : array of workers inherited from parents
     */
    function getWorkers($defaultValues)
    {
        $workers=array();
        $workers['local']=array();
        $workers['global']=array();

        if ($this->data['uid'] != "") {
            //processing local administrators
            $queryParts = array(
                'SELECT' => "uid_foreign",
                'FROM' => "tx_paeproject_projectelement_workers_mm",
                'WHERE' => "uid_local=" . intval($this->data['uid']),
            );

            $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                $queryParts['SELECT'],
                $queryParts['FROM'],
                $queryParts['WHERE']
            ) or die("ERROR: tx_paeproject_project->getWorkers(req1): " . $GLOBALS['TYPO3_DB']->sql_error());

            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_row($result)) {
                $newUser = t3lib_div::makeInstance('tx_paeproject_fe_user');
                $newUser->load($row[0]);
                $workers['local'][] = $newUser;
            }

            //processing parent administrators
            $parent = $this->getParent();

            if ($parent != false) {
                $parentWorkers = $parent->getWorkers($defaultValues);
                $workers['global'] = array_merge($workers['global'], $parentWorkers['local']);
                $workers['global'] = array_merge($workers['global'], $parentWorkers['global']);
            }
            else {
                //adding typoscript default workers to the recursive list of workers
                $TS_Workers = explode(',', $defaultValues['workers']);
                foreach ($TS_Workers as $uid) {
                    $newUser = t3lib_div::makeInstance('tx_paeproject_fe_user');
                    $newUser->load($uid);
                    $workers['global'][] = $newUser;
                }
            }
        }
        return $workers;
    }

    /**
     * Check if provided username as some edit rights, being an administrator or a worker.
     *
     * @param    string fe_username : login name of the user queried as supplied by $GLOBALS['TSFE']->fe_user->user['uid']
     * @return    boolean
     */
    function isEditingEnabled($fe_user_uid, $defaultValues)
    {
        $workers = $this->getWorkers($defaultValues);
        $admins = $this->getAdministrators($defaultValues);

        foreach ($admins as $admins_scope) {
            foreach ($admins_scope as $admin) {
                if ($admin->data['uid'] == $fe_user_uid) return 2;
            }
        }
        foreach ($workers as $workers_scope) {
            foreach ($workers_scope as $worker) {
                if ($worker->data['uid'] == $fe_user_uid) return 1;
            }
        }
        return 0;
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pae_project/class.tx_paeproject_project.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pae_project/class.tx_paeproject_project.php']);
}
