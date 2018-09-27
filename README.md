# GComments
Comments mod for joomla 3.7+  
About problems you can write to the email specified in the manifest file, or just to issues in repository.
## Install
Install just as joomla module
## Minimal requirements
 - Php 5.6
 - Joomla 3.7
 - jQuery
## Using
#### Default
Install, config, enable for position.
#### Everywhere
By this way you can use module everywhere you want. Like in com_content article template.
```php
<?php
    // gcoments - position name, it can be different
    $document = \Joomla\CMS\Factory::getDocument();
    $renderer = $document->loadRenderer('modules');
    echo $renderer->render('gcomments', [
        'context' => 'com_content.article',
        'id' => $this->item->id, // For com_content
    ]);
?>
```
