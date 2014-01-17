<?php
$dom = new DOMDocument("1.0");
header("Content-Type: text/xml");
// create root element
$root = $dom->createElement("list");
$dom->appendChild($root);



$pattern="(\.jpg$)|(\.png$)|(\.jpeg$)|(\.gif$)"; //valid image extensions
if($handle = opendir("../uploadthumb/")) 
{
	while(false !== ($file = readdir($handle)))
	{
		if(eregi($pattern, $file)) //if this file is a valid image
		{
			// create child element
			$item = $dom->createElement("image");
			$root->appendChild($item);
			
			// create text node
			$text = $dom->createTextNode($file);
			$item->appendChild($text);
		}
	}
	closedir($handle);
}

// save and display tree
echo $dom->saveXML();
//getfiles("uploadthumb/"); //List all image files within a specific directory on the server

?>