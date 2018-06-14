<?php
if (function_exists('mb_internal_encoding')) {
	mb_internal_encoding("UTF-8");
}
date_default_timezone_set('UTC');


include "../app/bootstrap.php";

try {
	AperireBootstrap::boot();
	AperireBootstrap::run();
}
catch (Exception $e) {
	echo 'Error!';
	print_r($e);
}

