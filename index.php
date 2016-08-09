<?php 
function getImgurClientId() {
	//put Imgur API Client ID in a file that's out of reach of web users
	//put the relative path here
	$keyfilePath = "../../apikey.txt";

	$keyfileHandle = fopen($keyfilePath,"r");
	return fread($keyfileHandle, filesize($keyfilePath));
}

function paraPrint($text) {
	print "<p>$text</p>";
}

function splitImgurUrl($url){
	$path = parse_url($url, PHP_URL_PATH);
	return reverseImgurPath($path);
}

function reverseImgurPath($path){
	$pathArray = preg_split('+/+', $path);
	$filteredPathArray = array_filter($pathArray);
	return array_reverse($filteredPathArray);
}

function imgurDirectRequest($imgLink){
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

function imgurApiRequest($queryString) {
	$imgurClientId = getImgurClientId();
	$ch = curl_init($queryString);

	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Client-ID $imgurClientId"));

	$curlResult = curl_exec($ch);
	curl_close($ch);

	$jsonResult = json_decode($curlResult);
	if($jsonResult->success){
		imgurDirectRequest($jsonResult->data->link);
	}
	else {
		paraPrint("Imgur didn't care for that request.");
	}	
}


$imgurEndpoint = "https://api.imgur.com/3/";
$endpointImage = "image/";

$urlAlbum = "/a/";
$urlImage = "";

if(!isset($_GET['url'])) {
	//todo http error and more sophisticated error screen
	print "<p>Pass a url next time.</p>";
	return;
}

//todo trim this
$url = $_GET['url'];

$pathArray = splitImgurUrl($url);
$pathTerminal = $pathArray[0];

$possibleExt = substr($pathTerminal, ($pathTerminal.strlen - 4));
$directFiletypes = array(".jpg",".gif",".png");

if(in_array($possibleExt, $directFiletypes)) {
	paraPrint("Looks like url is direct link to a $possibleExt.");
	imgurDirectRequest($url);
}
else if($pathArray[1] === "a") {
	print "<p>Imgur albums are not supported yet.</p>";
}
else {
	$queryString = $imgurEndpoint . $endpointImage . $pathArray[0];
	imgurApiRequest($queryString);
}
?>
