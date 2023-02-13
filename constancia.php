<?php

require('vendor\fpdf184\fpdf.php');
include 'database_connection.php';
include 'function.php';


class PDF extends FPDF{
    function Header(){
        $this-> SetFont('Arial','B',18);
        $this->Cell(60);
        $this->Cell(70,10,iconv('UTF-8', 'windows-1252', "Constancia de no adeudo"),0,0,'C');
        $this->Ln(20);
        $this->Cell(40,10,iconv('UTF-8', 'windows-1252', "Tus prestamos"),0,0,'C');
        $this->Ln(20);
    }
    function Footer(){
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,utf8_decode('Página').$this->PageNo().'/{nb}',0,0,'C');

    }
};
$mysqli = new mysqli ("localhost","root", "", "lms");
$consulta = "
	SELECT * FROM lms_issue_book 
	WHERE lms_issue_book.user_id = '".$_SESSION['user_id']."' 
	ORDER BY lms_issue_book.issue_book_id DESC
";

$resultado = $mysqli->query($consulta);

$deuda = Count_total_fines_user($connect,$_SESSION['user_id']);


$pdf = new PDF();
$pdf->AliasNBPages();
$pdf->AddPage();
$pdf->SetFont('Arial','B',10);

    $pdf->Cell(40,10,'ISBN',1,0,'C',0);
    $pdf->Cell(40,10,'Estado',1,0,'C',0);
    $pdf->Cell(50,10,'Fecha del prestamo',1,0,'C',0);
    $pdf->Cell(60,10,'Deuda generada',1,1,'C',0);
while ($row = $resultado->fetch_assoc()) {
    $pdf->Cell(40,10,$row['book_id'],1,0,'C',0);
    switch ($row['book_issue_status']) {
        case 'Not Return':
            $pdf->Cell(40,10,'Sin entregar',1,0,'C',0);
            break;
        case 'Return':
            $pdf->Cell(40,10,'Entregado',1,0,'C',0);
            break;
        case 'Issue':
            $pdf->Cell(40,10,'Prestado',1,0,'C',0);
            break;
        default:
            echo "?????????";
            break;
    }
    //$pdf->Cell(40,10,$row['book_issue_status'],1,0,'C',0);
    $pdf->Cell(50,10,$row['issue_date_time'],1,0,'C',0);
    
    $pdf->Cell(60,10,$row['book_fines'],1,1,'C',0);
}
if ($deuda>0) {
    $pdf->Cell(40,10,iconv('UTF-8', 'windows-1252', "Tu deuda es de $deuda$, debes pagar esta suma para validar esta constancia"));
}else{
    $pdf->Cell(40,10,iconv('UTF-8', 'windows-1252', "El sistema confirma que no tienes deudas pendientes"));
}

$pdf->Image('asset\img\logo.png',10,12,30,0,'','https://i.ytimg.com/vi/E20Qg8LUurA/maxresdefault.jpg');

$pdf->Output();
?>