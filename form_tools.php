<?php

	/**
	 * Create a text input preceded by its label.
	 *
	 * @param	tslib_pibase the plugin object in which the form element is written
	 * @param	string llxmlName : translation key for entry name in the locallang.xml file
	 * @param	string componentName : name of the input box. Will be converted to tx_myextension[componentName] for usage with piVars
	 * @param	string value : content of the text input field
	 * @param	string class : class style to apply
	 * @return	string : the html text of the input and label
	 */
	function writeInput($plugin, $llxmlName, $componentName, $value, $class)
    {
		$fieldName   = $plugin->prefixId.'[' . $componentName . ']';

        $htmlCode = array();
        $htmlCode[]  = '<p>';
        $htmlCode[]  = '<label for="' . $fieldName . '">' . $plugin->pi_getLL($llxmlName) . '</label>';
        $htmlCode[]  = '<input name="' . $fieldName . '" value="' . $value . '" class="' . $class . '" />';
        $htmlCode[]  = '</p>';

        return implode(chr(10), $htmlCode);
	}

	/**
	 * Create a text input preceded by its label.
	 *
	 * @param	tslib_pibase the plugin object in which the form element is written
	 * @param	string llxmlName : translation key for entry name in the locallang.xml file
	 * @param	string componentName : name of the input box. Will be converted to tx_myextension[componentName] for usage with piVars
	 * @param	string timestamp : timestamp for the date
	 * @param	string class : class style to apply
	 * @return	string : the html text of the input and label
	 */
	function writeDate($plugin, $llxmlName, $componentName, $timestamp)
    {
		$fieldName_d = $plugin->prefixId . '['.$componentName . '_d]';
		$fieldName_m = $plugin->prefixId . '['.$componentName . '_m]';
		$fieldName_Y = $plugin->prefixId . '['.$componentName . '_Y]';

        $htmlCode = array();
        $htmlCode[]  = '<p>';
        $htmlCode[]  = '<label for="' . $fieldName . '">' . $plugin->pi_getLL($llxmlName) . '</label>';
        $htmlCode[]  = $plugin->pi_getLL('dd')   . ' <input name="' . $fieldName_d . '" value="' . date('d', $timestamp) . '" class="inputTiny" />';
        $htmlCode[]  = $plugin->pi_getLL('mm')   . ' <input name="' . $fieldName_m . '" value="' . date('m', $timestamp) . '" class="inputTiny" />';
        $htmlCode[]  = $plugin->pi_getLL('yyyy') . ' <input name="' . $fieldName_Y . '" value="' . date('Y', $timestamp) . '" class="inputTiny" />';
        $htmlCode[]  = '</p>';

		return implode(chr(10), $htmlCode);
	}

	/**
	 * Create a select box preceded by its label.
	 *
	 * @param	tslib_pibase the plugin object in which the form element is written
	 * @param	string llxmlName : translation key for entry name in the locallang.xml file
	 * @param	string componentName : name of the input box. Will be converted to tx_myextension[componentName] for usage with piVars
	 * @param	string value : 1=enabled 0=disabled
	 */
	function writeCheckBox($plugin, $llxmlName, $componentName,$value)
    {
		$htmlCode   = array();
		$fieldName  = $plugin->prefixId . '[' . $componentName . ']';

		$htmlCode[] = '<p>';
        $htmlCode[] = '<label for="' . $fieldName . '">' . $plugin->pi_getLL($llxmlName) . '</label>';
		$htmlCode[] = '<input type="checkbox" name="' . $fieldName . '" ' . (($value == 1) ? 'checked' : '') . ' />';
		$htmlCode[] = '</p>';

		return implode(chr(10), $htmlCode);
	}

	/**
	 * Create a select box preceded by its label.
	 *
	 * @param	tslib_pibase the plugin object in which the form element is written
	 * @param	string llxmlName : translation key for entry name in the locallang.xml file
	 * @param	string componentName : name of the input box. Will be converted to tx_myextension[componentName] for usage with piVars
	 * @param	string optionLabels : array of lavels for select box options
	 * @param	string optionValues : array of values for select box options (length must match sizeof(optionLabels))
	 * @param	string selectedValue : the value currently selected
	 * @param	string class : class style to apply
	 * @return	string : the html text of the input and label
	 */
	function writeSelect($plugin, $llxmlName, $componentName, $optionLabels, $optionValues, $selectedValue, $class)
    {
		$htmlCode   = array();
		$fieldName  = $plugin->prefixId . '[' . $componentName . ']';

		$htmlCode[] = '<p><label for="' . $fieldName . '">' . $plugin->pi_getLL($llxmlName) . '</label>';
		$htmlCode[] = '<select name="' . $fieldName . '" class="' . $class . '">';

		for ($i=0; $i<sizeof($optionValues); $i++) {
			$selected = ($optionValues[$i] == $selectedValue) ? ' selected="selected"' : '';
			$htmlCode[] = '<option value="' . $optionValues[$i] . '"' . $selected . '>' . $optionLabels[$i] . '</option>';
		}
		$htmlCode[] = '</select></p>';
		return implode(chr(10), $htmlCode);
	}

	/**
	 * Create a text area preceded by its label.
	 *
	 * @param	tslib_pibase the plugin object in which the form element is written
	 * @param	string llxmlName : translation key for entry name in the locallang.xml file
	 * @param	string componentName : name of the input box. Will be converted to tx_myextension[componentName] for usage with piVars
	 * @param	string formName : name the form containing the component
	 * @param	string value : content of the textarea field
	 * @return	array : the html text of the input and label
	 */
	function writeRTE($plugin, $llxmlName, $componentName, $formName, $value)
    {
		$htmlCode   = array();
		$fieldName  = $plugin->prefixId.'[' . $componentName . ']';
		$htmlCode[] ='<p><label for="' . $fieldName . '">' . $plugin->pi_getLL($llxmlName) . '</label><br />';
		$htmlCode[] ='<div class="rte">';
		$htmlCode[] ='<textarea id="textAreaID_' . $plugin->textAreaID . '" name="' . $fieldName . '" class="rte">'.$value.'</textarea>';
		$htmlCode[] ='</div>';
		$htmlCode[] ='</p>';
		$htmlCode[] ='
		<script type="text/javascript">
			// Create a new configuration object
			var config = new HTMLArea.Config();
			config.width = "400px";
			config.height = "200px";
			config.pageStyle =
			  "body { font-family: verdana,sans-serif;font-size:11px; } " +
			  "p { font-width: bold; } ";

			config.statusBar = false;
			config.toolbar = [
				["formatblock", "space", "justifyleft", "justifycenter", "justifyright", "justifyfull", "bold", "italic", "underline", "subscript", "superscript"],
			  	["outdent", "indent", "separator","forecolor", "hilitecolor", "textindicator", "separator", "createlink", "inserttable", "htmlmode"]
			];
		   	// Replace an existing textarea with an HTMLArea object having the above config.
			HTMLArea.replace("textAreaID_' . $plugin->textAreaID . '", config);
		</script>';

		$plugin->textAreaID++;
		return implode(chr(10), $htmlCode);
 	}

 	/**
	 * Create a multiple select .
	 *
	 * @param	tslib_pibase the plugin object in which the form element is written
	 * @param	string llxmlName : translation key for entry name in the locallang.xml file
	 * @param	string componentName : name of the input box. Will be converted to tx_myextension[componentName] for usage with piVars
	 * @param	array options : list of available options in the select box
	 * @param	string class : css style name to be applied to component
	 * @param	string formName : name the form containing the component
	 * @return	array : the html text of the input and label
	 */
	function writeDocumentList($plugin, $llxmlName, $componentName, $options, $class, $formName)
    {
		$htmlCode  = array();
		$fieldName = $plugin->prefixId . '[' . $componentName . ']';

		//remove void keys function explode() may return when supplied void string parameter
		if(isset($options[0]) && $options[0] == "") unset($options[0]);

		$htmlCode[] = '<script language="javascript">
		<!--

		function del(){
			document.forms[\'' . $formName . '\'][\'' . $plugin->prefixId . '[action]\'].value="deleteDoc";
		}

		function upload(){
			document.forms[\'' . $formName . '\'][\'' . $plugin->prefixId . '[action]\'].value="uploadDoc";
		}

		-->
		</script>';

		$htmlCode[] = '<p><label for="' . $fieldName . '">' . $plugin->pi_getLL($llxmlName) . '</label><table class="docListTable"><tr><td><table>';
		$htmlCode[] = '<tr><td class="top"><select name="' . $fieldName . '" size="6" multiple="multiple" class="' . $class . '">';

		foreach($options as $value){
			$htmlCode[] = '<option value="' . $value . '">' . $value . '</option>';
		}

		$htmlCode[] = '</select></td>';
		$htmlCode[] = '<td class="top"><input type="image" onclick="del();" src="' . t3lib_extMgm::siteRelPath($plugin->extKey) . 'res/del.gif" border="0" alt="' . $plugin->pi_getLL('delDoc') . '"></td>';
		$htmlCode[] = '</tr></table></td></tr>';

		$fieldName  = $plugin->prefixId . '[' . $componentName . '_upload]';
		$htmlCode[] = '<tr><td colspan="2"><div id="uploadDoc"><span class="label">' . $plugin->pi_getLL('addDoc') . '</span><br /><input type="file" name="' . $fieldName . '" /><input type="submit" value="' . $plugin->pi_getLL('upload') . '" onclick="upload()"></div></td></tr>';
		$htmlCode[] = '</table></p>';



		return implode(chr(10),$htmlCode);
	}

	/**
	 * Create double multiple select box with selected values in the left box and available values in the right box.
	 *
	 * @param	tslib_pibase the plugin object in which the form element is written
	 * @param	string llxmlName : translation key for entry name in the locallang.xml file
	 * @param	string componentName : name of the input box. Will be converted to tx_myextension[componentName] for usage with piVars
	 * @param	array selectedValues : list of values for selected options for the object displayed
	 * @param	array selectedLabels : list of labels for selected options for the object displayed
	 * @param	array availableValues : list of values for available options for the object displayed
	 * @param	array availableLabels : list of labels for available options for the object displayed
	 * @param	string class : css style name to be applied to component
	 * @param	string formName : name the form containing the component
	 * @return	array : the html text of the input and label
	 */
	function writeDoubleList($plugin, $llxmlName, $componentName, $selectedValues, $selectedLabels, $availableValues, $availableLabels, $class, $formName, $size=7)
    {
		$htmlCode = array();

        // keys of entries already selected on component display.
		$baseKeys = array();
		$fieldID  = $componentName.'_selected';

		//remove void keys function explode() may return when supplied void string parameter
		if(isset($selectedValues[0]) && $selectedValues[0] == ""){
			unset($selectedValues[0]);
			unset($selectedLabels[0]);
		}
		if(isset($availableValues[0]) && $availableValues[0] == ""){
			unset($availableValues[0]);
			unset($availableLabels[0]);
		}

		$htmlCode[] = '<label for="' . $fieldName . '_selected">' . $plugin->pi_getLL($llxmlName) . '</label><table class="docListTable"><tr><td>';
		$htmlCode[] = '<select id="' . $fieldID . '" size="' . $size . '" multiple="multiple" class="' . $class . '">';

		for($i=0; $i<sizeof($selectedValues); $i++){
			$htmlCode[] = '<option value="' . $selectedValues[$i] . '">' . $selectedLabels[$i] . '</option>';
		}
		$htmlCode[] = '</select></td>';
		$htmlCode[] = '
		<td class="top">
			<img class="imgButton" onclick="' . $componentName . 'Add();" src="' . t3lib_extMgm::siteRelPath($plugin->extKey) . 'res/arrow.gif" border="0" alt="' . $plugin->pi_getLL('add') . '"><br />
			<img class="imgButton" onclick="' . $componentName . 'Del();" src="' . t3lib_extMgm::siteRelPath($plugin->extKey) . 'res/del.gif" border="0" alt="' . $plugin->pi_getLL('delete') . '">
		</td>';

		$fieldID    = $componentName . '_available';
		$htmlCode[] = '<td><select id="' . $fieldID . '" size="' . $size . '" multiple="multiple" class="' . $class . '">';

		for($i=0; $i<sizeof($availableValues); $i++) {
			$htmlCode[] = '<option value="' . $availableValues[$i] . '">' . $availableLabels[$i] . '</option>';
		}

		$htmlCode[] = '</select></td>';
		$htmlCode[] = '</tr></table>';

		$htmlCode[] = '<input type="hidden" id="' . $componentName . '_final_selection" name="' . $plugin->prefixId . '[' . $componentName . '_final_selection]" value="' . $project->data['uid'] . '">';

		$htmlCode[] = '<script language="javascript">
		<!--

		var ' . $componentName . '_selectedList = document.getElementById(\'' . $componentName . '_selected\');
		var ' . $componentName . '_availableList = document.getElementById(\'' . $componentName . '_available\');
		var ' . $componentName . '_final_selection = document.getElementById(\'' . $componentName . '_final_selection\');

		function ' . $componentName . 'updateHiddenField(){
			' . $componentName . '_values = [];
			for(i=0; i<' . $componentName . '_selectedList.options.length; i++){
				' . $componentName . '_values[i]=' . $componentName . '_selectedList.options[i].value;
			}
			' . $componentName . '_values.sort( function (a,b) { return a-b });  // Sort Numerically
			' . $componentName . '_final_selection.value=' . $componentName . '_values.join(\',\');
		}

		function ' . $componentName . 'Del(){
			deletedValue = ' . $componentName . '_selectedList.selectedIndex;
			' . $componentName . '_selectedList.options[deletedValue]=null;
			' . $componentName . 'updateHiddenField();
		}

		function ' . $componentName . 'Add(){
			selectedIndex = ' . $componentName . '_availableList.selectedIndex;
			selectedText = ' . $componentName . '_availableList.options[selectedIndex].text;
			selectedValue = ' . $componentName . '_availableList.options[selectedIndex].value;

			//check if value doesn\'t already exist
			update = true;
			for(i=0; i<' . $componentName . '_selectedList.options.length; i++){
				if(' . $componentName . '_selectedList.options[i].text == selectedText)update = false;
			}

			if(update){
				' . $componentName . '_selectedList.options[' . $componentName . '_selectedList.options.length]= new Option(selectedText,selectedValue);

				' . $componentName . 'updateHiddenField();
			}
		}

		' . $componentName . 'updateHiddenField();
		-->
		</script>';

		return implode(chr(10), $htmlCode);
	}
