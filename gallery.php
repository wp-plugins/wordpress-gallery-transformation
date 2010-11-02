<?php

/**
 * @package Gallery
 * @author PsMan
 * @version 0.7
 */
/*
Plugin Name: Gallery transformation
Plugin URI: http://angrybyte.com/wordpress-plugins/wordpress-gallery-transformation/
 Description: Transforms word press into a gallery, wallpapers website, you name it. with ftp upload support, batch URL download, auto resizing. tagging, categorizing, renaming is made so easy! Thsi plugin will also create a new  post for every picture in the gallery to make your gallery compatible with any wordpress plugin you may want to use. a working copy of this gallery is here http://cosplay-naruto.com
 Notice that to use this your blog must be blank, with no current posts, this plugin is designed to ren wordpress as a dedicated images gallery. 
 
 PS: Sorry for the previous buggy release, I will make sure to keep my work updated from now on ;)
Author: PsMan, Bouzid Nazim Zitouni
Version: 0.7
Author URI: http://angrybyte.com
 */
add_filter('the_content','gallery_mode');
add_filter('the_title','cutename');
add_action('wp_footer','footerlink');
//add_filter('the_meta','blanker');
add_action('admin_menu', 'my_plugin_menu');
 register_sidebar_widget('Gallery Random Picture', 'randompic');
register_sidebar_widget('Gallery Most Viewed Picture', 'mostviewedpic');
register_sidebar_widget('Gallery Tag Cloud', 'galtagcloud');
 register_sidebar_widget('Add your own picture', 'useraddpic');
register_sidebar_widget('Gallery categories', 'galist');
add_option("Allowgalleryfooterlink", '1', 
'Would you be cool enough to keep a link to us at the bottom of your Gallery?', 'yes'); 
function footerlink($content){
	if (get_option('Allowgalleryfooterlink') ){
		$content .= "<p align='center'>Gallery Transformation by <a href='http://angrybyte.com'> PsMan</a> Visit my gallery at <a href='http://cosplay-naruto.com'> Cosplay Naruto </a></p>";
		
		
	}
	echo $content;
	return $content;
}

