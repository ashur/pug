<?php

/*
 * This file is part of Pug
 */

use Huxtable\Core\File;

return array(
	/*
	 * 0.5.0:
	 *   - Removed user config
	 *   - Switched to modern Huxtable
	 */
	'0.5.0' => function( $dirPug )
	{
		$dirHuxtable = $dirPug->childDir( 'vendor' )->childDir( 'Huxtable' );

		return array(
			/* ./ */
			$dirPug->child( 'config.php' ),

			/* ./vendor/huxtable */
			$dirHuxtable->child( '.gitignore' ),
			$dirHuxtable->child( 'README.md' ),
			$dirHuxtable->child( 'composer.json' ),
			$dirHuxtable->childDir( 'src' ),
		);
	},
);
