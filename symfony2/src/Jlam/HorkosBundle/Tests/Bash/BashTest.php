<?php

namespace Spotlight\RestBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BashTest extends WebTestCase
{
	

	/**
	 * Dummy test to see if file properly boots.
	 * And to avoid phpunit giving us warning
	 * for file not having a test
	 */
	public function testDummy() {
		$this->assertTrue(TRUE);
	}
	
	
	public function testRunBashScripts() {
		$pwd = exec('pwd');
		
		$pathParts = explode('/', $pwd);
		$lastPart = end($pathParts);
		
		if($lastPart != 'app') {
			$this->markTestSkipped("You need to be in symfony/app for this test to work");
		}
		
		#$scriptPath = $pwd . '/../src/Spotlight/RestBundle/bin/';
		#$this->get('kernel')->getRootDir();
		$client = static::createClient();
		$container = $client->getContainer();
		$rootdir = $container->get('kernel')->getRootDir();
		
		$this->assertNotEmpty($rootdir, "rootdir is empty");
		$scriptPath =  $rootdir . '/../src/Jlam/HorkosBundle/Tests/Bash/';

		$this->assertTrue(is_dir($scriptPath), "Not a directory or permissions not set right: $scriptPath");
		
		
		$scripts = array();  $foundFiles = array();
		$incompleteTests = array();
		if ($handle = opendir($scriptPath)) {
			$files=array();
			while (false !== ($file = readdir($handle))) {
				if(preg_match('/^test.*\.bash/', $file)) {
					$scripts[] = $file; # Timestamps may not be unique, file names are.
				}
				elseif(preg_match('/^incomplete.*\.bash/', $file)) {
					$incompleteTests[] = $file;
				}
				
				$foundFiles[] = $file;
			}
			closedir($handle);
		}
		
		$this->assertGreaterThanOrEqual(
				1, count($scripts), 
				"Did not find at least 1 script.  The files found were " . var_export($foundFiles, TRUE)
		);
		
		foreach($scripts as $script) {
			$fullPath = $scriptPath .'/'. $script;
			
			$this->assertFileExists($fullPath, "Can't find the script $fullPath");

			$stdOutPath = '/tmp/bashSymfonyTestMessage-' . date('YmdHis') .'-'. rand(0, 1000000) . '.txt';	
			
			$returnCode = 1;
			passthru($fullPath . " 2>/dev/null > $stdOutPath", $returnCode);

			$message=file_get_contents($stdOutPath);
			
			$this->assertEquals(0, $returnCode,
					"Script $fullPath did not return 0.\n".
					"Did you run symfony/bin/resetPerms.bash to reset permissions?\n".
					"Did you build the boostrapt cache file by running Symfony/vendor/sensio/distribution-bundle/Sensio/Bundle/DistributionBundle/Resources/bin/build_bootstrap.php\n".
					"The message from the bash script was \n $message \n\n"
			);
		}
		
		if(count($incompleteTests) > 0) {
			$this->markTestIncomplete(sprintf(
					"Tests %s are marked as incomplete", 
					join(',', $incompleteTests))
			);
		}
	}

}
