<?php

/*
 * This file is part of Pug
 */
namespace Pug;

use \Huxtable\Output;
use \Huxtable\Command\CommandInvokedException;

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
				throw new \Huxtable\Command\CommandInvokedException("Can't read from ~/.pug", 1);
			}
			if(!is_writable(PUG_CONFIG))
			{
				throw new \Huxtable\Command\CommandInvokedException("Can't write to ~/.pug", 1);
			}

			$json = json_decode(file_get_contents(PUG_CONFIG), true);

			if(isset($json['projects']))
			{
				foreach($json['projects'] as $project)
				{
					$enabled = isset( $project['enabled'] ) ? $project['enabled'] : true;
					$updated = isset( $project['updated'] ) ? $project['updated'] : null;
					$this->projects[] = new Project($project['name'], $project['path'], $enabled, $updated);
				}
			}
		}

		$this->sortProjects();
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
				throw new \Huxtable\Command\CommandInvokedException("The project '{$project->getName()}' already exists. See 'pug list'", 1);
			}
		}

		$this->projects[] = $project;
		$this->write();
	}

	/**
	 * @param	string	$name
	 * @return	void
	 */
	public function disableProject( $name )
	{
		$count = count($this->projects);
		$disabled = 0;

		for( $i=0; $i<$count; $i++ )
		{
			if( $this->projects[$i]->getName() == $name )
			{
				$this->projects[$i]->disable();
				$disabled++;
			}
		}

		if( $disabled == 0 )
		{
			throw new \Huxtable\Command\CommandInvokedException("Project '{$name}' not found", 1);
		}

		$this->write();
	}
	
	/**
	 * @param	string	$name
	 * @return	void
	 */
	public function enableProject( $name )
	{
		$count = count($this->projects);
		$enabled = 0;

		for( $i=0; $i<$count; $i++ )
		{
			if( $this->projects[$i]->getName() == $name )
			{
				$this->projects[$i]->enable();
				$enabled++;
			}
		}

		if($enabled == 0)
		{
			throw new \Huxtable\Command\CommandInvokedException("Project '{$name}' not found", 1);
		}

		$this->write();
	}

	/**
	 * @param	string	$name
	 * @return	Project, boolean
	 */
	public function getProject($name)
	{
		foreach($this->projects as &$project)
		{
			if($project->getName() == $name)
			{
				return $project;
			}
		}

		return false;
	}

	/**
	 * @param	boolean	$sortByUpdated
	 * @return	array
	 */
	public function getProjects ($sortByUpdated = false)
	{
		if( $sortByUpdated )
		{
			$this->sortProjects( $sortByUpdated );
		}

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

		if( $removed == 0 )
		{
			throw new \Huxtable\Command\CommandInvokedException("Project '{$name}' not found", 1);
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
			throw new \Huxtable\Command\CommandInvokedException("Project '{$project->getName()}' not found", 1);
		}

		$this->write();
	}

	/**
	 * @param	boolean	$sortByUpdated
	 */
	protected function sortProjects($sortByUpdated = false)
	{
		$name = [];
		$updated = [];

		// Sort projects by name
		foreach($this->projects as $project)
		{
			$name[] = $project->getName();
			$updated[] = $project->getUpdated();
		}

		if ($sortByUpdated == true)
		{
			array_multisort($updated, SORT_DESC, $name, SORT_ASC, $this->projects);
			return;
		}

		array_multisort($name, SORT_ASC, $this->projects);
	}

	/**
	 * @param	string	$target		Target to update
	 */
	public function update( $target )
	{
		$output = new Output;

		// Update all tracked projects
		if( $target == 'all' )
		{
			array_walk($this->projects, function(&$project, $key)
			{
				if( $project->isEnabled() )
				{
					try
					{
						$project->update();
					}
					// A pug-level error we need to show the user (but which shouldn't interrupt other projects from updating — thanks, Sheree!)
					catch( \Exception $e )
					{
						// We're going to make this pretty, since it's one in a list of multiple.
						echo "Updating '{$project->getName()}'... halted: " . PHP_EOL . PHP_EOL;
						echo Output::colorize( ' ! ', 'red' ) . 'pug: ' . $e->getMessage() .PHP_EOL . PHP_EOL;
					}
				}
			});
		}
		else
		{
			// Update single project
			$project = $this->getProject( $target );

			$projectName = '';
			$projectPath = '';

			if( $project != false )
			{
				$projectName = $project->getName();
				$projectPath = $project->getPath();
			}
			else
			{
				$file = new \SplFileInfo( $target );
				
				if( $file->isDir() )
				{
					$projectPath = $file->getRealpath();

					// Let's check to see if a tracked project is already registered at this path
					foreach( $this->projects as $project )
					{
						if( $project->getPath() == $projectPath )
						{
							$projectName = $project->getName();
							break;
						}
					}

					if( $projectName == '' )
					{
						// No registered projects found, down to the bare file path itself
						$project = new Project( $file->getRealpath(), $file->getRealPath() );
						$projectName = $projectPath;
					}
				}
				else
				{
					throw new CommandInvokedException( "Unknown project or directory '{$target}'", 1 );
				}
			}

			try
			{
				$project->update();
			}
			// There's something funny about this project's directory, so we need to pull over
			catch( InvalidDirectoryException $e )
			{
				throw new CommandInvokedException( $e->getMessage(), 1 );
			}
			// Could not find any traces of SCM, which is... problematic
			catch( MissingSourceControlException $e )
			{
				throw new CommandInvokedException( $e->getMessage(), 1 );
			}	
		}

		$this->write();
	}

	/**
	 */
	protected function write()
	{
		$this->sortProjects();
		$projects = $this->projects;

		$json = json_encode(compact('projects'), JSON_PRETTY_PRINT);

		file_put_contents(PUG_CONFIG, $json);
	}
}

?>
