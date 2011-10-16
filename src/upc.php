<?php
/**
 * unencrypted package checksum calculation example implementation
 *
 * This file is part of the Contao rfccc-1 <https://github.com/Discordier/Contao-ER3>
 * This file is licensed under the Creative Commons Attribution-ShareAlike 3.0 Unported License <http://creativecommons.org/licenses/by-sa/3.0/legalcode>
 */

// the path to calculate checksum
$path = dirname(dirname(__FILE__)) . '/example/hello_world';

// output path
echo 'path: ' . $path . "\n";

// list of files
$files = array();

// walk over the path
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::LEAVES_ONLY);
foreach ($iterator as $file)
{
	if ($file->isFile())
	{
		$files[] = $file->getPathname();
	}
}

// sort file list
sort($files);

// output file list
echo 'list of files:' . "\n";
foreach ($files as $file)
{
	echo $file . "\n";
}

// calculate checksum
$hash = hash_init('sha256');
foreach ($files as $file)
{
	hash_update_file($hash, $file);
}
$checksum = hash_final($hash);

// output checksum
echo 'checksum: ' . $checksum . "\n";
