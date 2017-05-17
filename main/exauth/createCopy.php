<?php
require_once '../inc/global.inc.php';
require_once 'fpdf.php';

$dbApplicationId  = $_GET["id"];

$sql = "select * from exam_applications where confirmation_id=\"$dbApplicationId\" ";
$result = Database::query($sql);

if (Database::num_rows($result) == 1) {
        $row = Database :: fetch_array($result, 'ASSOC');
        $dbApplicationId  = $row['id'];
        $firstname  = $row['firstname'];
        $lastname  = $row['lastname'];
        $salutation  = $row['salutation'];
        $address  = $row['address'];
        $postcode  = $row['postcode'];
        $city  = $row['city'];
        $country  = $row['country'];
        $telephone  = $row['telephone'];
        $country_code  = $row['country_code'];
        $hours_from  = $row['hours_from'];
        $hours_to  = $row['hours_to'];
        $email  = $row['email'];

	$pdf = new FPDF();
	$pdf->AddPage();
	$pdf->Image('pdf_header.png');
	$pdf->SetFont('Arial','BU',16);
	$pdf->Cell(100,10,'Exam Registration Details',0,1);
	$pdf->SetFont('Arial','BU',14);
	$pdf->Cell(100,10,'Identification',0,1);
	$pdf->SetFont('Arial','B',12);
	$pdf->Cell(100,7,'Title: '.$salutation,0,1);
	$pdf->Cell(100,7,'First Name: '.$firstname,0,1);
	$pdf->Cell(100,7,'Last Name: '.$lastname,0,1);
	$pdf->SetFont('Arial','I',10);
	$pdf->SetFont('Arial','BU',14);
	$pdf->Cell(100,10,'Communication Adress',0,1);
	$pdf->SetFont('Arial','B',12);
	$pdf->Cell(100,7,'Address: '.$address,0,1);
	$pdf->Cell(100,7,'Post Code: '.$postcode,0,1);
	$pdf->Cell(100,7,'City: '.$city,0,1);
	$pdf->Cell(100,7,'Country: '.$country,0,1);
	$pdf->SetFont('Arial','I',10);
	$pdf->SetFont('Arial','BU',14);
        $pdf->Cell(100,10,'Telephone and Email',0,1);
	$pdf->SetFont('Arial','B',12);
	$pdf->Cell(100,7,'Telephone: '.$telephone,0,1);
	$pdf->Cell(100,7,'Country Code: '.$country_code,0,1);
	$pdf->Cell(100,7,'Business Hours: From '.$hours_from.' To '.$hours_to,0,1);
	$pdf->Cell(100,7,'Email: '.$email,0,1);
	$pdf->SetFont('Arial','I',10);
	$pdf->Ln(10);
	$pdf->SetFont('Arial','B',11);
	$pdf->MultiCell(150,5,'I, the undersigned, hereby confirm that I have read and accepted the terms of engagement of the online certification examination and I wish to apply for the online certification examination of the International Academy of Classical Homeopathy');
	$pdf->Ln(10);
	$pdf->Cell(100,10,'Date: ');
	$pdf->Cell(100,10,'Signature: ');
	$pdf->Output();
       }




?>
