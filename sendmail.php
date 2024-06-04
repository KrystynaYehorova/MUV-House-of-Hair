<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if (isset($_POST['firstName'], $_POST['lastName'], $_POST['email'], $_POST['cartItemsHTML'], $_POST['cartTotalPriceHTML'])) {
       
        $firstName = htmlspecialchars(trim($_POST['firstName']), FILTER_SANITIZE_SPECIAL_CHARS);
        $lastName = htmlspecialchars(trim($_POST['lastName']), FILTER_SANITIZE_SPECIAL_CHARS);
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $data['result'] = "error";
            $data['info'] = "Invalid email address.";
            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        }

        $cartItemsHTML = $_POST['cartItemsHTML'];
        $cartTotalPriceHTML = $_POST['cartTotalPriceHTML'];
        $faktura = isset($_POST['FakturaCheckbox']) ? 'Tak' : 'Nie';
        $delivery = isset($_POST['deliveryCheckbox']) ? true : false;

        $mailToClient = new PHPMailer();
        $mailToSeller = new PHPMailer();

        try {
    
        // Konfiguracja SMTP
        $mailToSeller->isSMTP();
        $mailToSeller->CharSet = "UTF-8";
        $mailToSeller->SMTPAuth = true;
        $mailToSeller->Debugoutput = function($str, $level) {$GLOBALS['data']['debug'][] = $str;};
        $mailToSeller->Host = 'mail.muvhouseofhair.pl';
        $mailToSeller->Username = 'kontakt@muvhouseofhair.pl';
        $mailToSeller->Password = 'Mozumo79';
        $mailToSeller->SMTPSecure = 'ssl';
        $mailToSeller->Port      = 465;
        $mailToSeller->setFrom('kontakt@muvhouseofhair.pl');
        $mailToSeller->addAddress('joanna.grochowa@icloud.com');
        $mailToSeller->isHTML(true);

        $mailToClient->isSMTP();
        $mailToClient->CharSet = "UTF-8";
        $mailToClient->SMTPAuth = true;
        $mailToClient->Debugoutput = function($str, $level) {$GLOBALS['data']['debug'][] = $str;};
        $mailToClient->Host = 'mail.muvhouseofhair.pl';
        $mailToClient->Username = 'kontakt@muvhouseofhair.pl';
        $mailToClient->Password = 'Mozumo79';
        $mailToClient->SMTPSecure = 'ssl';
        $mailToClient->Port      = 465;
        $mailToClient->setFrom('kontakt@muvhouseofhair.pl');
        $mailToClient->addAddress($email);
        $mailToClient->isHTML(true);

        $cartItemsHTML = '<table>' . $cartItemsHTML . '</table>';

            $mailToClient->Subject = 'Potwierdzenie zamówienia MUV House Of Hair';

            $mailToClient->Body = "<h3>Szanowni Państwo,</h3>
            <p>Dziękujemy za złożenie zamówienia w MUV House of Hair. 
            Oto szczegóły dotyczące Państwa zamówienia:</p><br> $cartItemsHTML 
            <p><strong>Łączna kwota do zapłaty:</strong></p> $cartTotalPriceHTML
            <p>Płatność można dokonać stacjonarnie w salonie lub 
            przelewem na poniższy adres:</p>
            <p>Nr rachunku bankowego:<br>
            <strong>68 1140 2004 0000 3402 8150 7493</strong><br>
            MUV House of Hair <br>
            JOANNA GROCHOWA - TWARDA <br>
            Leszno 64-100,<br>
            ul. Jana Sobieskiego 2B
            </p>
            <p>Jeśli wybrali Państwo opcję dostawy, przesyłka będzie realizowana 
            za pośrednictwem InPost. Prosimy o dokonanie przelewu bankowego i 
            wysłanie potwierdzenia przelewu na adres e-mail: joanna.grochowa@icloud.com.</p>
            <p>Prosimy również o przesłanie adresu dostawy wraz z potwierdzeniem przelewu.</p>
            <p>Dziękujemy za zaufanie i czekamy na Państwa kolejne wizyty w MUV House Of Hair.</p>
            <p>Z poważaniem,</p>
            <p>[Zespół <strong>MUV HOUSE OF HAIR</strong>]</p>";

            $mailToSeller->Subject = 'Potwierdzenie Nowego Zamówienia';

            $mailToSeller->Body =  "
            <h3>Szanowny Zespole,</h3>
            <p>Zarejestrowano nowe zamówienie. Oto szczegóły:</p>
            <p><strong>Imię Zamówcy:</strong> {$firstName} </p>
            <p><strong>Nazwisko Zamówcy:</strong> {$lastName} </p>
            <p><strong>E-mail Zamówcy:</strong> {$email} </p><br> $cartItemsHTML<br>$cartTotalPriceHTML
            <p><strong>Czy potrzebuje Zamówca fakturę:</strong> {$faktura} </p>
            
            <p>Klient został poinformowany o złożonym zamówieniu poprzez wysłanie e-maila 
            z potwierdzeniem. W razie potrzeby proszę o kontakt z nim.</p>
            <p>Dziękujemy!</p>";

            if ($mailToClient->send() && $mailToSeller->send()) {
                $data['result'] = "success";
                $data['info'] = "Rezerwacja została pomyślnie wysłana! Sprawdź e-mail.";
            } else {
                $data['result'] = "error";
                $data['info'] = "Wiadomość nie została wysłana. Wystąpił błąd podczas wysyłania wiadomości.";
                $data['desc'] = "Przyczyna: " . $mailToClient->ErrorInfo . " | " . $mailToSeller->ErrorInfo;
            }
        } catch (Exception $e) {
            $data['result'] = "error";
            $data['info'] = "Wystąpił błąd podczas wysyłania wiadomości.";
            $data['desc'] = "Przyczyna: " . $e->getMessage();
        }

        header('Content-Type: application/json');
        echo json_encode($data);
    }

    exit();
}
