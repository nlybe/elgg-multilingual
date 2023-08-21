# Elgg Multilingual Plugin

![Elgg 5.0](https://img.shields.io/badge/Elgg-5.0-orange.svg?style=flat-square)

Multilingual content on Elgg posts and content.

## Features

- Option to enable multilingual content for pages (pages plugin) in plugin settings
- Option to enable multilingual content for site pages (externalpages plugin) in plugin settings
- Compatible with [External pages Extended](https://github.com/nlybe/Elgg-externalpages_extended)
- It can be used from other plugins for translating specific field of Elgg Objects
- The [Language selector](https://github.com/ColdTrick/language_selector) plugin is suggested for changing site languages

## How to use

Let's say that you have an Elgg Object called MyElggObject and you need to add translation for the fields title, description and location. You have to do the following in order to use the Multilingual for translating these fields:

1. Add the lines below on **start.php** or **bootstrap.php**:

```php
use Multilingual\MultilingualOptions;

if (elgg_is_active_plugin('multilingual')) {
    $options = [
        'title' => ['type' => 'text', 'label' => 'myplugin:title', 'annotate' => false],
        'description' => ['type' => 'longtext', 'label' => 'myplugin:description', 'annotate' => false],
        'location' => ['type' => 'location', 'label' => 'myplugin:location', 'annotate' => false],
    ];
    MultilingualOptions::setSubtypeForTranslation(MyElggObject::SUBTYPE, $options);
}
```

This code will add the sub-item **Edit translations** on all MyElggObject entities menu. When click on **Edit translations** a form will open for translating
the fields title, description and location for all enabled languages of the site.

2. Wherever any of the fields title, description and location appear, make the following replacements:

```php
use Multilingual\MultilingualOptions;

// $title = $entity->title; # to be replaced by
$title = MultilingualOptions::getFieldValue('title', $entity);

// $description = $entity->description; # to be replaced by
$description = MultilingualOptions::getFieldValue('description', $entity);

// $location = $entity->location; # to be replaced by
$location = MultilingualOptions::getFieldValue('location', $entity);
```


