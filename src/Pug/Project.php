<?php

/*
 * This file is part of Pug
 */
namespace Pug;

class Project implements \JsonSerializable
{
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
	 * @param	string	$updated	UNIX timestamp of last update
	 */
	public function __construct($name, $path, $updated=null)
	{
		$this->name = $name;
		$this->path = new \SplFileInfo($path);
		$this->updated = $updated;
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
		return !is_null($this->updated) ? date('D M j H:i', $this->updated) : "-";
	}

	/**
	 * @return	boolean
	 */
	public function update()
	{
		if(!$this->path->isDir())
		{
			echo "Project root '{$this->path}' is not a directory".PHP_EOL;
			return;
		}
		if(!$this->path->isReadable())
		{
			echo "Project root '{$this->path}' isn't readable".PHP_EOL;
			return;
		}

		$title = ucwords($this->name);

		// Figlet
		exec('which figlet', $output, $figlet);

		if($figlet == 0)
		{
			system('echo');
			system("figlet -f smslant '{$title}'");	
		}
		else
		{
			echo $title.PHP_EOL;
		}
	
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
			'updated' => $this->updated
		];
	}
}

?>
