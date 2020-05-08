<?php
$output_dir = "list";
$repos = [["تاهیر تۆفیق", "list_tahir_tofiq",
	   "https://allekok.github.io/tahir-tofiq"],
	  ["حەسەن زیرەک", "list_zirek",
	   "https://allekok.github.io/zirek"],
	  ["عەلی مەردان", "list_eli_merdan",
	   "https://allekok.github.io/eli-merdan"],
	  ["محەممەد ماملێ", "list_mamle",
	   "https://allekok.github.io/mamle"],
	  ["خدر قادری", "list_xdr_qadri",
	   "https://allekok.github.io/xdr-qadri"],
	  ["KurdishLyricsCorpus", "list_KLC",
	   "https://allekok.github.io/KurdishLyricsCorpus"]];

function list_tahir_tofiq ($repo) { return list_allekok($repo); }
function list_zirek ($repo) { return list_allekok($repo); }
function list_eli_merdan ($repo) { return list_allekok($repo); }
function list_mamle ($repo) { return list_allekok($repo); }
function list_xdr_qadri ($repo) {
	$albums = download(list_allekok_url($repo[2]));
	$albums = explode("\n", $albums);
	$i = 0;
	foreach($albums as $album) {
		$url = "$repo[2]/".urlencode("دەنگ")."/".
		       str_replace("+", "%20", urlencode($album))."/list.txt";
		$list = explode("\n", download($url));
		foreach($list as $item) {
			$new_list[$i] = [
				$repo[0], /* Singer */
				sanitize($album)."/".sanitize($item), /* Song */
				"$repo[2]/دەنگ/$album/$item", /* Song Url */
				"", /* Description */
			];
			$i++;
		}
	}
	return $new_list;
}
function list_KLC ($repo) {
	function _as_string ($amb) {
		if(!is_array($amb)) return $amb;
		$str = "";
		foreach($amb as $o) {
			$str .= "\n";
			if(is_array($o)) $str .= _as_string($o);
			else $str .= trim($o);
		}
		return trim($str);
	}
	function as_string ($amb) {
		return preg_replace("/\n+/u", "\n", _as_string($amb));
	}
	$json = json_decode(download("$repo[2]/KurdishLyricsCorpus.json"), true);
	$list = $json["lyrics"];
	$new_list = [];
	$i = 0;
	foreach($list as $item) {
		if(@!$item["div"]["audio"]) continue;
		$new_list[$i++] = [
			as_string($item["div"]["singer"]), /* Singer */
			as_string($item["div"]["head"]), /* Song */
			"$repo[2]/audio/{$item["@id"]}.mp3", /* Song Url */
			as_string($item["div"]["lg"]), /* Description */
			/* Description: In this case lyrics */
		];
	}
	return $new_list;
}
function list_allekok ($repo) {
	$list = download(list_allekok_url($repo[2]));
	$list = explode("\n", $list);
	foreach($list as $i => $item) {
		$list[$i] = [
			$repo[0], /* Singer */
			sanitize($item), /* Song */
			"$repo[2]/دەنگ/$item", /* Song Url */
			"", /* Description */
		];
	}
	return $list;
}
function list_allekok_url($repo_url) {
	return "$repo_url/" . urlencode("دەنگ") . "/list.txt";
}
function download ($url, $timeout=1, $times=-1) {
	while(!($res = file_get_contents($url)) &&
	      $times-- !== 0) sleep($timeout);
	return trim($res);
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
	/* 'mp3' has character:'3' in it so we will 
	   remove it before applying 'kurdish_numbers' */
	$song_name = str_ireplace([".mp3"], "", $song_name);
	$song_name = kurdish_numbers($song_name);
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
	$song_name = preg_replace("/\s+/u", " ", trim($song_name));
	return $song_name;
}
function str_replace_pos ($from, $to, $str, $pos) {
	return mb_substr($str, 0, $pos) . $to .
	       mb_substr($str, $pos + mb_strlen($from));
}
function kurdish_numbers ($s) {
	return str_replace(["1","2","3","4","5","6","7","8","9","0"],
			   ["١","٢","٣","٤","٥","٦","٧","٨","٩","٠"],$s);
}

/* Collecting */
$id = 0;
$desc = "";
$global_list = [];
foreach($repos as $repo) {
	$list = $repo[1]($repo);
	$global_list = array_merge($global_list, $list);
	echo "$repo[0]\tAdded.\n";
}

/* Saving */
rmdir_($output_dir);
mkdir($output_dir, 0755, true);
foreach($global_list as $i => $_) {
	$item = $global_list[$i];
	$meta = implode("\n\n", $item);
	$output = "$output_dir/$i";
	file_put_contents($output, $meta);
}

/* server.js */
$db_path = "server/$output_dir";
$global_list_len = count($global_list);
file_put_contents("../client/server.js", "
const dbPath = '{$db_path}'
const numberOfSongs = {$global_list_len}
");

echo "DONE.\n";
?>
