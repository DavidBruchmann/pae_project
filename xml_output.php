<?php

/***************************************************************
 * Copyright notice
 *
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is 
  * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/** 
 * XML page generation script for the 'pae_project' extension.
 *
 * @author		Alban Cousinié (ace@mind2machine.com)
 * @version		1.0
 */
	
//include class tslib_cObj 
require_once(PATH_tslib . 'class.tslib_content.php');
require_once(t3lib_extMgm::extPath('pae_project', 'pi1/class.tx_paeproject_pi1.php'));
require_once(t3lib_extMgm::extPath('pae_project', 'class.tx_paeproject_project.php'));
require_once(t3lib_extMgm::extPath('pae_project', 'class.tx_paeproject_exception.php'));
require_once(t3lib_extMgm::extPath('pae_project', 'date_tools.php'));
require_once(t3lib_extMgm::extPath('pae_project', 'form_tools.php'));
require_once(t3lib_extMgm::extPath('pae_project', 'project_tools.php'));

class tx_paeproject_xmloutput
{
	//constructor;
	function tx_paeproject_xmloutput()
    {
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');
	}
	
	/**
	 * Creates XML output for Flash.
	 * 
	 * This function creates an XML output wich can be used by a SWF movie.
	 * 
	 * @return		The XML content.
	 */
	function writeXML()
    {
		//initializing session
		//print_r($GLOBALS["TSFE"]->fe_user);
		$session = $GLOBALS["TSFE"]->fe_user->getKey('ses', 'tx_paeproject');
		
		//overwrite session data since it has proven sometimes not to be correct
		/*$session->prefixId = $_REQUEST("prefixId");
		$session->pluginPageUID = $_REQUEST("id");
		$session->selectedPids = $_REQUEST("selectedPids");*/
		
		// XML Storage
		$xml =  array();
		
		$xml[] = '<?xml version="1.0" encoding="' . $GLOBALS['TSFE']->tmpl->setup['config.']['metaCharset'] . '" standalone="yes" ?>';
		$xml[] = '<pae_project>';
		$xml = array_merge($xml, listAllProjectsXML(NULL, $session));
		$xml[] = '</pae_project>';
					
		// Return XML code
		return implode(chr(10), $xml);
	}
}	

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pae_project/xml_output.php'])	{
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pae_project/xml_output.php']);
}
	
$xmloutput = new tx_paeproject_xmloutput();
echo($xmloutput->writeXML());
exit();
