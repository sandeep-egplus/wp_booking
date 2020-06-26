<?php
    if ( isset($_GET['csv_dir'] ) ) {
        $dir = $_GET['csv_dir'];
        $dir = str_replace('?', '', $dir);
        $dir .= '/../wpbc_csv';                                               //FixIn: 8.3.3.10
    } else
        $dir = dirname(__FILE__) . '/../../../../wpbc_csv' ;                    //FixIn: 8.3.3.10
    $filename = 'bookings_export.csv';
    if ( ! file_exists( "$dir/$filename" ) ){
        die( 'Wrong Path. Error during exporting CSV file!' );
    }   
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream'); 
    header('Content-Disposition: attachment; filename='.$filename);
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    echo "\xEF\xBB\xBF"; // UTF-8 BOM
    readfile("$dir/$filename");