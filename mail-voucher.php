<?php

require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

if (!isset($_POST['firstName'], $_POST['lastName'], $_POST['recipientFirstName'], $_POST['recipientLastName'], $_POST['email'], $_POST['voucherName'])) {
    $data['result'] = "error";
    $data['info'] = "Nie wszystkie wymagane pola formularza zostały wypełnione.";
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

// Pobierz dane z formularza
$firstName = htmlspecialchars(trim($_POST['firstName']), FILTER_SANITIZE_SPECIAL_CHARS);
$lastName = htmlspecialchars(trim($_POST['lastName']), FILTER_SANITIZE_SPECIAL_CHARS);
$recipientFirstName = htmlspecialchars(trim($_POST['recipientFirstName']), FILTER_SANITIZE_SPECIAL_CHARS);
$recipientLastName = htmlspecialchars(trim($_POST['recipientLastName']), FILTER_SANITIZE_SPECIAL_CHARS);
$email =filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $data['result'] = "error";
    $data['info'] = "Invalid email address.";
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}


$voucherName = $_POST['voucherName'];

// Utwórz treść wiadomości
$mailToSeller = "
<h3>Szanowny Zespole,</h3>
<p>Zarejestrowano nową rezerwacje Vouchera. Oto szczegóły:</p>
<p><strong>Imię Zamówcy:</strong> {$firstName} </p>
<p><strong>Nazwisko Zamówcy:</strong> {$lastName} </p>
<p><strong>E-mail Zamówcy:</strong> {$email} </p>
<p><strong>Dla kogo rezerwacja Vouchera:</strong> {$recipientFirstName} {$recipientLastName} </p>
<p><strong>Wybrany voucher:</strong> $voucherName</p>          
<p>Klient został poinformowany o złożonej rezerwacji poprzez wysłanie e-maila 
z potwierdzeniem. W razie potrzeby proszę o kontakt z nim.</p>
<p>Dziękujemy!</p>";


$mailToClient = "
<h3> Drogi Kliencie, </h3>
<p>Dziękujemy za zarezerwowanie vouchera w MUV House of Hair.</p>
<p>Jesteśmy bardzo wdzięczni za Państwa zaufanie.</p>
<p><strong>Szczegóły rezerwacji vouchera:</strong></p>
<p><strong>Imię i nazwisko osoby, dla której przeznaczony jest voucher:</strong> $recipientFirstName . $recipientLastName</p>
<p><strong>Wybrany voucher:</strong> $voucherName</p>
<p>Voucher jest ważny przez 6 miesięcy od daty rezerwacji.</p>
<p>Płatności można dokonać przy odbiorze zamówionych produktów w salone lub 
przelewem bankowym na poniższe dane:</p>
<p>Nr rachunku bankowego:<br>
<strong>68 1140 2004 0000 3402 8150 7493</strong><br>
MUV House of Hair <br>
JOANNA GROCHOWA - TWARDA <br>
Leszno, ul. Jana Sobieskiego 2B <br>
64-100</p>
<p>Po opłaceniu prosimy o wysłanie potwierdzenia przelewu na adres e-mail: joanna.grochowa@icloud.com.</p>
<p>Jak otrzymamy potwierdzenie, to wyślemy do Państwa wiadomość e-mail z Voucherem w formacie PDF, 
który można wydrukować i podarować bliskiej osobie 😊</p>
<p>Dziękujemy za zaufanie i czekamy na Państwa kolejne wizyty w MUV Salon Piękności.</p>
<p>Z poważaniem,</p>
<p>[Zespół <strong>MUV HOUSE OF HAIR</strong>]</p>";


$mail = new PHPMailer\PHPMailer\PHPMailer();
$mail2 = new PHPMailer\PHPMailer\PHPMailer();

// Konfiguracja SMTP
$mail->isSMTP();
$mail->CharSet = "UTF-8";
$mail->SMTPAuth = true;
$mail->Debugoutput = function($str, $level) {$GLOBALS['data']['debug'][] = $str;};
$mail->Host = 'mail.muvhouseofhair.pl';
$mail->Username = 'kontakt@muvhouseofhair.pl';
$mail->Password = 'Mozumo79';
$mail->SMTPSecure = 'ssl';
$mail->Port      = 465;
$mail->setFrom('kontakt@muvhouseofhair.pl');
$mail->addAddress('joanna.grochowa@icloud.com');

$mail2->isSMTP();
$mail2->CharSet = "UTF-8";
$mail2->SMTPAuth = true;
$mail2->Debugoutput = function($str, $level) {$GLOBALS['data']['debug'][] = $str;};
$mail2->Host = 'mail.muvhouseofhair.pl';
$mail2->Username = 'kontakt@muvhouseofhair.pl';
$mail2->Password = 'Mozumo79';
$mail2->SMTPSecure = 'ssl';
$mail2->Port      = 465;
$mail2->setFrom('kontakt@muvhouseofhair.pl');
$mail2->addAddress($email);


// Ustawienie wiadomości jako HTML
$mail->isHTML(true);
$mail->Subject = "Potwierdzenie Nowej Rezerwacji Vouchera";
$mail->Body = $mailToSeller;

$mail2->isHTML(true);
$mail2->Subject = "Potwierdzenie rezerwacji Vouchera MUV House Of Hair";
$mail2->Body = $mailToClient;

// Wyślij wiadomość
if ($mail->send()) {
    $data['result'] = "success";
    $data['info'] = "Rezerwacja została pomyślnie wysłana! Sprawdź e-mail.";
} else {
    $data['result'] = "error";
    $data['info'] = "Wiadomość nie została wysłana. Wystąpił błąd podczas wysyłania wiadomości.";
    $data['desc'] = "Przyczyna: {$mail->ErrorInfo}";
}

if ($mail2->send()) {
    $data['result'] = "success";
    $data['info'] = "Rezerwacja została pomyślnie wysłana! Sprawdź e-mail.";
} else {
    $data['result'] = "error";
    $data['info'] = "Wiadomość nie została wysłana. Wystąpił błąd podczas wysyłania wiadomości.";
    $data['desc'] = "Przyczyna: {$mail2->ErrorInfo}";
}

header('Content-Type: application/json');
echo json_encode($data);

exit();
}

?>
