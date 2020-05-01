<?php
$output_dir = "list";
$repos = [["https://allekok.github.io/tahir-tofiq",
	   "تاهیر تۆفیق"],
	  ["https://allekok.github.io/zirek",
	   "حەسەن زیرەک"],
	  ["https://allekok.github.io/eli-merdan",
	   "عەلی مەردان"],
	  ["https://allekok.github.io/mamle",
	   "محەممەد ماملێ"]];
function list_url($repo_url) {
	return "$repo_url/" .
	       urlencode("دەنگ") .
	       "/list.txt";
}
function song_url($repo_url, $song_name) {
	return "$repo_url/دەنگ/$song_name";
}
function download ($url, $timeout=1, $times=-1) {
	while(!($res = file_get_contents($url)) &&
	      $times-- !== 0) sleep($timeout);
	return $res;
}
function randomize ($list) {
	$last_item = count($list) - 1;
	$new_list = [];
	for($i = 0; $i <= $last_item; ) {
		$new_i = mt_rand(0, $last_item);
		if(isset($new_list[$new_i])) continue;
		else $new_list[$new_i] = $list[$i++];
	}
	return $new_list;
}
function rmdir_ ($path) {
	$files = scandir($path);
	foreach($files as $file) {
		$file_path = "$path/$file";
		if(in_array($file, [".",".."]))	continue;
		if(is_dir($file_path)) rmdir_($file_path);
		else unlink($file_path);
	}
	rmdir($path);
}
function sanitize ($song_name) {
	$song_name_len = mb_strlen($song_name);
	for($i = 0; $i < $song_name_len; $i++) {
		$c = mb_substr($song_name, $i, 1);
		if(in_array($c, [":", "-"])) continue;
		$uc = mb_ord($c);
		if($uc >= 1536 && $uc <= 1791) /* Unicode Arabic Range */
			continue;
		else
			$song_name = str_replace_pos($c, " ", $song_name, $i);
	}
	$song_name = preg_replace("/\s+/u", " ", $song_name);
	return $song_name;
}
function str_replace_pos ($from, $to, $str, $pos) {
	return mb_substr($str, 0, $pos) . $to .
	       mb_substr($str, $pos + mb_strlen($from));
}

$id = 0;
$desc = "";
$global_list = [];

foreach($repos as $repo) {
	$list = trim(download(list_url($repo[0])));
	$list = explode("\n", $list);
	foreach($list as $i => $item) {
		$list[$i] = [
			$id, /* Unique Id */
			$repo[1], /* Singer */
			sanitize($item), /* Song Name */
			song_url($repo[0], $item),
			$desc, /* (Optional)Description */
		];
		$id++;
	}
	$global_list = array_merge($global_list, $list);
	echo "$repo[1]\t$repo[0]\tDownloaded.\n";
}

$global_list = randomize($global_list);

rmdir_($output_dir);
mkdir($output_dir, 0755, true);

foreach($global_list as $i => $_) {
	$item = $global_list[$i];
	$meta = implode("\n", $item);
	$output = "$output_dir/$i";
	file_put_contents($output, $meta);
}

$db_path = "server/$output_dir";
$epoch = time();
$global_list_len = count($global_list);
file_put_contents("../client/server.js", "
const dbPath = '{$db_path}'
const epochTime = {$epoch}
const numberOfSongs = {$global_list_len}
");

echo "DONE.\n";
?>
