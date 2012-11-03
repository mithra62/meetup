<?php
if(count($errors) >= 1)
{		
	foreach($errors AS $error)
	{
		echo '<div id="m62_system_error">';
		$replace = array('#config_url#');
		$paths = $url_base.'settings';
		$str = str_replace($replace, $paths, lang($error));
		echo $str;
		if(count($errors) > 1)
		{
			echo '<br />';
		}
		echo '</div>';
	}
}


$parts = parse_url($_SERVER['HTTP_HOST']);
$url = 'http://';
if(isset($parts['scheme']) && $parts['scheme'] == 'https')
{
	$url = 'https://';
}	
$url .= 'mithra62.com';
?>
<style>
#m62_system_error
{
	border:1px solid #bf0012;
	background:#ffbc9f;
	padding:15px 45px 15px 15px;
	font-family: Arial, Helvetica, sans-serif;
	color:#18362D;
	font-size:18px;
	margin:0 0 10px 0;
}
#m62_system_error a{
	color:#a10a0a;
	font-size:14px;
}

</style>
<link href="<?php echo $theme_folder_url; ?>third_party/cartthrob/css/cartthrob.css" rel="stylesheet" type="text/css" />