<?php
/**
 * Email Functions using PHPMailer
 * Thunderstorm Rock Festival 2025
 */

require_once __DIR__ . '/../PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/SMTP.php';
require_once __DIR__ . '/../PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Lähetä sähköposti
 * 
 * @param string $to Vastaanottajan email
 * @param string $subject Aihe
 * @param string $body HTML-sisältö
 * @param string $recipient_name Vastaanottajan nimi (vapaaehtoinen)
 * @return bool True jos onnistui
 */
function send_email($to, $subject, $body, $recipient_name = '') {
    $mail = new PHPMailer(true);
    
    try {
        // SMTP-asetukset (Mailtrap)
        $mail->isSMTP();
        $mail->Host = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth = true;
        $mail->Username = '628bb21aa0c28a';
        $mail->Password = 'aa195cff892505';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 2525;
        
        // Lähettäjä
        $mail->setFrom('noreply@thunderstormrock.fi', 'Thunderstorm Rock Festival');
        
        // Vastaanottaja
        if (!empty($recipient_name)) {
            $mail->addAddress($to, $recipient_name);
        } else {
            $mail->addAddress($to);
        }
        
        // Sisältö
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body); // Plain text versio
        
        // Lähetä
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        // Loki virhe
        error_log("Email sending failed: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Lähetä salasanan palautuslinkki
 * 
 * @param string $email Käyttäjän email
 * @param string $username Käyttäjänimi
 * @param string $token Reset token
 * @return bool
 */
function send_password_reset_email($email, $username, $token) {
    $reset_link = SITE_URL . "/pages/reset_password.php?token=" . urlencode($token);
    
    $subject = "Salasanan palautus - Thunderstorm Rock Festival";
    
    $body = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; background-color: #0a0a0a; color: #e0e0e0; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 40px auto; background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); border: 3px solid #ff6b35; border-radius: 15px; padding: 40px; }
            .header { text-align: center; margin-bottom: 30px; }
            .logo { font-size: 32px; color: #ff6b35; font-weight: bold; text-shadow: 2px 2px 4px rgba(0,0,0,0.8); }
            .content { line-height: 1.8; }
            .button { display: inline-block; padding: 15px 35px; background: linear-gradient(45deg, #ff6b35, #ffa726); color: white; text-decoration: none; border-radius: 30px; font-weight: bold; margin: 20px 0; }
            .footer { margin-top: 30px; padding-top: 20px; border-top: 2px solid #ff6b35; text-align: center; color: #999; font-size: 14px; }
            .warning { background: rgba(255, 107, 53, 0.1); border-left: 4px solid #ff6b35; padding: 15px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <div class="logo">🎸 Thunderstorm Rock Festival</div>
            </div>
            
            <div class="content">
                <h2 style="color: #ffa726;">Hei ' . htmlspecialchars($username) . '!</h2>
                
                <p>Saimme pyynnön salasanasi vaihtamiseksi. Jos et pyytänyt tätä, voit jättää tämän viestin huomiotta.</p>
                
                <p>Vaihtaaksesi salasanasi, klikkaa alla olevaa painiketta:</p>
                
                <div style="text-align: center;">
                    <a href="' . $reset_link . '" class="button">Vaihda salasana</a>
                </div>
                
                <p>Tai kopioi tämä linkki selaimeesi:</p>
                <p style="word-break: break-all; color: #ffa726;">' . $reset_link . '</p>
                
                <div class="warning">
                    <strong>⚠️ Tärkeää:</strong>
                    <ul>
                        <li>Linkki on voimassa yhden tunnin</li>
                        <li>Voit käyttää sitä vain kerran</li>
                        <li>Älä jaa linkkiä kenellekään</li>
                    </ul>
                </div>
                
                <p>Jos sinulla on kysyttävää, ota yhteyttä: info@thunderstormrock.fi</p>
            </div>
            
            <div class="footer">
                <p><strong>Thunderstorm Rock Festival 2025</strong></p>
                <p>25.-26. Heinäkuuta 2025 | Ratina, Tampere</p>
                <p style="font-size: 12px; margin-top: 15px;">Rock on! 🤘</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    return send_email($email, $subject, $body, $username);
}

/**
 * Lähetä tervetuloa-viesti uudelle käyttäjälle
 * 
 * @param string $email Käyttäjän email
 * @param string $username Käyttäjänimi
 * @return bool
 */
function send_welcome_email($email, $username) {
    $subject = "Tervetuloa Thunderstorm Rock Festivalille! 🎸";
    
    $body = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { 
                font-family: Arial, sans-serif; 
                background-color: #0a0a0a; 
                color: #e0e0e0; 
                margin: 0; 
                padding: 0; 
            }
            .container { 
                max-width: 600px; 
                margin: 40px auto; 
                background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); 
                border: 3px solid #ff6b35; 
                border-radius: 15px; 
                padding: 40px; 
            }
            .header { 
                text-align: center; 
                margin-bottom: 30px; 
            }
            .logo { 
                font-size: 36px; 
                color: #ff6b35; 
                font-weight: bold; 
                text-shadow: 2px 2px 4px rgba(0,0,0,0.8); 
                margin-bottom: 10px;
            }
            .tagline {
                color: #ffa726;
                font-size: 18px;
                margin-top: 10px;
            }
            .content { 
                line-height: 1.8; 
            }
            .highlight-box {
                background: rgba(255, 107, 53, 0.15);
                border-left: 4px solid #ff6b35;
                padding: 20px;
                margin: 25px 0;
                border-radius: 5px;
            }
            .features {
                margin: 20px 0;
            }
            .feature-item {
                margin: 15px 0;
                padding-left: 30px;
                position: relative;
            }
            .feature-item:before {
                content: "🎸";
                position: absolute;
                left: 0;
                font-size: 20px;
            }
            .button { 
                display: inline-block; 
                padding: 15px 35px; 
                background: linear-gradient(45deg, #ff6b35, #ffa726); 
                color: white !important; 
                text-decoration: none; 
                border-radius: 30px; 
                font-weight: bold; 
                margin: 20px 0;
                text-align: center;
            }
            .footer { 
                margin-top: 30px; 
                padding-top: 20px; 
                border-top: 2px solid #ff6b35; 
                text-align: center; 
                color: #999; 
                font-size: 14px; 
            }
            .event-date {
                background: rgba(255, 107, 53, 0.8);
                display: inline-block;
                padding: 10px 20px;
                border-radius: 8px;
                margin: 15px 0;
                font-weight: bold;
                color: white;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <div class="logo">🎸 Thunderstorm Rock Festival</div>
                <div class="tagline">2025 - Suomen rockaavain festivaali</div>
            </div>
            
            <div class="content">
                <h2 style="color: #ffa726;">Hei ' . htmlspecialchars($username) . '!</h2>
                
                <p><strong>Tervetuloa Thunderstorm Rock Festival -yhteisöön! 🤘</strong></p>
                
                <p>Olet juuri liittynyt Suomen rockaavimpaan festivaalitapahtumaan. Olemme innoissamme, että olet mukana!</p>
                
                <div class="highlight-box">
                    <div class="event-date">📅 25.-26. Heinäkuuta 2025</div>
                    <div style="margin-top: 10px;">📍 Ratina, Tampere</div>
                </div>
                
                <h3 style="color: #ffb74d;">Mitä seuraavaksi?</h3>
                
                <div class="features">
                    <div class="feature-item">
                        <strong>Tutustu artisteihin</strong><br>
                        Kaksi päivää täynnä huippuesiintyjiä!
                    </div>
                    <div class="feature-item">
                        <strong>Osta liput</strong><br>
                        Valitse Päivälippu, Viikonloppulippu tai VIP-lippu
                    </div>
                    <div class="feature-item">
                        <strong>Päivitä profiilisi</strong><br>
                        Lisää tietosi ja pysy ajan tasalla
                    </div>
                </div>
                
                <div style="text-align: center; margin: 30px 0;">
                    <a href="' . SITE_URL . '" class="button">Siirry festivaalisivulle</a>
                </div>
                
                <p><strong>Lipputyypit:</strong></p>
                <ul>
                    <li>🎫 <strong>Päivälippu</strong> - 35€ (pääsy yhtenä päivänä)</li>
                    <li>🎫 <strong>Kahden päivän lippu</strong> - 60€ (pääsy molempina päivinä)</li>
                    <li>⭐ <strong>VIP-lippu</strong> - 120€ (VIP-alue + backstage-pääsy)</li>
                </ul>
                
                <p style="margin-top: 25px;">Jos sinulla on kysyttävää, vastaamme mielellämme!</p>
                <p>📧 <a href="mailto:info@thunderstormrock.fi" style="color: #ffa726;">info@thunderstormrock.fi</a></p>
            </div>
            
            <div class="footer">
                <p><strong>Thunderstorm Rock Festival 2025</strong></p>
                <p>25.-26. Heinäkuuta 2025 | Ratina, Tampere</p>
                <p style="margin-top: 15px;">🎸 Seuraa meitä sosiaalisessa mediassa!</p>
                <p style="font-size: 20px; margin-top: 10px;">
                    📘 📷 🐦 📺 🎵
                </p>
                <p style="font-size: 12px; margin-top: 15px; color: #666;">
                    Sait tämän viestin koska rekisteröidyit Thunderstorm Rock Festival -sivustolle.
                </p>
                <p style="font-size: 16px; margin-top: 15px; color: #ff6b35;">
                    Rock on! 🤘
                </p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    return send_email($email, $subject, $body, $username);
}

?>