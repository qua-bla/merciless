<?php

function msg() {
	global $strCurrentFile;
	global $blnFileAnnounced;

	if (!$blnFileAnnounced) {
		_n();
		printf('%s%s', $strCurrentFile, PHP_EOL);
		$blnFileAnnounced = true;
	}
}

function _n() {
	printf('---%s', PHP_EOL);
}

function debugFound(ParserToken $objToken) {
	global $blnDebug;
	if ($blnDebug) {
		msg();
		printf('Found %s on line %s: "%s"%s', $objToken->tokenName(),
				$objToken->intRow, $objToken->strOriginalString, PHP_EOL);
	}
}

function error($strMsg, ParserToken $objToken) {
	global $intErrors;
	$intErrors++;
	msg();
	printf('Error on line %s: "%s"%s', $objToken->intRow, $strMsg, PHP_EOL);
}

function errorGlobal($strMsg) {
	global $intErrors;
	$intErrors++;
	msg();
	printf('Global error in file: "%s"%s', $strMsg, PHP_EOL);
}

function check_class_file(SplFileInfo $file, $strClassBase) {
	$strClassPath = str_replace($strClassBase, '', $file->getPath());
	$arrNamepspace = explode('/', $strClassPath);
	$strClassNameFromFilename = $file->getBasename('.php');

	$content = file_get_contents((string) $file);
	$tokens = token_get_all($content);
	#print_r($tokens);
	global $strCurrentFile;
	global $blnFileAnnounced;
	$strCurrentFile = (string) $file;
	$blnFileAnnounced = false;

	if (array_key_exists(0, $tokens)) {
		$objFirstToken = new ParserToken($tokens[0]);
		if ($objFirstToken->intTokenIndex !== T_OPEN_TAG) {
			error('First token is not the opening tag', $objFirstToken);
		} else {
			if (trim($tokens[0][1]) != '<?php') {
				error('Opening tag is not `<?php`', $objFirstToken);
			} else {
				debugFound($objFirstToken);
			}
		}
	} else {
		errorGlobal('File seems to be empty');
	}

	$blnFoundNamespace = false;
	foreach ($tokens as $intTokenCount => $arrToken) {
		if (is_array($arrToken)) {
			$objToken = new ParserToken($arrToken);
			debugFound($objToken);

			switch ($objToken->intTokenIndex) {
				case T_CLOSE_TAG:
					error('Closing tags are neither required nor allowed',
							$objToken);
					break;

				case T_CLASS:
					debugFound($objToken);
					$objClassName =
							new ParserToken($tokens[$intTokenCount + 2]);
					debugFound($objClassName);
					if ($objClassName->strOriginalString
							!== $strClassNameFromFilename)
						error(
								'Class name `'
										. $objClassName->strOriginalString
										. '` does not equal filename',
								$objClassName);
					break;

				case T_INLINE_HTML:
					error('That`s worse: Found plain HTML', $objToken);
					break;

				case T_NAMESPACE:
					debugFound($objToken);
					$blnFoundNamespace = true;

					$i = $intTokenCount + 2;
					reset($arrNamepspace);
					do {
						$objNamespace = new ParserToken($tokens[$i]);
						if ($objNamespace->strOriginalString
								!== current($arrNamepspace))
							error(
									'Namespace mismatch with path in level `'
											. key($arrNamepspace) . '`',
									$objNamespace);
						$i++;
						next($arrNamepspace);
					} while (array_key_exists($i, $tokens)
							&& is_array($tokens[$i])
							&& $tokens[$i++][0] == T_NS_SEPARATOR);

					break;
			}
		}
	}

	if (count($arrNamepspace) !== 0 && !$blnFoundNamespace)
		errorGlobal(
				'Found no namespace declaration but path indicates a namespace');
}

