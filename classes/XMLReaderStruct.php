<?php
if (!defined('Access')) {
	die('Silence is gold');
}

/** Example to use *************************************

<?xml version="1.0" encoding="UTF-8"?>
<document>
	<record>
		<id>0</id>
		<type>product</type>
		<name>Some product name. ID:0</name>
		<qty>0</qty>
		<price>0</price>
	</record>
	<record>
		<id>1</id>
		<type>service</type>
		<name>Some service name. ID:1</name>
		<qty>1</qty>
		<price>15</price>
	</record>
</document>

include_once 'classes/XMLReaderStruct.php';

$x = new XMLReaderStruct();
$productsSum = 0;
$servicesSum = 0;
$structure = array(
	'document' => array(
		'record' => array(
			'type'    => array( "__text" => function($text) use (&$currentRecord) {
				$currentRecord['isService'] = $text === 'service';
			} ),
			'qty'     => array( "__text" => function($text) use (&$currentRecord) {
				$currentRecord['qty'] = (int)$text;
			} ),
			'price'   => array( "__text" => function($text) use (&$currentRecord) {
				$currentRecord['price'] = (int)$text;
			} ),

			'__open'  => function() use (&$currentRecord) {
				$currentRecord = array();
			},
			'__close' => function() use (&$currentRecord, &$productsSum, &$servicesSum) {
				$money = $currentRecord['qty'] * $currentRecord['price'];
				if ($currentRecord['isService']) $servicesSum += $money;
				else $productsSum += $money;
			}
		)
	)
);

$x->xmlStruct(file_get_contents('example.xml'), $structure);
echo 'Overall products price: ', $productsSum, ', Overall services price: ', $servicesSum;

 *******************************************************/


/**
 * Class XMLReaderStruct
 */
class XMLReaderStruct extends XMLReader {

	/** @var string debug trace of parsing message */
	private $debugTrace;
	/** @var bool Default false if true not going in depth if getting unknown tag */
	private $skipToDepth;

	/**
	 * XMLReaderStruct constructor.
	 */
	public function __construct() {
		$this->debugTrace = '';
	}

	/**
	 * @param string $xml document like for XMLReader
	 * @param array $structure Associative array that fully describes how we should work with our file.
	 *          It is understood that its appearance is known in advance,
	 *          and we know exactly with what tags and what we should do.
	 * @param null $encoding like for XMLReader
	 * @param int $options like for XMLReader
	 * @param bool $debug Default No. If true write error log with full trace of parsing.
	 *          Available in method getDebugTrace()
	 *
	 * @param bool $skipToDepth Default false If true not going in depth if getting unknown tag
	 *
	 * @return bool false For parsing failed and true if not
	 */
	public function xmlStruct($xml, $structure, $encoding = null, $options = 0, $debug = false, $skipToDepth = false) {

		$this->xml($xml, $encoding, $options);
		$stack = array();
		$node = &$structure;
		$this->skipToDepth = $skipToDepth;

		while ($this->read()) {

			switch ($this->nodeType) {

				case self::ELEMENT:

					if ($this->skipToDepth === false) {

						if (isset($node[$this->name])) {

							if ($debug) $this->debugTrace .= "[ Opening ]: {$this->name} - found in structure. Descent by structure.\r\n";

							$stack[$this->depth] = &$node;
							$node = &$node[$this->name];

							if (isset($node["__open"])) {

								if ($debug) $this->debugTrace .= "              Found handler to opening {$this->name} - doing.\r\n";

								if (false === $node["__open"]()) return false;
							}

							if (isset($node["__attrs"])) {

								if ($debug) $this->debugTrace .= "              Attribute handler found {$this->name} - doing.\r\n";
								$attrs = array();

								if ($this->hasAttributes)
									while ($this->moveToNextAttribute())
										$attrs[$this->name] = $this->value;

								if (false === $node["__attrs"]($attrs)) return false;
							}

							if ($this->isEmptyElement) {

								if ($debug) $this->debugTrace .= "              Element {$this->name} empty. Return by structure.\r\n";

								if (isset($node["__close"])) {

									if ($debug) $this->debugTrace .= "             Found a close handler {$this->name}- doing.\r\n";

									if (false === $node["__close"]()) return false;
								}
								$node = &$stack[$this->depth];
							}

						} else {
							$this->skipToDepth = $this->depth;

							if ($debug) $this->debugTrace .= "[ Opening ]: {$this->name} - not found in structure. Starting the tag skip mode to achieve nesting {$this->skipToDepth}.\r\n";
						}

					} else {

						if ($debug) $this->debugTrace .= "( Opening ): {$this->name} - in tag skip mode.\r\n";
					}
					break;

				case self::TEXT:

					if ($this->skipToDepth === false) {

						if ($debug) $this->debugTrace .= "[ Text    ]: {$this->value} - in structure.\r\n";

						if (isset($node["__text"])) {

							if ($debug) $this->debugTrace .= "              Found a text handler - execute.\r\n";

							if (false === $node["__text"]($this->value)) return false;
						}

					} else {

						if ($debug) $this->debugTrace .= "( Text    ): {$this->value} - in tag skip mode.\r\n";
					}
					break;

				case self::END_ELEMENT:

					if ($this->skipToDepth === false) {

						if ($debug) $this->debugTrace .= "[ Closing ]: {$this->name} - we are in structure. Going up in structure.\r\n";

						if (isset($node["__close"])) {

							if ($debug) $this->debugTrace .= "              Found a close handler {$this->name} - doing.\r\n";

							if (false === $node["__close"]()) return false;
						}
						$node = &$stack[$this->depth];

					} elseif ($this->depth === $this->skipToDepth) {

						if ($debug) $this->debugTrace .= "[ Closing ]: {$this->name} - nesting achieved {$this->skipToDepth}. Cancel tag skip mode.\r\n";

						$this->skipToDepth = false;

					} else {

						if ($debug) $this->debugTrace .= "( Closing ): {$this->name} - in tag skip mode.\r\n";
					}
					break;
			}
		}
		return true;
	}

	/**
	 * Print debug msg function
	 * @return string error
	 */
	public function getDebugTrace() {
		return $this->debugTrace;
	}

}