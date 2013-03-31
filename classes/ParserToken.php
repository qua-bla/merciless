<?php

class ParserToken {
	/**
	 * @var integer
	 */
	public $intTokenIndex;
	/**
	 * @var string
	 */
	public $strOriginalString = '';
	/**
	 * @var integer
	 */
	public $intRow;

	public function __construct(array $arrToken) {
		if (!is_int($arrToken[0]))
			trigger_error('Array of token contained non integer at 0');
		if (!is_int($arrToken[2]))
			trigger_error('Array of token contained non integer at 2');

		// to get a warning if no token
		token_name($arrToken[0]);

		$this->intTokenIndex = $arrToken[0];
		$this->strOriginalString = $arrToken[1];
		$this->intRow = $arrToken[2];
	}

	public function tokenName() {
		return token_name($this->intTokenIndex);
	}
}
