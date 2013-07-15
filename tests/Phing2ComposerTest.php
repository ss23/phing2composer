<?php

class Phing2ComposerTest extends PHPUnit_Framework_TestCase {
	/**
	 * @dataProvider getTestFilePairs
	 */
	public function testConversion($inFile, $outFile) {
		$CLI_script = escapeshellcmd(dirname(__DIR__) . '/phing2composer');
		$CLI_arg = escapeshellarg($inFile);
		$output = `$CLI_script $CLI_arg`;

		echo $output;

		$this->assertEquals(file_get_contents($outFile), $output);
	}

	/**
	 * Get all the file pairs <testname>.in, <testname>.out from the tests directory.
	 *
	 * This is used as a dataProvider for the test
	 */
	public function getTestFilePairs() {
		$dir = __DIR__;

		$output = array();

		foreach(scandir($dir) as $file) {
			if($file[0] == '.') continue;
			if(substr($file,-3) == '.in') {
				$outFile = substr($file,0,-3) . '.out';
				if(file_exists("$dir/$outFile")) {
					$output[] = array("$dir/$file", "$dir/$outFile");
				}
			}
		}

		$this->assertNotEquals(array(), $output);

		return $output;
	}
}
