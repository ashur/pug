<?php

/*
 * This file is part of Pug
 */
namespace Pug\DependencyManager;

use Pug\Pug;

class Composer implements \Pug\DependencyManager
{
	/**
	 * @var string
	 */
	protected $hashComposerFileBefore="";
	/**
	 * @var SplFileInfo
	 */
	protected $projectPath;

	/**
	 * @var boolean
	 */
	protected $usesComposer;

	/**
	 * Determine whether use of Composer is detected in the given directory
	 *
	 * @param	SplFileInfo
	 */
	public function __construct( \SplFileInfo $projectPath )
	{
		$this->projectPath = $projectPath;

		$composerFile = new \SplFileInfo( $this->projectPath->getRealPath() . '/composer.json' );

		if( $composerFile->isFile() )
		{
			$this->usesComposer = true;
			$this->hashComposerFilefileBefore = sha1_file( $composerFile );
		}
	}

	/**
	 *
	 * @return	boolean
	 */
	public function update()
	{
		if( $this->usesComposer )
		{
			echo ' • Updating Composer... ';

			// a lockfile exists
			$lockFile = new \SplFileInfo( $this->projectPath->getRealPath() . '/composer.lock' );
			$updateComposer = $lockFile->isFile() == false;

			// composer.json was updated
			$composerFile = new \SplFileInfo( $this->projectPath->getRealPath() . '/composer.json' );
			$updateComposer = $updateComposer || $this->hashComposerFileBefore != sha1_file( $composerFile );

			if( $updateComposer )
			{
				Pug::executeCommand( 'composer update' );
			}
			else
			{
				echo 'done.' . PHP_EOL;
			}

			return true;
		}

		return false;
	}
}

?>
