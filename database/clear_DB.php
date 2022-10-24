<?php

require_once dirname(__DIR__)."/home/utilities.php";

$scriptfile = fopen('./init.sql', "rb");
if (!$scriptfile) { die("ERROR: Couldn't open $scriptfile.\n"); }

// grab each line of file, skipping comments and blank lines

$script = '';
while (($line = fgets($scriptfile)) !== false) {
    $line = trim($line);
    if(preg_match("/^#|^--|^$/", $line)){ continue; }
    $script .= $line;
}

// explode script by semicolon and run each statement

$statements = explode(';', $script);

$config = [
    env('DB_HOST', '127.1.0.1'),
    env('DB_PORT', '5432'),
    env('DB_DATABASE', 'php-education'),
    env('DB_USER', 'php-education'),
    env('DB_PASSWORD', 'php-education')
];

$pdo = new PDO(
    vsprintf('pgsql:host=%s; port=%s; dbname=%s; user=%s; password=%s', $config)
);

foreach($statements as $sql){
    if($sql === '') { continue; }
    $query = $pdo->query($sql);
    if($query->errorCode() !== '00000')
    {
        header('Location: http://'.env('HOST', '127.0.0.1').':'.env('PORT', '8001').'/');
        die("ERROR: SQL error code: ".$query->errorCode()."\n");
    }
}

header('Location: http://'.env('HOST', '127.0.0.1').':'.env('PORT', '8001').'/');
die();
