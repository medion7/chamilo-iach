<?php
// Chamilo LMS
// See license terms in chamilo/documentation/license.txt

// Training tools
// Wiki

// For more information: http://docs.fckeditor.net/FCKeditor_2.x/Developers_Guide/Configuration/Configuration_Options

// Hide/show SpellCheck buttom
if ((api_get_setting('allow_spellcheck') == 'true')) {
	$VSpellCheck='SpellCheck';
}
else{
	$VSpellCheck='';	
}

// This is the visible toolbar set when the editor has "normal" size.
$config['ToolbarSets']['Normal'] = array(
	array('FitWindow','Save','NewPage','Templates','PageBreak','Preview','-','PasteText','-','Undo','Redo','-','SelectAll','-','Find'),
	array('Wikilink','Link','Unlink','Anchor'),
	array('Image','flvPlayer','Flash','EmbedMovies','YouTube','MP3','mimetex','asciimath','fckeditor_wiris_openFormulaEditor','fckeditor_wiris_openCAS'),
	array('Table','Rule','Smiley','SpecialChar','googlemaps'),
	array('FontFormat','FontName','FontSize'),
	array('Bold','Italic','Underline'),
	array('Subscript','Superscript','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyFull','-','OrderedList','UnorderedList','-','Outdent','Indent','-','TextColor','BGColor', $VSpellCheck),
	array('Source')
);

// This is the visible toolbar set when the editor is maximized.
// If it has not been defined, then the toolbar set for the "normal" size is used.
/*
$config['ToolbarSets']['Maximized'] = array(
	array('FitWindow','-') // ...
);
*/

// Sets whether the toolbar can be collapsed/expanded or not.
// Possible values: true , false
//$config['ToolbarCanCollapse'] = true;

// Sets how the editor's toolbar should start - expanded or collapsed.
// Possible values: true , false
//$config['ToolbarStartExpanded'] = true;

//This option sets the location of the toolbar.
// Possible values: 'In' , 'None' , 'Out:[TargetId]' , 'Out:[TargetWindow]([TargetId])'
//$config['ToolbarLocation'] = 'In';

// A setting for blocking copy/paste functions of the editor.
// This setting activates on leaners only. For users with other statuses there is no blocking copy/paste.
// Possible values: true , false
//$config['BlockCopyPaste'] = false;

// A setting for force paste as plain text.
if ((api_get_setting('force_wiki_paste_as_plain_text') == 'true')) {
	$config['ForcePasteAsPlainText'] = true;
}
else{
	$config['ForcePasteAsPlainText'] = false;
}

// Here new width and height of the editor may be set.
// Possible values, examples: 300 , '250' , '100%' , ...
//$config['Width'] = '100%';
//$config['Height'] = '400';
