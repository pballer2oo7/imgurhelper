<?php 
function paraPrint($text) {
	print "<p>$text</p>";
}

function splitImgurUrl($url){
	$path = parse_url($url, PHP_URL_PATH);
	$pathArray = preg_split('+/+', $path);

	$filteredPathArray = array_filter($pathArray);
	return array_reverse($filteredPathArray);
}

//put Imgur API Client ID in a file that's out of reach of web users
//put the relative path here
$keyfilePath = "../../apikey.txt";

$keyfileHandle = fopen($keyfilePath,"r");
$imgurClientId = fread($keyfileHandle, filesize($keyfilePath));

$imgurEndpoint = "https://api.imgur.com/3/";
$endpointImage = "image/";

$urlAlbum = "/a/";
$urlImage = "";

if(!isset($_GET['url'])) {
	//todo http error and more sophisticated error screen
	print "<p>Pass a url next time.</p>";
	return;
}

$url = $_GET['url'];

$pathArray = splitImgurUrl($url);
if($pathArray[1] === "a") {
	print "<p>Imgur albums are not supported yet.</p>";
}
else {
	$queryString = $imgurEndpoint . $endpointImage . $pathArray[0];
	$ch = curl_init($queryString);

	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Client-ID $imgurClientId"));

	$curlResult = curl_exec($ch);
	curl_close($ch);

	$jsonResult = json_decode($curlResult);

	if($jsonResult->success){
		$imgLink = $jsonResult->data->link;
		$imgPathArray = splitImgurUrl($imgLink);
		$imgName = $imgPathArray[0];
		
		$imageFile = fopen("dmp/$imgName", "w");
		
		$directHandle = curl_init($imgLink);
		curl_setopt($directHandle, CURLOPT_FILE, $imageFile);
		
		curl_exec($directHandle);
		fclose($imageFile);
		curl_close($directHandle);
		paraPrint("<a href=\"dmp/$imgName\">$imgName</a>");
	}
	else {
		paraPrint("Imgur didn't care for that request.");
	}	
}
?>
