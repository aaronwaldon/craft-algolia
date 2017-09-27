<?php

/**
 * Algolia plugin for Craft CMS.
 *
 * Algolia search integration.
 *
 * @author    Joshua Baker
 * @copyright Copyright (c) 2016 Joshua Baker
 *
 * @link      https://joshuabaker.com/
 * @since     0.1.0
 */

namespace Craft;

class AlgoliaPlugin extends BasePlugin
{
    /**
     * Plugin init method.
     */
    public function init()
    {
        require_once __DIR__.'/vendor/autoload.php';

        $init = craft()->config->get('init', 'algolia');
        if (!empty($init) && is_callable($init))
        {
            call_user_func($init);
        }
        else
        {
            craft()->on('elements.onSaveElement', function (Event $event) {
                craft()->algolia->indexElement($event->params['element']);
            });
        }
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return Craft::t('Algolia');
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return Craft::t('Algolia search integration.');
    }

    /**
     * @return string
     */
    public function getDocumentationUrl()
    {
        return 'https://github.com/joshuabaker/craft-algolia/blob/master/README.md';
    }

    /**
     * @return string
     */
    public function getReleaseFeedUrl()
    {
        return 'https://raw.githubusercontent.com/joshuabaker/craft-algolia/master/releases.json';
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return '0.2.0';
    }

    /**
     * @return string
     */
    public function getSchemaVersion()
    {
        return '0.2.0';
    }

    /**
     * @return string
     */
    public function getDeveloper()
    {
        return 'Joshua Baker';
    }

    /**
     * @return string
     */
    public function getDeveloperUrl()
    {
        return 'https://joshuabaker.com/';
    }

    /**
     * @return bool
     */
    public function hasCpSection()
    {
        return false;
    }
}
