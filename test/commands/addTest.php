<?php

/*
 * This file is part of Pug
 */

include_once( 'PugTestCase.php' );

use PHPUnit\Framework\TestCase;

class addTest extends PugTestCase
{
	public function projectNameProvider()
	{
		return [
			['pug/3'],
			['pug3']
		];
	}

	public function testAddEmptyQueryDisplaysUsage()
	{
		$result = $this->executePugCommand( 'add' );

		$this->assertEquals( 1, $result['exit'] );

		$expectedOutput = <<<OUTPUT
usage: pug add [<group>/]<name> <path> [--url=<url>]

OPTIONS
    --url=<url>
        Clone the Git repository at <url> to <path>.

OUTPUT;

		$this->assertEquals( $expectedOutput, $result['output'] );
	}

	public function testAddNonExistentPathReturnsError()
	{
		$path = self::$fixturesPath . '/' . microtime( true );
		$result = $this->executePugCommand( 'add', ['pug/1', $path] );

		$this->assertEquals( 1, $result['exit'] );
		$this->assertEquals( "pug: Couldn't track project. Path '{$path}' not found.", $result['output'] );
	}

	public function testAddExistingFileReturnsError()
	{
		$this->usePugfile( '.pug-new' );

		$path = self::$pugfilePath;
		$result = $this->executePugCommand( 'add', ['pug/1', $path] );

		$this->assertEquals( 1, $result['exit'] );
		$this->assertEquals( "pug: Couldn't track project. Invalid directory '{$path}'", $result['output'] );
	}

	public function testAddExistingNameReturnsError()
	{
		$this->usePugfile( '.pug-enabled' );

		$projectName = 'pug/1';
		$result = $this->executePugCommand( 'add', [$projectName, self::$projectPath] );

		$this->assertEquals( 1, $result['exit'] );
		$this->assertEquals( "pug: Couldn't track project. Project '{$projectName}' already exists. See 'pug show'.", $result['output'] );
	}

	public function testAddDirectoryWithoutSCMReturnsError()
	{
		$projectName = 'pug/3';
		$projectPath = dirname( self::$projectPath );	// Parent folder of repo folder should not be under SCM

		$result = $this->executePugCommand( 'add', [$projectName, $projectPath] );

		$this->assertEquals( 1, $result['exit'] );
		$this->assertEquals( "pug: Couldn't track project. Source control not found in '{$projectPath}'.", $result['output'] );
	}

	/**
	 * @dataProvider	projectNameProvider
	 */
	public function testAddProjectReturnsListing( $projectName )
	{
		$this->usePugfile( '.pug-disabled' );

		$result = $this->executePugCommand( 'add', [$projectName, self::$fixturesPath] );

		$this->assertEquals( 0, $result['exit'] );

		$expectedOutput = <<<OUTPUT
  pug/1
  pug/2
* {$projectName}
OUTPUT;

		$this->assertEquals( $expectedOutput, $result['output'] );
	}

	public function testAddWhoseNameCollidesWithExistingGroupReturnsError()
	{
		$this->markTestIncomplete();

		$this->usePugfile( '.pug-enabled' );

		$projectName = 'pug';
		$projectPath = self::$projectPath;

		$result = $this->executePugCommand( 'add', [$projectName, $projectPath] );

		$this->assertEquals( 1, $result['exit'] );
		$this->assertEquals( "pug: Couldn't track project. Group '{$projectName}' already exists. See 'pug show'.", $result['output'] );
	}
}
