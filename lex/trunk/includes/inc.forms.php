<?php
require_once('HTML/QuickForm.php');

$form = new HTML_QuickForm();
$renderer =& $form->defaultRenderer();

$renderer->setFormTemplate('<form{attributes}><table id="contentTable">{content}</table></form>');

// Use a table layout for elements to get a proper alignment
$renderer->setElementTemplate(
 '<tr>
	  <td align="left" valign="top" width="30%">{label}
		  <!-- BEGIN required --><span style="color: red">*</span><!-- END required -->
    </td>
	  <td align="left">{element}
		  <!-- BEGIN error --><br /><span style="color: red">{error}</span><!-- END error -->
	  </td>
	</tr>'
);

// Header text should span over all (2) columns
$renderer->setHeaderTemplate(
 '<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2" style="font-weight:bold">{header}</td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>'	
);

$form->setRequiredNote('<br><tr><td><font size=2>* '.$lng_denotes_required_field.'</font></td></tr>');

?>
