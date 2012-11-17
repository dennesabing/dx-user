<?php

if (file_exists(__DIR__ . '/bootstrap.local.php'))
{
	include_once __DIR__ . '/bootstrap.local.php';
}
else
{
	if(file_exists(__DIR__ . '/../../../test/bootstrap.php')){
		include_once __DIR__ . '/../../../test/bootstrap.php';
	}
}
