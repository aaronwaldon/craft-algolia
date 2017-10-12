<?php

/**
 * Algolia plugin for Craft CMS.
 *
 * algolia command
 *
 * @author    Aaron Waldon
 * @link      https://www.causingeffect.com/
 */

namespace Craft;

class AlgoliaCommand extends BaseCommand
{

	/**
	 * Command: php yiic algolia clearindex --name=some_index_name_here
	 */
	public function actionClearIndex($name)
	{
		$index = craft()->algolia->getAlgoliaClient()->initIndex($name);
		$index->clearIndex();
	}
}