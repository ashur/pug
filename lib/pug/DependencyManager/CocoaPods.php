<?php

/*
 * This file is part of Pug
 */
namespace Pug\DependencyManager;

use Pug\Pug;

class CocoaPods implements IDependencyManager
{
	/**
	 * @var string
	 */
	protected $hashPodfileBefore="";
	/**
	 * @var SplFileInfo
	 */
	protected $projectPath;

	/**
	 * @var boolean
	 */
	protected $usesCocoaPods;

	/**
	 * Determine whether use of CocoaPods is detected in the given directory
	 *
	 * @param	SplFileInfo
	 */
	public function __construct( \SplFileInfo $projectPath )
	{
		$this->projectPath = $projectPath;

		$podfile = new \SplFileInfo( $this->projectPath->getRealPath() . '/Podfile' );

		if( $podfile->isFile() )
		{
			$this->usesCocoaPods = true;
			$this->hashPodfileBefore = sha1_file( $podfile );
		}
	}

	/**
	 * @param	boolean	$force	Force an update
	 * @return	boolean
	 */
	public function update( $force=false )
	{
		if( $this->usesCocoaPods )
		{
			echo PHP_EOL;
			echo ' • Updating CocoaPods... ';

			// a Pods folder exists
			$podsFolder = new \SplFileInfo( $this->projectPath->getRealPath() . '/Pods' );
			$updateCocoaPods = $podsFolder->isDir() == false;

			// a lockfile exists
			$lockFile = new \SplFileInfo( $this->projectPath->getRealPath() . '/Podfile.lock' );
			$updateCocoaPods = $updateCocoaPods || $lockFile->isFile() == false;

			// the Podfile was updated
			$podfile = new \SplFileInfo( $this->projectPath->getRealPath() . '/Podfile' );
			$updateCocoaPods = $updateCocoaPods || $this->hashPodfileBefore != sha1_file( $podfile );

			if( $updateCocoaPods || $force )
			{
				Pug::executeCommand( 'pod install' );
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
