<?php
$scriptfile = fopen('../database/init.sql', "r");
if (!$scriptfile) { die("ERROR: Couldn't open {$scriptfile}.\n"); }

// grab each line of file, skipping comments and blank lines

$script = '';
while (($line = fgets($scriptfile)) !== false) {
    $line = trim($line);
    if(preg_match("/^#|^--|^$/", $line)){ continue; }
    $script .= $line;
}

// explode script by semicolon and run each statement

$statements = explode(';', $script);

include 'connection_config.php';

$pdo = new PDO($dsn, $username, $password
//    sprintf("pgsql:host=%s; port=%s; dbname=%s; user=%s; password=%s",
//        $host, $port, $database, $username, $password)
 );

foreach($statements as $sql){
    if($sql === '') { continue; }
    $query = $pdo->prepare($sql);
    $query->execute();
    if($query->errorCode() !== '00000')
    {
        header("Location: http://127.0.0.1:{$php_port}/");
        die("ERROR: SQL error code: ".$query->errorCode()."\n");
    }
}
header("Location: http://127.0.0.1:{$php_port}/");
die();
