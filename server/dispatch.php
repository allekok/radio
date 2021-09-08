<?php
/* Globals */
$output_dir = "list";
$repos = [
	["تاهیر تۆفیق",
	 "list_allekok",
	 "https://allekok.github.io/tahir-tofiq"],
	["حەسەن زیرەک",
	 "list_allekok",
	 "https://allekok.github.io/zirek"],
	["عەلی مەردان",
	 "list_allekok",
	 "https://allekok.github.io/eli-merdan"],
	["محەمەد ماملێ",
	 "list_allekok",
	 "https://allekok.github.io/mamle"],
	["خدر قادری",
	 "list_xdr_qadri",
	 "https://allekok.github.io/xdr-qadri"],
	["gorani-kurdi",
	 "list_gorani_kurdi",
	 "https://allekok.github.io/gorani-kurdi"]
];

/* Functions */
function repo_name($repo) {
	return $repo[0];
}
function repo_fun($repo) {
	return $repo[1];
}
function repo_url($repo) {
	return $repo[2];
}
function list_xdr_qadri($repo) {
	$albums = download(list_allekok_url(repo_url($repo)));
	$albums = explode("\n", $albums);
	$new_list = [];
	foreach($albums as $album) {
		$url = repo_url($repo) . "/" .
		       urlencode("دەنگ") . "/" .
		       str_replace("+", "%20", urlencode($album)) .
		       "/list.txt";
		$list = explode("\n", download($url));
		foreach($list as $item) {
			$new_list[] = [
				repo_name($repo),
				sanitize($album) . "/" .
				sanitize($item),
				repo_url($repo) . "/دەنگ/$album/$item",
				"",
			];
		}
	}
	return $new_list;
}
function list_gorani_kurdi($repo) {
	function _as_string($amb) {
		if(!is_array($amb))
			return $amb;
		$str = "";
		foreach($amb as $o) {
			$str .= "\n";
			if(is_array($o))
				$str .= _as_string($o);
			else
				$str .= trim($o);
		}
		return trim($str);
	}
	function as_string($amb) {
		return preg_replace("/\n+/u", "\n", _as_string($amb));
	}
	$json = json_decode(download(
		repo_url($repo) . "/KurdishLyricsCorpus.json"), true);
	$list = $json["lyrics"];
	$new_list = [];
	foreach($list as $item) {
		if(@!$item["div"]["audio"])
			continue;
		$new_list[] = [
			as_string($item["div"]["singer"]),
			as_string($item["div"]["head"]),
			repo_url($repo) . "/audio/{$item["@id"]}.mp3",
			as_string($item["div"]["lg"]),
		];
	}
	return $new_list;
}
function list_allekok($repo) {
	$list = download(list_allekok_url(repo_url($repo)));
	$list = explode("\n", $list);
	foreach($list as $i => $item) {
		$list[$i] = [
			repo_name($repo),
			sanitize($item),
			repo_url($repo) . "/دەنگ/$item",
			"",
		];
	}
	return $list;
}
function list_allekok_url($repo_url) {
	return "$repo_url/" . urlencode("دەنگ") . "/list.txt";
}
function download($url, $timeout=1, $times=-1) {
	while(!($res = file_get_contents($url)) && $times--)
		sleep($timeout);
	return trim($res);
}
function rmdir_rec($path) {
	$files = scandir($path);
	foreach($files as $file) {
		if(in_array($file, [".", ".."]))
			continue;
		$file_path = "$path/$file";
		if(is_dir($file_path))
			rmdir_rec($file_path);
		else
			unlink($file_path);
	}
	rmdir($path);
}
function sanitize($song_name) {
	$song_name = str_ireplace([".mp3", ".m4a"], "", $song_name);
	$song_name = kurdish_numbers($song_name);
	$song_name_len = mb_strlen($song_name);
	for($i = 0; $i < $song_name_len; $i++) {
		$c = mb_substr($song_name, $i, 1);
		if(in_array($c, [":", "-"]))
			continue;
		$uc = mb_ord($c);
		if($uc >= 1536 && $uc <= 1791) /* Unicode Arabic Range */
			continue;
		$song_name = str_replace_pos($c, " ", $song_name, $i);
	}
	$song_name = preg_replace("/\s+/u", " ", trim($song_name));
	return $song_name;
}
function str_replace_pos($from, $to, $str, $pos) {
	return mb_substr($str, 0, $pos) . $to .
	       mb_substr($str, $pos + mb_strlen($from));
}
function kurdish_numbers($s) {
	return str_replace(["1","2","3","4","5","6","7","8","9","0"],
			   ["١","٢","٣","٤","٥","٦","٧","٨","٩","٠"],
			   $s);
}

/* Collecting */
$id = 0;
$desc = "";
$global_list = [];
foreach($repos as $repo) {
	$list = repo_fun($repo)($repo);
	$global_list = array_merge($global_list, $list);
	echo repo_name($repo). "\tAdded.\n";
}

/* Saving */
rmdir_rec($output_dir);
mkdir($output_dir, 0755, true);
foreach($global_list as $i => $item) {
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
