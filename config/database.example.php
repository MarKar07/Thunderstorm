<?php
// ESIMERKKI - Kopioi tästä database.php ja laita omat tietosi
$host = "localhost";
$dbname = "tietokannan_nimi";  
$username = "käyttäjätunnus";
$password = "salasana_tähän";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Tietokantayhteys epäonnistui.");
}
?>