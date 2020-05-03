<?php
/* Read CSS */
$CSS = file_get_contents("sima.css");
$CSS = "<style>$CSS</style>";

/* Read JS */
$JS = file_get_contents("server.js");
$JS .= file_get_contents("main.js");
$JS = "<script>$JS</script>";

/* Read INDEX */
$IDX = file_get_contents("index.html");

/* Replace */
$IDX = str_replace(["{style}", "{script}"],
		   [$CSS, $JS], $IDX);

/* Write INDEX */
file_put_contents("../index.html", $IDX);
?>
