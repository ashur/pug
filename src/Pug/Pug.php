<?php

/*
 * This file is part of Pug
 */
namespace Pug;

define('PUG_CONFIG', getenv('HOME').DIRECTORY_SEPARATOR.'.pug');

class Pug
{
	/**
	 * @var array
	 */
	protected $projects=[];

	/**
	 * @return	void
	 */
	public function __construct()
	{
		$projects = [];
		$fileInfo = new \SplFileInfo(PUG_CONFIG);

		if(!file_exists(PUG_CONFIG))
		{
			touch($fileInfo->getPathname());
		}
		else
		{
			if(!is_readable(PUG_CONFIG))
			{
				throw new \Huxtable\Application\Command\CommandInvokedException("Can't read from ~/.pug", 1);
			}
			if(!is_writable(PUG_CONFIG))
			{
				throw new \Huxtable\Application\Command\CommandInvokedException("Can't write to ~/.pug", 1);
			}

			$json = json_decode(file_get_contents(PUG_CONFIG), true);

			if(isset($json['projects']))
			{
				foreach($json['projects'] as $project)
				{
					$this->projects[] = new Project($project['name'], $project['path']);
				}
			}
		}
	}

	/**
	 * @param	Project	$project
	 */
	public function addProject(Project $project)
	{
		foreach($this->projects as $current)
		{
			if($project->getName() == $current->getName())
			{
				throw new \Huxtable\Application\Command\CommandInvokedException("The project '{$project->getName()}' already exists. Choose another name or see 'pug help project'", 1);
			}
		}

		$this->projects[] = $project;
		$this->write();
	}

	/**
	 * @param	string	$name
	 * @return
	 */
	public function getProject($name)
	{
		foreach($this->projects as $project)
		{
			if($project->getName() == $name)
			{
				return $project;
			}
		}

		throw new \Huxtable\Application\Command\CommandInvokedException("Project '{$name}' not found", 1);
	}
	/**
	 * @return	array					Return contents of $this->projects
	 */
	public function getProjects()
	{
		return $this->projects;
	}

	/**
	 * @param	string	$name
	 */
	public function removeProject($name)
	{
		$count   = count($this->projects);
		$removed = 0;

		for($i=0; $i < $count; $i++)
		{
			if($this->projects[$i]->getName() == $name)
			{
				unset($this->projects[$i]);
				$removed++;
			}
		}

		if($removed == 0)
		{
			throw new \Huxtable\Application\Command\CommandInvokedException("Project '{$name}' not found", 1);
		}
		
		$this->write();
	}

	/**
	 * @param	Project	$project
	 */
	public function setPathForProject(Project $project)
	{
		$updated = 0;
		for($i=0; $i < count($this->projects); $i++)
		{
			if($this->projects[$i]->getName() == $project->getName())
			{
				$this->projects[$i] = $project;
				$updated++;
			}
		}

		if($updated == 0)
		{
			throw new \Huxtable\Application\Command\CommandInvokedException("Project '{$project->getName()}' not found", 1);
		}

		$this->write();
	}

	/**
	 */
	protected function write()
	{
		$projects = $this->projects;

		$json = json_encode(compact('projects'), JSON_PRETTY_PRINT);

		file_put_contents(PUG_CONFIG, $json);
	}
}

?>
