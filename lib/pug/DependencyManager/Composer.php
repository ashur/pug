<?php

/*
 * This file is part of Pug
 */
namespace Pug\DependencyManager;

use Pug\Pug;

class Composer implements IDependencyManager
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
			$this->hashComposerFileBefore = sha1_file( $composerFile );
		}
	}

	/**
	 * @param	boolean	$force	Force an update
	 * @return	boolean
	 */
	public function update( $force=false )
	{
		if( $this->usesComposer )
		{
			echo PHP_EOL;
			echo ' • Updating Composer... ';

			// a lockfile exists
			$lockFile = new \SplFileInfo( $this->projectPath->getRealPath() . '/composer.lock' );
			$updateComposer = $lockFile->isFile() == false;

			// composer.json was updated
			$composerFile = new \SplFileInfo( $this->projectPath->getRealPath() . '/composer.json' );
			$updateComposer = $updateComposer || $this->hashComposerFileBefore != sha1_file( $composerFile );

			if( $updateComposer || $force )
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
