<?php
/**
 * encryption example implementation
 *   the decryption works analog to this
 *
 * This file is part of the Contao rfccc-1 <https://github.com/Discordier/Contao-ER3>
 * This file is licensed under the Creative Commons Attribution-ShareAlike 3.0 Unported License <http://creativecommons.org/licenses/by-sa/3.0/legalcode>
 */

// get path
$path = dirname(dirname(__FILE__)) . '/example/hello_world';

// checksum from license
$checksum_license = '2eba386ca69992ac9632e7234c1d91606a06116650f7fdd01f5b99c45bc97515';

// checksum from package
$checksum_package = 'f60217d8baeed81a37773c0f475d5f523562b298256b68f65729054982b4660a';

// recalculate bytestream from hex checksum
$bytestream = '';
for ($i=0; $i<strlen($checksum_license); $i+=2)
{
	$int_1 = hexdec(substr($checksum_license, $i, 2));
	$int_2 = hexdec(substr($checksum_package, $i, 2));
	$bytestream .= chr($int_1 ^ $int_2);
}

// Open the cipher
$td = mcrypt_module_open('rijndael-256', '', 'ofb', '');

// Create the IV, use MCRYPT_RAND on Windows instead
$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_RANDOM);

// Intialize encryption
mcrypt_generic_init($td, $bytestream, $iv);

// temp directory for output
$tmp = dirname(dirname(__FILE__)) . '/example/hello_world_encrypt';
mkdir($tmp);

// walk over the path
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::LEAVES_ONLY);
foreach ($iterator as $file)
{
	if ($file->isFile())
	{
		// calculate relative path
		$rel = substr($file->getPathname(), strlen($path)+1);
		// only encrypt files from CONTAO or DATA
		if (!preg_match('#^(CONTAO|DATA)/#', $rel)) {
			// copy file unencrypted
			copy($file->getPathname(), $tmp . '/' . $rel);
			// output action
			echo '---' . "\n";
			echo 'copy file unencrypted ' . $rel . "\n";
			continue;
		}
		// the output path
		$output = $tmp . '/' . $rel . '.aes';
		// create parent directories
		if (!is_dir(dirname($output)))
			mkdir(dirname($output), 0777, true);
		// read the original file
		$data = file_get_contents($file->getPathname());
		// encrypt file content
		$data = mcrypt_generic($td, $data);
		// write encrypted file
		file_put_contents($output, $data);
		// output action
		echo '---' . "\n";
		echo 'encrypt source file ' . $file->getPathname() . "\n";
		echo '      with checksum ' . hash_file('sha256', $file->getPathname()) . "\n";
		echo '            to file ' . $output . "\n";
		echo '      with checksum ' . hash_file('sha256', $output) . "\n";
	}
}

// Terminate encryption handler
mcrypt_generic_deinit($td);
