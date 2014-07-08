<?php
	
require_once dirname(dirname(__FILE__)).'/vendor/autoload.php';

function run(Huxtable\Application $app)
{
	require_once 'commands.php';

	// Register commands defined in command.php
	foreach($commands as $command)
	{
		$app->registerCommand($command);
	}
	
	// Attempt to run the requested command
	$app->run();
	
	// Stop application and exit
	$app->stop();
}

?>
