<?php

/**
 * Algolia plugin for Craft CMS.
 *
 * Algolia Service
 *
 * @author    Joshua Baker
 * @copyright Copyright (c) 2016 Joshua Baker
 *
 * @link      https://joshuabaker.com/
 * @since     0.1.0
 */

namespace Craft;

use AlgoliaSearch\Client as AlgoliaClient;

class AlgoliaService extends BaseApplicationComponent
{
    /**
     * An Algolia client instance.
     *
     * @var \AlgoliaSearch\Client
     */
    protected $algoliaClient;

    /**
     * An array of Algolia_IndexModel instances.
     *
     * @var array
     */
    protected $mappings;

    /**
     * Returns an Algolia client instance.
     *
     * @return \AlgoliaSearch\Client
     */
    public function getAlgoliaClient()
    {
        if (is_null($this->algoliaClient)) {
            $this->algoliaClient = new AlgoliaClient(
                craft()->config->get('applicationId', 'algolia'),
                craft()->config->get('adminApiKey', 'algolia')
            );
        }

        return $this->algoliaClient;
    }

    /**
     * Returns the supplied index name prefixed.
     *
     * @param $indexName string
     *
     * @return string
     */
    public function getPrefixedIndexName($indexName)
    {
        return craft()->config->get('indexNamePrefix', 'algolia').$indexName;
    }

    /**
     * Returns an array of Algolia_IndexModel instances with the unprefixed index names as keys.
     *
     * @return array
     */
    public function getMappings()
    {
        if (is_null($this->mappings)) {
            $this->mappings = [];
            $mappingsConfig = craft()->config->get('mappings', 'algolia');
            foreach ($mappingsConfig as $mappingConfig) {
                $this->mappings[] = new Algolia_IndexModel($mappingConfig);
            }
        }

        return $this->mappings;
    }

    /**
     * Passes the supplied element to each configured index.
     *
     * @param $element BaseElementModel
     */
    public function indexElement(BaseElementModel $element)
    {
        foreach ($this->getMappings() as $algoliaIndexModel) {
            $algoliaIndexModel->indexElement($element);
        }
    }

    /**
     * Passes the supplied element to each configured index.
     *
     * @param $element BaseElementModel
     */
    public function deindexElement(BaseElementModel $element)
    {
        foreach ($this->getMappings() as $algoliaIndexModel) {
            $algoliaIndexModel->deindexElement($element);
        }
    }
}
