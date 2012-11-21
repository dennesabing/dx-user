<?php

if (file_exists(__DIR__ . '/bootstrap.local.php'))
{
	include __DIR__ . '/bootstrap.local.php';
}
else
{
	if(file_exists(__DIR__ . '../../test/bootstrap.php')){
		include __DIR__ . '../../test/bootstrap.php';
	}
}
