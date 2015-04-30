<?php

/*
 * This file is part of Pug
 */
namespace Pug;

class Project implements \JsonSerializable
{
	/**
	 * @var boolean
	 */
	protected $enabled;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var \SplFileInfo
	 */
	protected $path;

	/**
	 * @var int
	 */
	protected $updated;

	/**
	 * @param	string	$name		Name of project
	 * @param	string	$path		Path to project directory
	 * @param	boolean	$enabled	Enabled status
	 * @param	string	$updated	UNIX timestamp of last update
	 */
	public function __construct($name, $path, $enabled=true, $updated=null)
	{
		$this->name = $name;
		$this->path = new \SplFileInfo($path);
		$this->enabled = $enabled;
		$this->updated = $updated;
	}

	/**
	 * @return	void
	 */
	public function disable()
	{
		$this->enabled = false;
	}

	/**
	 * @return	void
	 */
	public function enable()
	{
		$this->enabled = true;
	}

	/**
	 * @return	boolean
	 */
	public function isEnabled()
	{
		return $this->enabled;
	}

	/**
	 * @return	string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return	string
	 */
	public function getPath()
	{
		return $this->path->getPathname();
	}

	/**
	 * @return	string
	 */
	public function getUpdated()
	{
		return $this->updated;
	}

	/**
	 * @return	boolean
	 */
	public function update()
	{
		if(!$this->path->isDir())
		{
			throw new \Huxtable\Command\CommandInvokedException ("Project root '{$this->path}' is not a directory", 1);
		}
		if(!$this->path->isReadable())
		{
			throw new \Huxtable\Command\CommandInvokedException ("Project root '{$this->path}' isn't readable", 1);
		}

		echo "Updating '{$this->name}'...".PHP_EOL;

		$updateOutput = false;
		chdir($this->path->getPathname());

		// Git
		$gitFile = new \SplFileInfo($this->path->getRealPath() . '/.git');

		if($gitFile->isDir())
		{
			// Get latest changes
			$updateOutput = system('git pull');
			system('git submodule update --init --recursive');
		}

		// Subversion
		$svnFile = new \SplFileInfo($this->path->getRealPath() . '/.svn');

		if($svnFile->isDir())
		{
			// Get latest changes
			$updateOutput = system('svn up');
		}

		// Bail early if no changes came down
		if($updateOutput == 'Already up-to-date.')
		{
			return;
		}

		// CocoaPods
		$podFile = new \SplFileInfo($this->path->getRealPath() . '/Podfile');

		if($podFile->isFile())
		{
			system('pod install');
		}

		// Composer
		$composerFile = new \SplFileInfo($this->path->getRealPath() . '/composer.json');

		if($composerFile->isFile())
		{
			system('composer update');
		}

		if(!$updateOutput)
		{
			throw new \Huxtable\Command\CommandInvokedException("No supported version control found at '{$this->path}'", 1);
		}

		$this->updated = time();
	}

	/**
	 * @return	array
	 */
	public function jsonSerialize()
	{
		return [
			'name' => $this->getName(),
			'path' => $this->getPath(),
			'enabled' => $this->enabled,
			'updated' => $this->updated
		];
	}
}

?>