function dbcreator(){
	global $wpdb;

 $pfx=$wpdb->prefix;

$a=$wpdb->query("CREATE TABLE IF NOT EXISTS `{$pfx}gallery` (
  `name` varchar(50) NOT NULL,
  `thumb` text NOT NULL,
  `url` text NOT NULL,
  `hits` int(11) NOT NULL,
  `cat` int(4) NOT NULL,
  `rates` int(11) NOT NULL,
  `id` int(11) NOT NULL auto_increment,
  `owner` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  ;");

$a=$wpdb->query("CREATE TABLE IF NOT EXISTS `{$pfx}gal_cats` (
  `ID` int(11) NOT NULL auto_increment,
  `pic` varchar(100) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=InnoDB  ;");



$a=$wpdb->query("CREATE TABLE IF NOT EXISTS `{$pfx}gal_entries` (
  `id` int(11) NOT NULL auto_increment,
  `post_id` int(11) NOT NULL,
  `galid` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB ;");


$a=$wpdb->query("CREATE TABLE IF NOT EXISTS `{$pfx}gal_tags` (
  `id` int(11) NOT NULL auto_increment,
  `tag` varchar(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB ;");


$a=$wpdb->query("CREATE TABLE IF NOT EXISTS `{$pfx}gal_tag_rel` (
  `id` int(11) NOT NULL auto_increment,
  `gal_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `tagger` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB ;");
 $basedir=str_replace("wp-admin",'',realpath(dirname($_SERVER['index.php']))) ;
 //createThumbs($basedir . "gal/ftp/",$basedir ."gal/thumbs",$basedir ."gal/pics",250);
 
 if(!file_exists($basedir . "/gal")){
 	mkdir($basedir . "/gal",777);
 }
  if(!file_exists($basedir . "/gal/ftp")){
 	mkdir($basedir . "/gal/ftp",777);
 }
   if(!file_exists($basedir . "/gal/thumbs")){
 	mkdir($basedir . "/gal/thumbs",777);
 }
   if(!file_exists($basedir . "/gal/pics")){
 	mkdir($basedir . "/gal/pics",777);
 }
}
function galist(){
global $wpdb;

 $pfx=$wpdb->prefix;
 dbcreator();
 $qry= "
Select * from `" . $pfx . "gal_cats` 
;
";

 $res= $wpdb->get_results($qry);
 $cons='<h2 class="widgettitle">Gallery Categories</h2>';
foreach($res as $cat){
 $cons = $cons . '<a href="index.php?lst=0&gp=1&gcat='. $cat->ID .'">'. $cat->name .'</a> ';
}
 echo $cons;

}
function blanker($content){

 return '';

}

function cutename($content){
    global $post;
    global $wpdb;
    $pid=$post->ID;
    $pfx=$wpdb->prefix;
    dbcreator();
    $content=$wpdb->get_var("select name from {$pfx}gallery where  id in (select galid from {$pfx}gal_entries where post_id = $pid) limit 1");
 $content=str_replace('-',' ',$content);
 $content=str_replace('_',' ',$content);
 $content=str_replace('.jpg','',$content);
 $content=str_replace('.JPG','',$content);
 return $content;

}
function useraddpic(){
	dbcreator();
 if(is_user_logged_in()){
 $oot ='<h2 class="widgettitle">Add Your Own Picture</h2>';
 if($_FILES["usafile"]){
 $oot .="You submission will be reviewed and added very soon, Thank you!!";
 echo $oot;
 return("good");
 }
 $serv=$_SERVER['PHP_SELF'];
 $oot .="<img src='gal/thumbup.jpg' align='left' width ='30%' /> <form action='$serv' method='post'
enctype='multipart/form-data'>
<label for='file'><br />Filename:</label>
 <input type='file' name='usafile' id='usafile' /> 
<br />
<input type='submit' name='submit' value='Submit' />
</form> ";
 }else{ $oot= "<br /><h2 class='widgettitle'>Add Your Own Picture</h2> <img src='gal/thumbup.jpg' align='left' width ='30%' /><br />You Need to <a href='wp-login.php?action=register'>Register </a>and <a href='wp-login.php'>Login </a> to add your pictures, it will only take a second.";
 }
echo $oot;
}
function randompic(){
 global $wpdb;

 $pfx=$wpdb->prefix;
dbcreator();
 $res= $wpdb->get_results("select * from {$pfx}gallery order by RAND() limit 1");
 $oot ='<h2 class="widgettitle">Random Picture</h2>';

 foreach($res as $pres){
 $wppost=$wpdb->get_var("select post_id from {$pfx}gal_entries where galid = {$pres->id} limit 1");
 $oot .='<a class ="thumbnail" href="index.php?p=' . $wppost . '&gp=2&gpic='. $pres->id .'">' . "<img width='100%' src='{$pres->thumb}' title='{$pres->name}' /></a>";

 }
echo $oot;

}

function mostviewedpic(){
 global $wpdb;

 $pfx=$wpdb->prefix;
dbcreator();
 $res= $wpdb->get_results("select * from {$pfx}gallery order by hits DESC limit 1");
 $oot ='<h2 class="widgettitle">Most Viewed</h2>';
 foreach($res as $pres){
 $wppost=$wpdb->get_var("select post_id from {$pfx}gal_entries where galid = {$pres->id} limit 1");
 $oot .='<a class ="thumbnail" href="index.php?p=' . $wppost . '&gp=2&gpic='. $pres->id .'">' . "<img width='100%' src='{$pres->thumb}' title='{$pres->name}' /></a><br />Viewed {$pres->hits} Times";

 }
echo $oot;

}

function galtagcloud(){
 global $wpdb;

 $pfx=$wpdb->prefix;
 dbcreator();
 $res=$wpdb->get_results("select {$pfx}gal_tags.id, {$pfx}gal_tags.tag, count({$pfx}gal_tag_rel.id) as zecount from {$pfx}gal_tags, {$pfx}gal_tag_rel where {$pfx}gal_tags.id={$pfx}gal_tag_rel.tag_id GROUP BY wp_gal_tags.tag");

 $oot ='<h2 class="widgettitle">Gallery Tags</h2>' ;
 $maxx=0;
 foreach($res as $pres){
 if ($pres->zecount > $maxx){
 $maxx=$pres->zecount;
 }


 }
 foreach($res as $pres){

 $fs= ($pres->zecount)/$maxx *10; 
 $oot .="<a href='index.php?lst=0&gp=1&gtag={$pres->id}'> <font size={$fs}> {$pres->tag}</font></a>";

 }
 echo $oot;
}

function my_plugin_menu() {

 add_options_page('Gallery Admin', 'Gallery Transformation Admin', 8, __FILE__, 'my_plugin_options');
 add_options_page('Gallery Picture Namer', 'Gallery Pictures', 8, __FILE__ . '2', 'nameze');
 }
function nameze() {


 global $wpdb;

 $pfx=$wpdb->prefix;
dbcreator();
 if($_GET['picnj']){

 $jpic=$_GET['picnj'];
 $jnm=$_GET['nmj'];
 $wpdb->query("update {$pfx}gallery set name='{$jnm}' where id=$jpic;");
 $wpdb->query("update {$pfx}gallery set rates=44");
 return 'ok?';
 }

 $basedir=str_replace("wp-admin",'',realpath(dirname($_SERVER['index.php']))) ;
 $serv=str_replace( '%7E', '~', $_SERVER['REQUEST_URI']);
 $serv=str_replace("php2","php",$serv);
 //echo $serv;
 $qry="select * from ". $pfx ."gallery where thumb
REGEXP name ";

 $ress= $wpdb->get_results($qry);
 $unnampics="";

echo("<h3> Galley Pictures Renamer </h3><br /><br /> Here you can quickly rename the pictures in your gallery<br />");

foreach($ress as $unnam){

 $unnampics .= "<div id='{$unnam->id}'><img src='../$unnam->thumb' /> <input type='text' name='tx{$unnam->id}' id = 'tx{$unnam->id}' /> <input type='button' onclick='najax_call({$unnam->id})' Value='Edit' /></div>
 <script type='text/javascript'>
var xmlhttp=false;

if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
xmlhttp = new XMLHttpRequest();
}

function najax_call(n) {
 var nnnm='tx' + n;
var nm = document.getElementById(nnnm).value ;
xmlhttp.open('GET', '$serv&picnj=' +
n +'&nmj='+ nm, true);
document.getElementById(n).style.height='0px';
 document.getElementById(n).style.visibility='hidden';
xmlhttp.onreadystatechange=function() {
if (xmlhttp.readyState==4) {

}
}

xmlhttp.send(null)
return false;

}
function reloadPage()
 {
window.location.reload();
}
</script>


 ";

 }
 echo $unnampics ;
 }


function my_plugin_options() {
 global $wpdb;
 $pfx=$wpdb->prefix;
 dbcreator();
 
 $basedir=str_replace("wp-admin",'',realpath(dirname($_SERVER['index.php']))) ;
 
// echo "Hy $basedir" . $_FILES["file"];
if($_POST['tagcat']){
 $qq="select {$pfx}gallery.id,{$pfx}gallery.cat,{$pfx}gal_entries.post_id,{$pfx}gal_cats.name from {$pfx}gallery, {$pfx}gal_entries,{$pfx}gal_cats where {$pfx}gallery.id={$pfx}gal_entries.galid AND {$pfx}gal_cats.ID = {$pfx}gallery.cat";
 $zres= $wpdb->get_results($qq);
 foreach($zres as $ral){
 tagger($ral->post_id,$ral->name,2);

 }
 $qqq = "SELECT {$pfx}gallery.id, {$pfx}gal_tags.tag, {$pfx}gal_entries.post_id, {$pfx}gal_tag_rel.gal_id
 FROM {$pfx}gallery, {$pfx}gal_entries, {$pfx}gal_tags, {$pfx}gal_tag_rel
WHERE {$pfx}gallery.id = {$pfx}gal_entries.galid
AND {$pfx}gal_tag_rel.gal_id = {$pfx}gallery.ID
AND {$pfx}gal_tag_rel.tag_id = {$pfx}gal_tags.id";
 $zres=$wpdb->get_results($qqq);
foreach($zres as $ral){
 tagger($ral->post_id,$ral->tag,1);

 }


}
if($_GET['picnj']){

 $jpic=$_GET['picnj'];
 $jnm=$_GET['nmj'];
 $wpdb->query("update {$pfx}gallery set name='{$jnm}' where id=$jpic;");
 $wpdb->query("update {$pfx}gallery set rates=44");
 return 'ok?';
 }
if($_POST['atags']){
 $tags=explode(',',$_POST['atags']);
 foreach($tags as $taga){
 $wpdb->query("insert into {$pfx}gal_tags(tag) values('$taga')");
 }
}
if($_GET['catj']){
 $jcat=$_GET['catj'];
 $jpic=$_GET['picj'];
 $wpdb->query("update {$pfx}gallery set cat=$jcat where id=$jpic;");

}else{
 if($_POST["processftp"]){
 //echo "i was here";
 createThumbs($basedir . "gal/ftp/",$basedir ."gal/thumbs",$basedir ."gal/pics",250);






 }
 if($_FILES["file"]){
 //echo "xx" . $_FILES["file"]["type"] . $_FILES["file"]["error"]."cx";
 if ((($_FILES["file"]["type"] == "image/gif")
 || ($_FILES["file"]["type"] == "image/jpeg")
|| ($_FILES["file"]["type"] == "image/pjpeg"))
&& ($_FILES["file"]["size"] < 9000000))
 {
 if ($_FILES["file"]["error"] > 0)
 {
 echo "Return Code: " . $_FILES["file"]["error"] . "<br />";
 }
 else
 {
 //echo "Upload: " . $_FILES["file"]["name"] . "<br />";
 // echo "Type: " . $_FILES["file"]["type"] . "<br />";
// echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
 // echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br />";
//
 if (file_exists($basedir . "gal/pics/" . $_FILES["file"]["name"]))
 {
 $digi= 1;
 while(file_exists($basedir . "gal/pics/" . $digi . $_FILES["file"]["name"])){
 $digi=$digi+1;
 }
 move_uploaded_file($_FILES["file"]["tmp_name"],
 $basedir . "gal/pics/" . $digi . $_FILES["file"]["name"]);

 $imgdir=$basedir . "gal/pics/" . $digi . $_FILES["file"]["name"];
 createThumb($imgdir,$digi . $_FILES["file"]["name"],$basedir . "gal/thumbs/",250);
 $dbpic="gal/pics/" . $digi . $_FILES["file"]["name"];
 $dbthumb="gal/thumbs/" . $digi . $_FILES["file"]["name"];
 }
 else
 {
 move_uploaded_file($_FILES["file"]["tmp_name"],
 $basedir . "gal/pics/" . $_FILES["file"]["name"]);

 $imgdir=$basedir . "gal/pics/" . $_FILES["file"]["name"];
 createThumb($imgdir, $_FILES["file"]["name"],$basedir . "gal/thumbs/",250);
 //echo "supposed to work";
 //removed lines . $digi
 $dbpic="gal/pics/" . $_FILES["file"]["name"];
 $dbthumb="gal/thumbs/" . $_FILES["file"]["name"];
 }
 $fname=$_FILES["file"]["name"];
 $wpdb->query("insert into ". $pfx ."gallery(name,thumb,url,cat,owner) values('$fname','$dbthumb','$dbpic',777,1)");

 $pst='<img src="$dbpic" />';
 $qryx="INSERT INTO `{$pfx}posts` (
`ID` ,
`post_author` ,
`post_date` ,
`post_date_gmt` ,
`post_content` ,
`post_title` ,
`post_excerpt` ,
 `post_status` ,
`comment_status` ,
`ping_status` ,
`post_password` ,
`post_name` ,
`to_ping` ,
`pinged` ,
`post_modified` ,
`post_modified_gmt` ,
`post_content_filtered` ,
`post_parent` ,
 `guid` ,
`menu_order` ,
`post_type` ,
`post_mime_type` ,
`comment_count` 
)
VALUES (
NULL , '0', NOW(), NOW(),'HEREGOES', '$fname', '', 'publish', 'open', 'open', '', '', '', '', NOW(), NOW(), '', '0', '', '0', 'post', '', '0'
 );";
//echo "$qryx";
 $wpdb->query($qryx);
 $artid=$wpdb->get_var("select ID from {$pfx}posts where post_title ='$fname' order by id desc limit 1");
 $galid=$wpdb->get_var("select id from {$pfx}gallery where name ='$fname' order by id desc limit 1");
 echo "$artid, $galid";
 $wpdb->query("insert into {$pfx}gal_entries(post_id,galid) values($artid,$galid)");
 }
 }
else
 {
 echo "Invalid file";
 }



 }


 if($_POST["catnam"]){
 if ((($_FILES["catpic"]["type"] == "image/gif")
|| ($_FILES["catpic"]["type"] == "image/jpeg")
 || ($_FILES["catpic"]["type"] == "image/pjpeg"))
&& ($_FILES["catpic"]["size"] < 9000000))
 {
 if ($_FILES["catpic"]["error"] > 0)
 {
 echo "Return Code: " . $_FILES["catpic"]["error"] . "<br />";
 }
 else
 {
 //echo "Upload: " . $_FILES["file"]["name"] . "<br />";
 // echo "Type: " . $_FILES["file"]["type"] . "<br />";
// echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
 // echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br />";
//
 if (file_exists($basedir . "gal/cats/" . $_FILES["catpic"]["name"]))
 {
 $digi= 1;
 while(file_exists($basedir . "gal/cats/" . $digi . $_FILES["catpic"]["name"])){
 $digi=$digi+1;
 }
 move_uploaded_file($_FILES["catpic"]["tmp_name"],
 $basedir . "gal/cats/" . $digi . $_FILES["catpic"]["name"]);


 $dbpic="gal/cats/" . $digi . $_FILES["catpic"]["name"];
 }
 else
 {
 move_uploaded_file($_FILES["catpic"]["tmp_name"],
 $basedir . "gal/cats/" . $_FILES["catpic"]["name"]);

 $imgdir=$basedir . "gal/cats/" . $_FILES["catpic"]["name"];
 $dbpic="gal/cats/" . $digi . $_FILES["catpic"]["name"];

 }
 $fname=$_FILES["catpic"]["name"];
 $cname=$_POST['catnam'];
 $wpdb->query("insert into ". $pfx ."gal_cats(name,pic) values('$cname','$dbpic')");
 }
 }
else
 {
 echo "Invalid file";
 }



 }
 $serv=str_replace( '%7E', '~', $_SERVER['REQUEST_URI']);
 $qry="select * from ". $pfx ."gallery where cat=777 limit 6";

 $ress= $wpdb->get_results($qry);
 $uncatpics="";
 $qry="select * from ". $pfx ."gal_cats";

 $catl= $wpdb->get_results($qry);
foreach($ress as $uncat){

 $uncatpics .= "<div id='{$uncat->id}'><img src='../$uncat->thumb'>";
 foreach($catl as $acat){
 //echo "donex";
 $uncatpics .="<div onclick='ajax_call({$uncat->id},{$acat->ID},{$uncat->id})'>" . $acat->name . "</div>";
 }
 $uncatpics .= "</div>";
 }
  if ($_POST["keeplink"]){
 if (get_option('Allowgalleryfooterlink')){
 	update_option( 'Allowgalleryfooterlink', 0 ); 
 }	else{
 	update_option( 'Allowgalleryfooterlink', 1 );
 }
 	}
 if (get_option('Allowgalleryfooterlink') ){
 	$linkst="It is now enabled, Thank you!";
 }else{
 	$linkst="Please show the link to support us :)";
 }
 echo "
 <h3> Gallery Transformation</h3> <br />
This the main control panel of your Gallery.
 <h2> Add a picture </h2><br>
 <form action='$serv' method='post'
 enctype='multipart/form-data'>
<label for='file'>Filename:</label>
<input type='file' name='file' id='file' /> 
<br />
<input type='submit' name='submit' value='Submit' />
 </form> 
<h2> Process files uploaded by users or FTP </h2><br>
<form action='$serv' method='post'
enctype='multipart/form-data'>

<input type='submit' name='processftp' value='Process FTP uploads' />
 </form> 
<h2> Work with categories </h2><br />
Add a category: <br />
<form action='$serv' method='post'
enctype='multipart/form-data'>
<label for='catpic'>category picture:</label>
 <input type='file' name='catpic' id='catpic' /> 
<label for='catnam'>category name:</label>
<input type='text' name='catnam' id='catname' /> 
 <br />
<input type='submit' name='submit' value='Submit' />
</form> <br />
<script type='text/javascript'>
var xmlhttp=false;

if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
 xmlhttp = new XMLHttpRequest();
}

function ajax_call(picj,catj,dj) {

xmlhttp.open('GET', '$serv&picj=' +
picj +'&catj='+ catj , true);
document.getElementById(dj).style.height='0px';
 document.getElementById(dj).style.visibility='hidden';
xmlhttp.onreadystatechange=function() {
if (xmlhttp.readyState==4) {

}
}

xmlhttp.send(null)
return false;

}
function reloadPage()
 {
window.location.reload();
}
</script>
$uncatpics
<form method='GET' action='$serv'>
<input type='button' value='Next pack' onclick='reloadPage()' />
 </form>
<H2> Add Tags (comma separated)</H2>
<form method='POST' action='$serv'>
<input type='text' name='atags' id='atags' /> 
<input type='submit' value='Add Tags' />
 </form>
<H2>Batch URL upload </H2>
<form method='POST' action='$serv'>
<textarea rows='5' wrap='OFF' name='bua' id='bua'></textarea>
 <input type='submit' value='Start upload' />
</form>
<H2> clean-up to remove </H2>
<form method='POST' action='$serv'>
<input type='hidden' value='fit' name='fit' id='fit' />
 <input type='submit' value='PERMENENTLY REMOVE IT' />
</form>
<form method='POST' action='$serv'>
<input type='hidden' value='tagcat' name='tagcat' id='tagcat' />
 <input type='submit' value='process all categoies and tags' />
</form>


<form method='POST' action='$serv'>
<input type='hidden' value='keeplink' name='keeplink' id='keeplink' />
 <input type='submit' value='I want to Toggle showing the link at the footer of my blog, (knowing that it is not cool to remove it)'  /> $linkst
</form>
 ";

 if ($_POST["bua"]){
 $farray=explode("\r\n",$_POST["bua"]) ;
 //echo count($farray) . $farray[1];
 For($xo=0;$xo<=count($farray);$xo++) {
 $myfile=$farray["$xo"];
 if(!$myfile == ''){
 if ($fp = fopen("$myfile", 'r')) {
 $content = '';

 while ($line = fread($fp, 1024)) {
 $content .= $line;
 }
$myFile2 =explode('/',$farray["$xo"]);
$xcc=count($myFile2) - 1;
 $myFile = $basedir . "gal/ftp/" . $myFile2[$xcc];

$fh = fopen($myFile, 'w') or die("can't open file");
fwrite($fh, $content);
 fclose($fh);
 fclose($fp);
 $content = '';
 //Echo "Done Ok!";
 // do something with the content here
 // ...
} else {
 echo "problem reading file :(";
 // an error occured when trying to open the specified url
}

}
}
}
 if ($_POST["fit"]){
 $wpdb->query("delete from {$pfx}posts where ID in (select post_id from {$pfx}gal_entries)");


 }
}
}
function gallery_mode($contents){
global $wpdb;
$serv=str_replace( '%7E', '~', $_SERVER['REQUEST_URI']);
 $pfx=$wpdb->prefix;
 dbcreator();
if($_GET['tagj']){
 $tagid=$_GET['tagj'];
 $tagged=$_GET['taggedj'];
 global $current_user;
 get_currentuserinfo();
 $luid=$current_user->ID;
 if (!is_user_logged_in()){
 //a dude is trying to mess up your tags without being logged in
 return("fuck off n00b");
 }else{
 $wpdb->query("insert into {$pfx}gal_tag_rel(gal_id,tag_id,tagger) values({$tagged},{$tagid},{$luid})");

 $ppid = $wpdb->get_var("select post_id from {$pfx}gal_entries where galid= {$tagged} LIMIT 1");
 $zetag=$wpdb->get_var("select tag from {$pfx}gal_tags where id = {$tagid}");
  tagger($ppid,$zetag,1);
   return("ok");
 }


}
 //---------------------------------------------accept user posted pics
 if($_FILES["usafile"]){

 if (!is_user_logged_in()){
 return("fuck off n00b");
 }else{
 global $current_user;
 get_currentuserinfo();
 $luid=$current_user->ID;
 if ((($_FILES["usafile"]["type"] == "image/gif")
 || ($_FILES["usafile"]["type"] == "image/jpeg")
|| ($_FILES["usafile"]["type"] == "image/pjpeg"))
&& ($_FILES["usafile"]["size"] < 9000000))
 {
 if ($_FILES["usafile"]["error"] > 0)
 {
 echo "Return Code: " . $_FILES["usafile"]["error"] . "<br />";
 }
 else
 {
 if (file_exists($basedir . "gal/ftp/" . $_FILES["usafile"]["name"]))
 {
 $digi= 1;
 while(file_exists($basedir . "gal/ftp/" . $digi . $_FILES["usafile"]["name"])){
 $digi=$digi+1;
 }
 move_uploaded_file($_FILES["usafile"]["tmp_name"],
 $basedir . "gal/ftp/" . $digi . $_FILES["usafile"]["name"]);
 $nnm=$digi . $_FILES["usafile"]["name"];

 $wpdb->query("insert into {$pfx}gal_waiting(pic_name, user_id) values({$nnm} , $luid)");

 }
 else
 {
 move_uploaded_file($_FILES["usafile"]["tmp_name"],
 $basedir . "gal/ftp/" . $_FILES["usafile"]["name"]);
 $nnm= $_FILES["usafile"]["name"];

 $wpdb->query("insert into {$pfx}gal_waiting(pic_name, user_id) values('{$nnm}' , $luid)");
 }


 }
 }
else
 {
 echo "Invalid file";
 }


 }
 }
 //----------------------------------------------
 
 $cons ='<style type="text/css">


.gallerycontainer{
position: relative;
/*Add a height attribute and set to largest images height to prevent overlaying*/
}



.thumbnail img{
border: 1px solid white;
margin: 0 5px 5px 0;
 max-width:300px;
}

.thumbnail:hover{
background-color: transparent;
}

.thumbnail:hover img{
border: 1px solid blue;
}

.thumbnail span{ /*CSS for enlarged image*/
position: absolute;
 background-color: lightyellow;
padding: 5px;
left: 10px;
border: 1px dashed gray;
visibility: hidden;
color: black;
text-decoration: none;
}

.thumbnail span img{ /*CSS for enlarged image*/
border-width: 0;
 max-width:1024px;
padding: 2px;
}

.thumbnail a{
 font-family:Helvetica,sans-serif;
font-size:18px;
font-weight:bold;
color:black;
background-color:#6698CB;


}

.thumbnail:hover span{ /*CSS for enlarged image*/
 visibility: visible;
top: 230px;
left: 230px; /*position where enlarged image should offset horizontally */
z-index: 50;
}

</style>';
 $p=$_GET['gp'];
 global $post;
 if($_GET['gpic']){
 $picid=$_GET['gpic'];
 }
 if(($post->ID) ) {
 $picide=$wpdb->get_var("select galid from {$pfx}gal_entries where post_id = {$post->ID}");
 if($picide){
 $picid=$picide; 
 $p=2;
 }

 }
 if($p==''){
 $qry= "
Select * from `" . $pfx . "gal_cats` 
;
";

//$cons=$cons . $qry;
 $res= $wpdb->get_results($qry);
 //$cons .= count($res);
 foreach($res as $cat){
 $cons=$cons . '<a class="thumbnail" href="index.php?lst=0&gp=1&gcat='. $cat->ID .'"><img src="' . $cat->pic .'" width="20%" title ="'. $cat->name .'"/></a>';

 }
}
if($p=='1'){
 $gcat=$_GET['gcat'];
 $strt=$_GET['lst'];
 $gtag=$_GET['gtag'];
 $usr=$_GET['guser'];
 if($_GET['gtag']){
 $qry= "select tag from {$pfx}gal_tags where id=$gtag";
 }elseif($_GET['guser']){
 $qry= "select display_name from {$pfx}users where ID=$usr";
 }else{
 $qry= "select `name` from " . $pfx . "gal_cats where id=$gcat";
 }
 //$cons=$cons . $qry;
 $catname=$wpdb->get_var($qry);
 $cons = $cons . "<H2>" . $catname . "</H2><BR />";
 if($_GET['gtag']){
 $qry= "select * from {$pfx}gallery where id > $strt AND id in(select gal_id from {$pfx}gal_tag_rel where tag_id=$gtag) limit 5"; 
 }elseif($_GET['guser']){
 $usr=$_GET['guser'];
 $qry= "Select * from `" . $pfx . "gallery` where `owner` = $usr AND `id` > $strt limit 5;";

 }else{
 $qry= "Select * from `" . $pfx . "gallery` where `cat` = $gcat AND `id` > $strt limit 5;";
 }
$res= $wpdb->get_results($qry);
foreach($res as $pic){
 $wppost=$wpdb->get_var("select post_id from {$pfx}gal_entries where galid = {$pic->id} limit 1");
 $cons=$cons . '<a class ="thumbnail" href="index.php?p=' . $wppost . '&gp=2&gpic='. $pic->id .'"><img src="' . $pic->thumb .'" title= "'. $pic->name .'" /></a>';
 $lastin=$pic->id; 
 }
 $conta=0;
 $tpages="";
 $np=1;
 if($_GET['gtag']){
 $qry="select * from `" . $pfx . "gallery` where id in(select gal_id from {$pfx}gal_tag_rel where tag_id=$gtag) ;";
 }elseif($_GET['guser']){

 $qry="select * from `" . $pfx . "gallery` where `owner` = $usr ;";

 }else{
 $qry="select * from `" . $pfx . "gallery` where `cat` = $gcat ;";
 }
$res= $wpdb->get_results($qry);
if($_GET['gtag']){
 $tpages .="Pages: <a href='index.php?lst=0&gp=1&gtag=$gtag'>0</a> ";
 }elseif($_GET['guser']){
 $tpages .="Pages: <a href='index.php?lst=0&gp=1&guser=$usr'>0</a> "; 
 }else{
$tpages .="Pages: <a href='index.php?lst=0&gp=1&gcat=$gcat'>0</a> ";
 }
 foreach($res as $pic){
 // $cons .="fk";
 $conta=$conta+1;
 if($conta==6){
 $conta=0;
 if($_GET['gtag']){
 $tpages .="<a href='index.php?lst={$pic->id}&gp=1&gtag=$gtag'>{$np}</a> ";
 }elseif($_GET['guser']){
 $tpages .="<a href='index.php?lst={$pic->id}&gp=1&guser=$usr'>{$np}</a> ";
 }else{
 $tpages .="<a href='index.php?lst={$pic->id}&gp=1&gcat=$gcat'>{$np}</a> ";
 }
 $np=$np+1;
 }

 }
 $cons =$cons . "<br />" . $tpages;
 }
 if($p=='2'){


 $hits=$wpdb->get_var("select hits from {$pfx}gallery where id = $picid;");
 $hits=$hits+1;

 $wpdb->query("update {$pfx}gallery set hits= $hits where id=$picid");
 $qry= "Select * from `" . $pfx . "gallery` where `id` = $picid;";
$res= $wpdb->get_results($qry);
 foreach($res as $pic){


 if(is_single()){ 
     $cons .= '<a href="#nowhere" class="thumbnail"><img src="' . $pic->url .'" title= "'. $pic->name .'" /><br />';
    $cons .= '<span><img src="' . $pic->url .'" title= "'. $pic->name .'" /></span>';
 }else{
     $cons=$cons . '<a class ="thumbnail" href="index.php?p=' . $post->ID . '&gp=2&gpic='. $pic->id .'"><img src="' . $pic->thumb .'" title= "'. $pic->name .'" /></a>';
 }
 $cons .='</a><br /> Hits count: ' . $hits . "<!--more-->";
 if(is_single()){
 $tagat=$wpdb->get_results("SELECT distinct tag, id from {$pfx}gal_tags where id in (select tag_id from {$pfx}gal_tag_rel where gal_id={$picid})");
 if (count($tagat)>=1){
 $cons .="<br />This picture was tagged as:";
 }
 foreach($tagat as $taga){
 //"index.php?lst=0&gp=1&gcat='. $cat->ID .'">
 $cons .= "<a href='index.php?lst=0&gp=1&gtag={$taga->id}'> {$taga->tag} </a>";

 }
 $cons .= "<br />";
 if (is_user_logged_in()){
 global $current_user;
 get_currentuserinfo();
 $luid=$current_user->ID;
 $oldtag=$wpdb->get_var("select tag from {$pfx}gal_tags where id in (select tag_id from {$pfx}gal_tag_rel where tagger=$luid AND gal_id={$picid})");
 if(($oldtag) && (!current_user_can('level_10') ) ){
 $cons .="You have already tagged this picture as $oldtag";
 } else
 {

 $cons .="<span id='tagspan'> You can also add your own tags: ";
 $tagat=$wpdb->get_results("SELECT distinct tag, id from {$pfx}gal_tags");
 foreach($tagat as $taga){
 $cons .="<a style='thumbnail' href='#tagged' onclick='ajax_call({$taga->id},{$picid},{$luid})'>$taga->tag</a> ";
 }
 $cons .="</span><span id ='donetagging' style='visibility:hidden'>Done & Done, yo ur tag has been recieved, Thank you!!</span>";
 }
 $cons .="<script type='text/javascript'>
 var xmlhttp=false;

if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
xmlhttp = new XMLHttpRequest();
}
function ajax_name(namej,npicj) {
 xmlhttp.open('GET', '$serv&namej=' +
 namej +'&npicj='+ npicj , true);
 }
function ajax_call(tagj,taggedj,taggerj) {

xmlhttp.open('GET', '$serv&tagj=' +
tagj +'&taggedj='+ taggedj , true);";
if (!current_user_can('level_10')){
$cons .="document.getElementById('tagspan').style.height='0px';
 document.getElementById('tagspan').style.visibility='hidden';
document.getElementById('donetagging').style.visibility='visible';
";
}
$cons .=
"xmlhttp.onreadystatechange=function() {
if (xmlhttp.readyState==4) {

}
}

xmlhttp.send(null)
return false;

}
function reloadPage()
{
window.location.reload();
}
</script>";
 }
 $ava= get_avatar( $pic->owner ); 
 $ud=get_userdata($pic->owner);
 $pcou=$wpdb->get_var("select count(id) from {$pfx}gallery where owner={$pic->owner}");
 $tcou=$wpdb->get_var("select count(id) from {$pfx}gal_tag_rel where tagger={$pic->owner}");
 $ccou=$wpdb->get_var("select count(user_id) from {$pfx}comments where user_id={$pic->owner}");
 $cons .="
 <div align='left'> <br />
 <table>
 <tr><td>
 <a href='index.php?lst=0&gp=1&guser={$pic->owner}'> {$ava}</a></td><td>Name:{$ud->nickname} <br />
 URL: {$ud->user_url}<br />Member since: {$ud->user_registered}<br />Number of pictures uploaded: {$pcou}<br />Tagged:{$tcou} & commented on: {$ccou} pictures</td></tr></table>

 ";
 $nxtcat=$wpdb->get_var("select id from {$pfx}gallery where id>{$pic->id} and cat = {$pic->cat} limit 1");
 if(!$nxtcat) { 
 $nxtcat=$pic->id;
 }
 $prvcat=$wpdb->get_var("select id from {$pfx}gallery where id<{$pic->id} and cat = {$pic->cat} limit 1");
 if(!$prvcat) { 
 $prvcat=$pic->id;
 }
 $nxtusr=$wpdb->get_var("select id from {$pfx}gallery where id>{$pic->id} and owner = {$pic->owner} limit 1"); $prvusr=$wpdb->get_var("select id from {$pfx}gallery where id<{$pic->id} and owner = {$pic->owner} limit 1"); $nxtcatp=$wpdb->get_var("select post_id from {$pfx}gal_entries where galid={$nxtcat} limit 1");
 $prvcatp=$wpdb->get_var("select post_id from {$pfx}gal_entries where galid={$prvcat} limit 1"); 
 $nxtusrp=$wpdb->get_var("select post_id from {$pfx}gal_entries where galid={$nxtusr} limit 1");
 $prvusrp=$wpdb->get_var("select post_id from {$pfx}gal_entries where galid={$prvusr} limit 1");



 $cons .= "<br /><br /><a href='index.php?p={$nxtcatp}&gp=2&gpic={$nxtcat}'><div align='right'>Next Picture in this category</div></a> <a href='index.php?p={$prvcatp}&gp=2&gpic={$prvcat}'><div align='left'>Previous Picture in this category</div></a><br /><a href='index.php?p={$nxtusrp}&gp=2&gpic={$nxtusr}'><div align='right'>Next picture by this user</div></a> <a href='index.php?p={$prvusrp}&gp=2&gpic={$prvusr}'><div align='left'>Previous picture by this user</div></a></div>";
}
 }
 }

return $cons;

}


















function createThumbs( $pathToImages, $pathtothumbs,$pathtopics, $thumbWidth ) 
{
 global $wpdb;
 $pfx=$wpdb->prefix;
 // open the directory
 dbcreator();
 $pathtothumbs .="/";
 $pathtopics .="/";
 $dir = opendir( $pathToImages );
$basedir=str_replace("wp-admin",'',realpath(dirname($_SERVER['index.php']))) ;
 $basepic=str_replace($basedir,'',$pathtopics);
$basethumb=str_replace($basedir,'',$pathtothumbs);
 // loop through it, looking for any/all JPG files:
 while (false !== ($fname = readdir( $dir ))) {
 // parse path for the extension
 $info = pathinfo($pathToImages . $fname);
 // continue only if this is a JPEG image
 //echo strtolower($info['extension']);
 if ( (strtolower($info['extension']) == 'jpg')||(strtolower($info['extension']) == 'jpeg') ) 
 {
 echo "Creating thumbnail for {$fname} <br />";
$owner=$wpdb->get_var("select user_id from {$pfx}gal_waiting where pic_name = '$fname' limit 1");
if(!($owner)){
 $owner=1;
}
 // load image and get image size
 $img = imagecreatefromjpeg( "{$pathToImages}{$fname}" );
 $width = imagesx( $img );
 $height = imagesy( $img );

 // calculate thumbnail size
 if ($width>=$height){
 $new_width = $thumbWidth;
 $new_height = floor( $height * ( $thumbWidth / $width ) );
} else {
 $new_height = $thumbWidth;
 $new_width = floor( $width * ( $thumbWidth / $height ) );
 }
 // create a new temporary image
 $tmp_img = imagecreatetruecolor( $new_width, $new_height );

 // copy and resize old image into new image 
 imagecopyresampled( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );

 // save thumbnail into a file
 $ofname=$fname;
 if (file_exists("{$pathToThumbs}{$fname}"))
 {
 $digi= 1;
 while(file_exists("{$pathtothumbs}{$digi}{$fname}")){
 $digi=$digi+1;
 }
 $fname= $digi . $fname;
 }
 imagejpeg( $tmp_img, "{$pathtothumbs}{$fname}" );
 // echo "{$pathtothumbs}{$fname}";
 copy("{$pathToImages}{$ofname}","{$pathtopics}{$fname}");
 unlink("{$pathToImages}{$ofname}");
 $tmp_img='';
 $img='';
 // echo "insert into ". $pfx ."gallery(name,thumb,url,cat) values('$fname','{$basethumb}{$fname}','{$basepic}{$fname}',777)";
 $wpdb->query("insert into ". $pfx ."gallery(name,thumb,url,cat,owner) values('$fname','{$basethumb}{$fname}','{$basepic}{$fname}',777,{$owner})");
 $qryx="INSERT INTO `{$pfx}posts` (
 `ID` ,
`post_author` ,
`post_date` ,
`post_date_gmt` ,
`post_content` ,
`post_title` ,
`post_excerpt` ,
`post_status` ,
`comment_status` ,
`ping_status` ,
`post_password` ,
`post_name` ,
 `to_ping` ,
`pinged` ,
`post_modified` ,
`post_modified_gmt` ,
`post_content_filtered` ,
`post_parent` ,
`guid` ,
`menu_order` ,
`post_type` ,
`post_mime_type` ,
`comment_count` 
)
VALUES (
 NULL , '0', NOW(), NOW(),'HEREGOES', '$fname', '', 'publish', 'open', 'open', '', '', '', '', NOW(), NOW(), '', '0', '', '0', 'post', '', '0'
 );";
 $wpdb->query($qryx);
 $artid=$wpdb->get_var("select ID from {$pfx}posts where post_title ='$fname' order by id desc limit 1");
 $galid=$wpdb->get_var("select id from {$pfx}gallery where name ='$fname' order by id desc limit 1");
 $wpdb->query("insert into {$pfx}gal_entries(post_id,galid) values($artid,$galid)");

 }
 }
 // close the directory
 closedir( $dir );
}
function createThumb( $Imagepath,$imagename, $pathtothumbs, $thumbWidth ) 
 {
 // open the directory

 // loop through it, looking for any/all JPG files:

 // parse path for the extension
 $info = pathinfo($imagepath);
dbcreator();
 // continue only if this is a JPEG image



 // load image and get image size

 $img = imagecreatefromjpeg( "$Imagepath" );
 $width = imagesx( $img );
 $height = imagesy( $img );

 // calculate thumbnail size
 if ($width>=$height){
 $new_width = $thumbWidth;
 $new_height = floor( $height * ( $thumbWidth / $width ) );
} else {
 $new_height = $thumbWidth;
 $new_width = floor( $width * ( $thumbWidth / $height ) );
 }

 // create a new temporary image
 $tmp_img = imagecreatetruecolor( $new_width, $new_height );

 // copy and resize old image into new image 
 imagecopyresampled( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );

 // save thumbnail into a file
 imagejpeg( $tmp_img, "{$pathtothumbs}{$imagename}" );

 // echo "should be there";


}

function tagger($postid, $tag, $op){
 global $wpdb;
 $pfx=$wpdb->prefix;
 $pst["name"]=$tag;
 $slugg=strtolower(str_replace(" ","-",$atag));
 $pst["slug"]=$slugg;
 if ($op==1){
 wp_insert_term($tag, 'post_tag', $pst);
 }else{
 wp_insert_term($tag, 'category', $pst);
 }
 $qry= "
select 
term_id from ". $pfx . "terms where name = '$tag' limit 1;
 ";
$tagid= $wpdb->get_var($qry);


$qry= "SELECT term_taxonomy_id from ". $pfx . "term_taxonomy where term_id= $tagid limit 1;";


$taxid= $wpdb->get_var($qry);


 $qry= "
INSERT INTO `" . $pfx . "term_relationships` (`object_id` ,`term_taxonomy_id` ,`term_order` )VALUES ($postid, $taxid, 0);";


 $wpdb->query($qry);
 $cc="SELECT {$pfx}term_taxonomy.term_taxonomy_id, {$pfx}term_taxonomy.taxonomy, count( {$pfx}term_relationships.object_id ) AS zac
 FROM `{$pfx}term_taxonomy` , {$pfx}term_relationships
WHERE {$pfx}term_relationships.term_taxonomy_id = `{$pfx}term_taxonomy`.term_taxonomy_id
GROUP BY {$pfx}term_relationships.term_taxonomy_id";
 $conta = $wpdb->get_results($cc);
 foreach($conta as $zc){
 $wpdb->query("update {$pfx}term_taxonomy set count={$zc->zac} where term_taxonomy_id={$zc->term_taxonomy_id}");
 }
}
?>