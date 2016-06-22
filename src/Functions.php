<?php
function pathFile($path) {
	$folder = '';
    $name = $path;
	if(strpos($path, '/')) {
		$path = explode('/', $path);
		
		$pathCount = count($path)-1;
        $folder = '';
		for ($i=0; $i < $pathCount; $i++) { 
			$folder .= $path[$i].'/';
		}
		$name = $path[$pathCount];
	}
    return array($folder, $name);
}


/*PO PRZEJŚCIU NA NOWY FRAMEWORK TRZEBA PRZENIEŚĆ TO DO BIBLIOTEKI LUB ZMIENIĆ */
function validateDate($date, $format = 'Y-m-d H:i:s') {
	$d = DateTime::createFromFormat($format, $date);
	return $d && $d->format($format) == $date;
}

/* 
 * Sumowanie minut
*/ 


function convertMinutes($minut) {
	$godzin = floor($minut/60);  // liczba pełnych godzin
	$rmin = $minut - $godzin*60;  // reszta minut

	$dni = floor($minut/(24*60));  // liczba pelnych dni
	$rgod = $godzin - $dni*24;  // reszta godzin

	$miesiecy = floor($minut/(30*24*60)); // liczba pelnych miesiecy (niedokladna)
	$rdni = $dni - $miesiecy*30;  // reszta dni

	$wypisz = '';
	if($miesiecy != 0) $wypisz .= $miesiecy." miesięcy, ";
	if($dni != 0) $wypisz .= $rdni." dni, ";
	if($godzin != 0) $wypisz .= $rgod." godziny, ";
	$wypisz .= $rmin." minuty | Suma Godzin: ".$godzin;

	return $wypisz;
}
/* 
 * Randomowo generowany string
*/ 

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

/* 
 * Zmiana Obiektu wielowymiaroa na tablice array
*/ 

function object_to_array($obj) {
    if(is_object($obj)) $obj = (array) $obj;
    if(is_array($obj)) {
        $new = array();
        foreach($obj as $key => $val) {
            $key2 = str_replace("\0", "", $key);
            $new[$key2] = object_to_array($val);
        }
    }
    else $new = $obj;
    return $new;       
}

/* 
 * Wyszukiwanie ciagu zdania za pozmoca wilcardu 
 * ala ma kota -> ala * kota == TRUE
*/ 


function stringMatchWithWildcard($source,$pattern) {
    $pattern = preg_quote($pattern,'/');        
    $pattern = str_replace( '\*' , '.*', $pattern);   
    return preg_match( '/^' . $pattern . '$/i' , $source );
}
?>