<?php

/*
 * This file is part of Pug
 */

$commands = [];

// Project
$project = new Huxtable\Application\Command('project', 'List, create or delete projects', function()
{
	$pug = Pug\Pug::open();

	foreach($pug->getProjects() as $project)
	{
		$pattern = "   %-11s%s\n";
		printf($pattern, $project->getName(), $project->getPath());
	}
});

$projectAdd = new Huxtable\Application\Command('add', 'Add a project named <name> which lives at <path>', function($name, $path)
{
	$pug = Pug\Pug::open();

	$pug->addProject(new Pug\Project($name, $path));

	foreach($pug->getProjects() as $project)
	{
		$pattern = "   %-11s%s\n";
		printf($pattern, $project->getName(), $project->getPath());
	}
});

$projectRemove = new Huxtable\Application\Command('remove', 'Remove the project named <name>.', function($name)
{
	$pug = Pug\Pug::open();
	$pug->removeProject($name);
});

$projectSetPath = new Huxtable\Application\Command('set-path', 'Changes the path for the named project.', function($name, $path)
{
	$pug = Pug\Pug::open();
	$pug->setPathForProject(new Pug\Project($name, $path));
});

$project->addSubcommand($projectAdd);
$project->addSubcommand($projectRemove);
$project->addSubcommand($projectSetPath);

$commands[] = $project;

// Update
$commands[] = new Huxtable\Application\Command('update', 'Fetch project updates', function($app=null)
{
	$pug = Pug\Pug::open();

	if(is_null($app))
	{
		foreach($pug->getProjects() as $project)
		{
			$project->update();
		}
	}
	else
	{
		$project = $pug->getProject($app)->update();
	}
});

?>
