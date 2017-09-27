<?php

/**
 * Algolia plugin for Craft CMS.
 *
 * Algolia IndexModel
 *
 * @author    Joshua Baker
 * @copyright Copyright (c) 2016 Joshua Baker
 *
 * @link      https://joshuabaker.com/
 * @since     0.1.0
 */

namespace Craft;

class Algolia_IndexModel extends BaseModel
{
    /**
     * @var \AlgoliaSearch\Index
     */
    protected $algoliaIndex;

    /**
     * Returns an Algolia Index instance based on the prefixed name.
     *
     * @return \AlgoliaSearch\Index
     */
    public function getAlgoliaIndex()
    {
        if (is_null($this->algoliaIndex)) {
            $this->algoliaIndex = craft()->algolia->getAlgoliaClient()->initIndex($this->getPrefixedIndexName());

            if (!empty($this->settings)) {
                $this->algoliaIndex->setSettings($this->settings, true);
            }
        }

        return $this->algoliaIndex;
    }

    /**
     * Detemines if the supplied element can be indexed in this index.
     *
     * @param $element BaseElementModel
     *
     * @return bool
     */
    public function canIndexElement(BaseElementModel $element)
    {
        return strtolower($this->elementType) == strtolower($element->elementType) &&
            call_user_func($this->filter, $element);
    }

    /**
     * Transforms the supplied element using the transformer method in config.
     *
     * @param $element BaseElementModel
     *
     * @return mixed
     */
    public function transformElement(BaseElementModel $element)
    {
        $transformed = call_user_func($this->transformer, $element);
        $transformed['objectID'] = $element->id;

        return $transformed;
    }

    /**
     * Adds or removes the supplied element from the index.
     *
     * @param $element BaseElementModel
     *
     * @return mixed
     */
    public function indexElement(BaseElementModel $element)
    {
        if ($this->canIndexElement($element)) {
            if ($element->enabled) {
                return $this->getAlgoliaIndex()->addObject(
                    $this->transformElement($element)
                );
            } else {
                return $this->getAlgoliaIndex()->deleteObject($element->id);
            }
        }
    }

    /**
     * Removes the supplied element from the index.
     *
     * @param $element BaseElementModel
     *
     * @return mixed
     */
    public function deindexElement(BaseElementModel $element)
    {
        if ($this->canIndexElement($element)) {
            return $this->getAlgoliaIndex()->deleteObject($element->id);
        }
    }

    /**
     * Returns the index name with the configured prefix.
     *
     * @return string
     */
    public function getPrefixedIndexName()
    {
        return craft()->config->get('indexNamePrefix', 'algolia').$this->indexName;
    }

    /**
     * @return array
     */
    protected function defineAttributes()
    {
        return [
            'indexName' => AttributeType::String,
            'elementType' => [
                AttributeType::String,
                'default' => ElementType::Entry,
            ],
            'filter' => [
                AttributeType::Mixed,
                'default' => function (BaseElementModel $element) {
                    return true;
                },
            ],
            'transformer' => [
                AttributeType::Mixed,
                'default' => function (BaseElementModel $element) {
                    return [
                        'title' => $element->getTitle(),
                    ];
                },
            ],
            'settings' => AttributeType::Mixed,
        ];
    }
}
