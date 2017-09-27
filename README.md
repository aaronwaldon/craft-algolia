# Algolia plugin for Craft CMS

Algolia search-as-a-service integration for Craft CMS.

## Installation

To install Algolia, follow these steps:

1. Download & unzip the file and place the `algolia` directory into your `craft/plugins` directory
2. Install plugin in the Craft Control Panel under Settings > Plugins

Algolia works on Craft 2.4.x and Craft 2.5.x.



## Configuring Algolia

You’ll need to create a `algolia.php` configuration file in `craft/config`.

Please see sample configuration below.

```php
<?php

namespace Craft;

return [

    'applicationId' => 'YOUR_ALGOLIA_APP_ID_HERE',
    'adminApiKey' => 'your_algolia_admin_api_key_here',
    'mappings' => [
        [
            'indexName' => 'newsPosts',
            'elementType' => 'entry',
            'filter' => function(BaseElementModel $element) {
                return $element->section->handle == 'news';
            },
            'transformer' => function(BaseElementModel $element) {
                return [
                    'title' => $element->title,
                    'body' => (string) $element->body,
                ];
            }
        ],
    ],
];
```



## Upgrade Note

If upgrading from version `0.1.0`, you'll need to update your config. 

You'll change the key `indicies` to `mappings` and you'll add the `indexName` child key to each mapping. So this:

```php
    ...
    'indicies' => [
        'newsPosts' => [
            'elementType' => 'entry',
            ...
        ],
    ],
    ...
```

will become this:

```php
    ...
    'mappings' => [
        [
            'indexName' => 'newsPosts',
            'elementType' => 'entry',
            ...
        ],
    ],
    ...
```


## Advanced Setting Configuration


### Customizing Event Listeners

By default, this plugin only listens for one event:

```php
    craft()->on('elements.onSaveElement', function (Event $event) {
        craft()->algolia->indexElement($event->params['element']);
    });
```

However, you can easily customize which events you want to listen for via the `'init'` config setting (available since version `'0.2.0'`). Here's an example of a custom `'init'` key that can be added to your config:

```php
    ...
    'adminApiKey' => 'your_algolia_admin_api_key_here',
    'init' => function(){
        //listen for element save events
        craft()->on('elements.onSaveElement', function (Event $event) {
            craft()->algolia->indexElement($event->params['element']);
        });

        //listen for entry delete events
        craft()->on('entries.onDeleteEntry', function (Event $event) {
            craft()->algolia->deindexElement($event->params['entry']);
        });
        
        //you can add other events here
    },
    'mappings' => [
    ...
```


### Prefix Your Indices

By default, your indexes are prefixed with your Craft Environment constant:

```php
    'indexNamePrefix' => CRAFT_ENVIRONMENT.'_',
```

That prefix setting can be overridden in your config. The following would remove the prefix:

```php
    ...
    'adminApiKey' => 'your_algolia_admin_api_key_here',
    'indexNamePrefix' => '',
    'init' => function(){
    ...
```



***



## Contributions

- [Aaron Waldon](https://github.com/aaronwaldon) / @aaronwaldon - Reworked the logic to allow multiple element types to map to an index and to be able to override the init method from the config. Also added a method to deindex elements.