<?
// mi_api_tuenti.php

// Autor: Javier Campos (2008)

define('API_URL', 'http://m.tuenti.com/?m=login&func=process_login');  
define('API_URL2', 'http://m.tuenti.com/');
define('URL_AMIGOS', 'http://m.tuenti.com/?m=friends');
define('BASE_COOKIE', 'c:/cookies/');
//define('MAX_PAG_AMIGOS', 2);

define('PREFIJO_LINK_AMIGO','?m=profile&user_id=');

/* iniciar_sesion_tuenti()
 *
 * inicia sesión y almacena en 'ruta_cookie' la cookie */
function iniciar_sesion_tuenti($user, $pass, $ruta_cookie){
	
	$POSTDATA="email=$user&password=$pass";

	$ch = curl_init();
	
	curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; es-ES; rv:1.9.0.5) Gecko/2008120122 Firefox/3.0.5");
	curl_setopt($ch, CURLOPT_COOKIEJAR, $ruta_cookie);
	curl_setopt($ch, CURLOPT_URL,API_URL);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $POSTDATA);

	ob_start();      // prevent any output
	curl_exec ($ch); // execute the curl command
	ob_end_clean();  // stop preventing output

	curl_close ($ch);
	unset($ch);
}

function obtener_amigos($ruta_cookie,$max_iteraciones){


	$cont_pag = 0;
	$amigos = array();	
	$count_amigos = 0;
	
	$iteraciones = 0;
	
	do{
		$url_actual = URL_AMIGOS . "&page=$cont_pag";
		
		$ch = curl_init();
		
		$str  = array(
				"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
				"Accept-Language: es-es,es;q=0.8,en-us;q=0.5,en;q=0.3",
				"Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7",
				"Keep-Alive: 300",
				"Connection: keep-alive"
			  );
      
		curl_setopt($ch, CURLOPT_HTTPHEADER, $str);
		
		curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; es-ES; rv:1.9.0.5) Gecko/2008120122 Firefox/3.0.5");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $ruta_cookie);
		curl_setopt($ch, CURLOPT_URL,$url_actual);

		$buffer = curl_exec ($ch);

		curl_close ($ch);
		
		$doc = new DOMDocument();
		@$doc->loadHTML($buffer);
		
		$count = $count_amigos;
		// NOMBRES y LINKS
	    foreach($doc->getElementsByTagName('a') as $link) {
	        $href = $link->getAttribute('href');
			if(stristr($href, PREFIJO_LINK_AMIGO) !== FALSE) {
				$nombre = $link->firstChild->nodeValue;
				$amigos[$count]["url"] = API_URL2.$href;
				$amigos[$count]["nombre"] = $nombre;
				$count++;
			}
	    } 
		
		if($count == $count_amigos){ // si no se ha encontrado nada en la ultima iteración...
			break;
		}
		
		$count_tmp = $count;
		$count = $count_amigos;
		
		if($count_tmp != 0){
			$count = -1; // para seguir iterando. Se da este caso cuando no se muestran las imagenes
			$count_amigos = $count_tmp;
		}		
		else{ // si todo correcto...
			$count_amigos = $count;
		}
		
		$cont_pag ++;
		
		$iteraciones ++;
		
	}while($iteraciones < $max_iteraciones);
	
	return $amigos;
}

function mostrar_amigos($amigos){

	?>
	<html>
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/> 
		</head>
		<body>
	<h2>Mis amigos en el Tuenti (<?=count($amigos)?>)</h2><?
	for($i=0; $i<count($amigos); $i++){
		?><a href="<?=$amigos[$i]["url"]?>" target="_blank"><?=$amigos[$i]["nombre"]?></a><br>
		<?if(isset($amigos[$i]["url_img"])){?>
			<img alt="Foto de perfil" src="<?=$amigos[$i]["url_img"]?>"/><br><br><?
		}
	}
	?></head></html><?

}

/*--------------------------------------------------------------------*/


$id = "12345";
$ruta_cookie = BASE_COOKIE.$id.".txt";


$user= "<EDITAR AQUÍ>@xxxxx.com";
$user = str_replace('@', '%40', $user);
$pass = "<EDITAR AQUÍ>";

$ret = iniciar_sesion_tuenti($user,$pass, $ruta_cookie);

$amigos = obtener_amigos($ruta_cookie);
if($amigos!= false){
	mostrar_amigos($amigos);
}


?>