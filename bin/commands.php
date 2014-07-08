<?php

/*
 * This file is part of Pug
 */

$commands = [];

// Define commands
$commands[] = new Huxtable\Application\Command('update', 'Fetch project updates', function($app=null)
{
	$projects[] = new Pug\Project('coda',   '~/Developer/coda/');
	$projects[] = new Pug\Project('prompt', '~/Developer/prompt/');
	$projects[] = new Pug\Project('audion', '~/Developer/audion/');

	foreach($projects as $project)
	{
		$project->update();
	}
});

?>
