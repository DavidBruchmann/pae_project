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

/**
 * Class that manages custom database records.
 *
 * @author      Communauté d'agglomération du pays d'Aubagne et de l'Etoile, Mind2Machine <ace@mind2machine.com>
 * @package     TYPO3
 * @subpackage  tx_paeproject
 */
class tx_paeproject_dbrecord
{
    /**
     * @var array
     */
    var $data;

    /**
     * @var string
     */
    var $tableName = "";


    /**
     * Constructor
     */
    function tx_paeproject_dbrecord()
    {

    }

    /**
     * initializes basic values for a new record
     *
     * @param  int pid       sets the parent record (ex:sysfolder uid) where the record is contained
     * @param  int user_uid  uid of the backend user who has created the record
     */
    function init($pid, $user_uid)
    {

    }

    /**
     * loads record data from database
     *
     * @param int uid
     */
    function load($uid)
    {
        $queryParts = array(
            'SELECT' => "*",
            'FROM' => $this->tableName,
            'WHERE' => "uid='" . $uid . "' AND deleted=0",
        );

        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            $queryParts['SELECT'],
            $queryParts['FROM'],
            $queryParts['WHERE']
        ) or die("ERROR: " . $this->tableName . ".load(req1): " . $GLOBALS['TYPO3_DB']->sql_error());


        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
        if (count($row)>0) {
            $this->data = $row;
        }
    }


    // Stores project data in the database and returns the uid of the record when successful, 0 when it fails
    function store()
    {
        $status=0;

        //updating last modification timestamp
        $this->data['tstamp'] = time();

        //verifying the record exists in the database
        $queryParts = array(
            'SELECT' => "COUNT(uid)",
            'FROM' => $this->tableName,
            'WHERE' => "uid='" . $this->data['uid'] . "'",
        );
        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery (
            $queryParts['SELECT'],
            $queryParts['FROM'],
            $queryParts['WHERE']
        ) or die("ERROR: " . $this->tableName . ".store(req1): " . $GLOBALS['TYPO3_DB']->sql_error());

        $row = $GLOBALS['TYPO3_DB']->sql_fetch_row($result);

        if ($row[0]>0){
            //updating object
            $sql = "UPDATE " . $this->tableName . " SET ";
            $i = 0;
            reset($this->data);
            foreach ($this->data as $key => $value) {
                if ($key != $this->data['uid']) {
                    $sql .= $key . "='" . $GLOBALS['TYPO3_DB']->quoteStr($value, $this->tableName);
                    if ($i < sizeof($this->data) - 1) {
                        $sql .= "', ";
                    }
                    elseif ($i == sizeof($this->data) - 1) {
                        $sql .= "'";
                    }
                    else {
                        $sql .= "'; ";
                    }
                }
                $i++;
            }
            $sql .= " WHERE uid='" . $this->data['uid'] . "';";

            if ($GLOBALS['TYPO3_DB']->sql(TYPO3_db, $sql)) {
                $status=$this->data['uid'];
            }
            else{
                $status=0;
                die("ERROR: " . $this->tableName . ".store(req2): " .$GLOBALS['TYPO3_DB']->sql_error());
            }
        }
        else {
            //inserting new object
            if ($this->getProjectCount()>0){
                $queryParts = array(
                    'SELECT' => "MAX(uid), MAX(sorting)",
                    'FROM' => $this->tableName,
                    'WHERE' => "1",
                );

                $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery (
                    $queryParts['SELECT'],
                    $queryParts['FROM'],
                    $queryParts['WHERE']
                ) or die("ERROR: " . $this->tableName . ".store(req3): " . $GLOBALS['TYPO3_DB']->sql_error());

                $row = $GLOBALS['TYPO3_DB']->sql_fetch_row($result);

                $maxID      = $row[0];
                $maxSorting = $row[1];
            }
            else{
                $maxID = 0;
                $maxSorting = 0;
            }
            $this->data['uid']     = ++$maxID;
            $this->data['sorting'] = $maxSorting * 2; //Ou alors on prend le sorting du parent

            $sql = "INSERT INTO " . $this->tableName . " SET ";
            $i = 0;
            reset($this->data);
            foreach ($this->data as $key => $value) {
                if ($key == "crdate") {
                    $sql .= "crdate=" . time() . ", ";
                }
                else {
                    $sql .= $key . "='" . $GLOBALS['TYPO3_DB']->quoteStr($value, $this->tableName) . (($i < sizeof($this->data) - 1) ? "', " : "';");
                }
                $i++;
            }

            //echo $sql."<br>";die();
            if ($GLOBALS['TYPO3_DB']->sql(TYPO3_db, $sql)) {
                $status = $this->data['uid'];
            }
            else {
                $status=0;
                die("ERROR: " . $this->tableName . ".store(req4): " . $GLOBALS['TYPO3_DB']->sql_error());
            }

        }
        return $status;
    }

    // Deletes current object
    function delete()
    {
        $this->data['deleted'] = 1;
        $this->store();
    }

    //Checks if current object exists in the database
    function exists()
    {
        $queryParts = array(
            'SELECT' => "COUNT(uid)",
            'FROM' => $this->tableName,
            'WHERE' => "uid='" . $this->data['uid'] . "' AND deleted=0",
        );

        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            $queryParts['SELECT'],
            $queryParts['FROM'],
            $queryParts['WHERE']
        ) or die("ERROR: " . $this->tableName . ".exists(req1): " . $GLOBALS['TYPO3_DB']->sql_error());

        $row = $GLOBALS['TYPO3_DB']->sql_fetch_row($result);

        return ($row[0]>0);
    }

    function toHTML()
    {
        $queryParts = array(
            'SELECT' => "*",
            'FROM' => $this->tableName,
            'WHERE' => "uid=" . $this->data['uid'],
        );

        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery (
            $queryParts['SELECT'],
            $queryParts['FROM'],
            $queryParts['WHERE']
        ) or die("ERROR: " . $this->tableName . ".toHTML(req1): " . $GLOBALS['TYPO3_DB']->sql_error());

        $html = array();
        $html[] = "<p>\n";
        $object = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
        foreach ($object as $key => $value) {
            $html[] = $key . " : " . $value . "<br />";
        }
        $html[] = "</p>\n";
        return implode(chr(10), $html);
    }

    //retourne le nombre d'élements non supprimés dans la table
    function getProjectCount()
    {
        $queryParts = array(
            'SELECT' => "COUNT(uid)",
            'FROM' => $this->tableName,
            'WHERE' => "deleted=0",
        );

        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            $queryParts['SELECT'],
            $queryParts['FROM'],
            $queryParts['WHERE']
        ) or die("ERROR: " . $this->tableName . ". getNumContents(req1): " . $GLOBALS['TYPO3_DB']->sql_error());


        $row = $GLOBALS['TYPO3_DB']->sql_fetch_row($result);
        return $row[0];
    }


    function moveUp()
    {
        $queryParts = array(
            'SELECT' => "uid, sorting, title",
            'FROM' => $this->tableName,
            'WHERE' => "deleted=0",
            'ORDER BY' => "sorting",
        );

        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery (
            $queryParts['SELECT'],
            $queryParts['FROM'],
            $queryParts['WHERE'],
            '',
            $queryParts['ORDER BY']
        ) or die("ERROR: " . $this->tableName . ".moveUp(req1): " . $GLOBALS['TYPO3_DB']->sql_error());

        $recordsList = array();
        $recordsSorting = array();

        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_row($result)) {
            $recordsList[] = $row[0];
            $recordsSorting[] = $row[1];
        }

        $thisPos = array_search($this->data['uid'], $recordsList);

        $currentSorting = 0;
        //if we are not already the top value
        if ($thisPos > 0) {

            //rewritting sorting values
            foreach ($recordsList as $position => $record_uid) {
                //swapping records
                if ( $position == $thisPos - 1) {
                    $order = $thisPos;
                }
                elseif ($position == $thisPos) {
                    $order = $thisPos-1;
                }
                else {
                    $order = $position;
                }

                //reordering objects
                $sql = "UPDATE " . $this->tableName . " SET sorting=" . pow(2,$order) . " WHERE uid='" . $record_uid . "';";

                if ($GLOBALS['TYPO3_DB']->sql(TYPO3_db, $sql)) {
                    $status = $this->data['uid'];
                }
                else {
                    $status = 0;
                    die("ERROR: " . $this->tableName . ".moveUp(req2): " . $GLOBALS['TYPO3_DB']->sql_error());
                }
            }
        }
    }

    function moveDown()
    {
        $queryParts = array(
            'SELECT' => "uid, sorting, title",
            'FROM' => $this->tableName,
            'WHERE' => "deleted=0",
            'ORDER BY' => "sorting",
        );

        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery (
            $queryParts['SELECT'],
            $queryParts['FROM'],
            $queryParts['WHERE'],
            '',
            $queryParts['ORDER BY']
        ) or die("ERROR: "  . $this->tableName . ".moveDown(req1): " . $GLOBALS['TYPO3_DB']->sql_error());

        $recordsList = array();
        $recordsSorting = array();

        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_row($result)) {
            $recordsList[] = $row[0];
            $recordsSorting[] = $row[1];
        }

        $thisPos = array_search($this->data['uid'], $recordsList);

        $currentSorting = 0;
        //if we are not already the top value
        if ($thisPos > 0) {

            //rewritting sorting values
            foreach ($recordsList as $position => $record_uid) {
                //swapping records
                if ( $position == $thisPos+1) {
                    $order = $thisPos;
                }
                else if ( $position == $thisPos) {
                    $order = $thisPos+1;
                }
                else {
                    $order = $position;
                }

                //reordering objects
                $sql = "UPDATE " . $this->tableName . " SET sorting=" . pow(2,$order) . " WHERE uid='" . $record_uid . "';";

                if ($GLOBALS['TYPO3_DB']->sql(TYPO3_db, $sql)) {
                    $status = $this->data['uid'];
                }
                else {
                    $status = 0;
                    die("ERROR: " . $this->tableName . ".moveDown(req2): " . $GLOBALS['TYPO3_DB']->sql_error());
                }
            }
        }
    }

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pae_project/class.tx_paeproject_dbrecord.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pae_project/class.tx_paeproject_dbrecord.php']);
}
