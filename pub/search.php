<!DOCTYPE unspecified PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Search Script</title>
<style type="text/css">
.result-table td {
    padding: 5px;
}
body{
	/*background:#ccc;*/
	margin:0px auto;
	width: 65%;
	/*background-image: url("bg.jpg");
	color: #fff;*/
}
h1{
	text-align: center;
	margin-top:10px;
	text-decoration: underline;
}
.main-div{
	margin:0px auto;
	float:left;
	width:100%;
	box-shadow: 0px 1px 11px #000;
	border: 1px outset #fff;
}
.result-table{
	margin: 0px auto;
	float: left;
	width: 100%;
	box-shadow: 0px 1px 11px #000;
	margin-top:5px;
	font-size: 13px;
}
.suggestion-string{
	clear: both;
    float: left;
    font-size: 11px;
    margin-left: 5px;
    position: absolute;
    margin-top: 4px;
    max-width: 350px;
}
form{
	padding:30px 30px 0;
}
.search-button{
	float:right;
	margin-top:25px;
}
thead {
    text-align: center;
    font-size: 20px;
}
</style>
</head>
<body>
<?php $ifEditorExists = file_exists(getcwd().DIRECTORY_SEPARATOR."editor.php"); ?>
	<h1>Search Script Page</h1>
<div class="main-div">
<form action="" method="post">
<table>
<tr><td><label><?php echo "Search String";?></label><td><input type="text" name="string" id="string" value="<?php echo $_POST['string'] ?>" /><label class="suggestion-string">Enter string to search e.g. AccountController</label></td></tr>
<tr><td><label><?php echo "Directory";?></label><td><input type="text" name="dir" id="dir"  value="<?php echo $_POST['dir'] ?>"/><label class="suggestion-string">Enter directory path e.g. app/code/local</label></td></tr>
<tr><td><label><?php echo "File Extensions";?></label><td><input type="text" name="ext" id="ext"  value="<?php echo $_POST['ext'] ?>"/><label class="suggestion-string">Enter file extensions. e.g. php / For multiple file types e.g. php,phtml Keep empty for all file types</label></td></tr>
<?php if($ifEditorExists){ ?>
<tr><td><label><?php echo "Sub Directory Name";?></label><td><input type="text" name="subdir" id="subdir"  value="<?php echo $_POST['subdir'] ?>"/><label class="suggestion-string">Enter directory name if source is not on base url</label></td></tr>
<?php } ?>
<tr><td colspan="2"><input class="search-button" type="submit" title="Search" value="Search"/></td></tr>
</table>
</form>
</div>
</body>
</html>
<?php
/*
###### Usage ########
STRING_TO_FIND ==> e.g. first_name
DIR_PATH ==> 	   start from the directory where file is located e.g. app/code/local
FILE_EXTENSION ==> extension of file. e.g. php,phtml,xml(can use multiple extension with comma separated)
BAS_URL ==>		   Project base url (without index.php)
1. Put the file in your root folder
2. To search a string type in URL :
   BAS_URL/search.php?string=STRING_TO_FIND&dir=DIR_PATH&ext=FILE_EXTENSION
*/
if($_POST){
	$string = $_POST['string'];
	$dir = $_POST['dir'];
	$extArray = array();
	$subdir = $_POST['subdir'];
	if($_POST['ext'] != ""){ $extArray = explode(",",$_POST['ext']); }
	echo "<table border='1' class='result-table'><thead><tr><td colspan='3'>Search Results</td></tr></thead><tbody><tr><td>Filepath</td><td>Last Modified Date</td>";
if($ifEditorExists) { echo "<td>Edit</td>"; } 
echo "</tr>";
	listFolderFiles($string, $dir, $extArray, $subdir, $ifEditorExists); 	
	echo "</tbody></table>";
}
$url = url();
function listFolderFiles($string, $dir, $extArray, $subdir, $ifEditorExists){
	
	if(!$dir){ $dir = getcwd(); }
    $ffs = scandir($dir);
    foreach($ffs as $ff){
        if($ff != '.' && $ff != '..'){
            if(is_dir($dir.'/'.$ff)){
				listFolderFiles($string, $dir.'/'.$ff, $extArray, $subdir, $ifEditorExists);
			}else{
				$extension = pathinfo($dir.'/'.$ff, PATHINFO_EXTENSION);
				if(!empty($extArray)){
					if(in_array($extension,$extArray)){
						$content = file_get_contents($dir.'/'.$ff);
						if (strpos($content, $string) !== false) {
							echo "<tr><td>". $dir.'/'.$ff."</td><td>".date ("F d Y H:i:s", filemtime($dir.'/'.$ff))."</td>";
if($ifEditorExists){ echo "<td><a href='". url()."/".$subdir."/editor.php?filepath=".$dir.'/'.$ff."' target='_blank'>Edit</a></td>";}
echo "</tr>";
							
						}
					}
				}
				else{
						$content = file_get_contents($dir.'/'.$ff);
						if (strpos($content, $string) !== false) {
							echo "<tr><td>". $dir.'/'.$ff."</td><td>".date ("F d Y H:i:s", filemtime($dir.'/'.$ff))."</td>";
if($ifEditorExists){ echo "<td><a href='". url()."/".$subdir."/editor.php?filepath=".$dir.'/'.$ff."' target='_blank'>Edit</a></td>";}
echo "</tr>";
							
						}
					}
					
			}
		
        }
    }
    
}
?>
<?php 
function url(){
  if(isset($_SERVER['HTTPS'])){
        $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
    }
    else{
        $protocol = 'http';
    }
    return $protocol . "://" . $_SERVER['HTTP_HOST'];
}
?>