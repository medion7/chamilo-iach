<?php
function export_pdf(){
	
	$html = '<html><body>';
	$html .= '<h2>Lia</h2><hr><br><br>';
	$html .= '</body></html>';
	
	$course_code = api_get_course_id();
    $pdf = new PDF();        
	$pdf->content_to_pdf($html, $css, get_lang('Glossary').'_'.$course_code, $course_code);
}
?>