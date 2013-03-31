#!/usr/bin/php
<?php

if ($argc !== 2) {
	printf('usage: %s <classdir>%s', $argv[0], PHP_EOL);
	exit(5);
}

if (!is_readable($argv[1])) {
	printf('fatal: cannot read file `%s`%s', $argv[1], PHP_EOL);
	exit(6);
}

define('MERCILESS_CLASSDIR', __DIR__ . '/classes');
require('shared/includes/autoload.php');
require('functions.php');

$intErrors = 0;
$strCurrentFile = '';
$blnFileAnnounced = false;
$blnDebug = false;

$strClassBase = $argv[1];
foreach (new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($strClassBase,
				RecursiveDirectoryIterator::FOLLOW_SYMLINKS)) as $file)
	if ($file->isFile() && $file->getExtension() === 'php')
		check_class_file($file, $strClassBase);

_n();

printf('Total: %s errors%s', $intErrors, PHP_EOL);

if ($intErrors)
	exit(1);
