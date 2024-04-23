<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once dirname(__FILE__) . '/tcpdf/tcpdf.php';

class Pdf extends TCPDF
{
    function __construct()
    {
        parent::__construct();
    }
	var $CI;
	function createpdf($html,$pdfname,$title='LP',$output='F'){ 
		$ci = get_instance(); 
		try {
			$pdf = new Pdf('P', 'mm', 'A4', true, 'UTF-8', false); 
			$pdf->SetTitle($title);
			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);
			$pdf->SetAutoPageBreak(true);
			$pdf->SetAuthor('Portal');
			$lg = Array();
			$lg['a_meta_charset'] = 'UTF-8'; 
			$pdf->setLanguageArray($lg);
			$pdf->SetMargins(7,10,10);
			$pdf->SetHeaderMargin(5);
			$pdf->SetFooterMargin(10);   
			$pdf->SetAutoPageBreak(TRUE);       
			$pdf->SetDisplayMode('real', 'default');    
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
			// convert TTF font to TCPDF format and store it on the fonts folder
 			//$Sanskrit = TCPDF_FONTS::addTTFfont('public/assets/fonts/Sanskrit.ttf', 'TrueTypeUnicode','',32); 
			//$TwCenMT = TCPDF_FONTS::addTTFfont('public/assets/fonts/TwCenMT.ttf', 'TrueTypeUnicode','',32); 
			//$pdf->SetFont('dejavusans', '', 11);
			//$pdf->AddFont('sanskrit','','sanskrit.php');
			//$pdf->SetFont('Sanskrit'); 
			//$pdf->AddFont('twcenmt','','twcenmt.php');
			//$pdf->SetFont('twcenmt', '', 13); 
			// convert TTF font to TCPDF format and store it on the fonts folder
			//$Sanskrit = TCPDF_FONTS::addTTFfont('public/assets/fonts/Sanskrit.ttf', 'TrueTypeUnicode', '', 96);
 			//$pdf->SetFont($Sanskrit, '', 14, '', false);
			//$TwCenMT = TCPDF_FONTS::addTTFfont('public/assets/fonts/TwCenMT.ttf', 'TrueTypeUnicode', '', 96);
 			//$pdf->SetFont($TwCenMT, '', 14, '', false);
			$pdf->AddPage(); 
			$pdf->WriteHTML($html);
			ob_end_clean();
			$pdf->Output($pdfname, $output);
		}
		catch(TCPDF_exception $e) {
			return $e;exit;
		} 
    }
}
