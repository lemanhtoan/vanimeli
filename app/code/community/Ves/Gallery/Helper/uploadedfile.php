<?php 

/**
* Handle file uploads via XMLHttpRequest
*/
class qqUploadedFileXhr {
/**
* Save the file to the specified path
* @return boolean TRUE on success
*/
function save($path) {
$input = fopen("php://input", "r");
$temp = tmpfile();
$realSize = stream_copy_to_stream($input, $temp);
fclose($input);

if ($realSize != $this->getSize()){
return false;
}

$target = fopen($path, “w”);
fseek($temp, 0, SEEK_SET);
stream_copy_to_stream($temp, $target);
fclose($target);

return true;
}
function getName() {
return $_GET['qqfile'];
}
function getSize() {
if (isset($_SERVER["CONTENT_LENGTH"])){
return (int)$_SERVER["CONTENT_LENGTH"];
} else {
throw new Exception("Getting content length is not supported.");
}
}
}

/**
* Handle file uploads via regular form post (uses the $_FILES array)
*/
class qqUploadedFileForm {
/**
* Save the file to the specified path
* @return boolean TRUE on success
*/
function save($path) {
if(!move_uploaded_file($_FILES['qqfile']['tmp_name'], $path)){
return false;
}
return true;
}
function getName() {
return $_FILES['qqfile']['name'];
}
function getSize() {
return $_FILES['qqfile']['size'];
}
}
