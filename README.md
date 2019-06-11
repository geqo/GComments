# GComments
Comments mod for joomla 3.7+  
About problems you can write to the email specified in the manifest file, or just to issues in repository.
## Install
Install just as joomla module
## Minimal requirements
 - Php 5.6
 - Joomla 3.7
## Using
#### Default
Install, config, enable for position.
#### Everywhere
By this way you can use module everywhere you want. Like in com_content article template.  
`context` - is string variable, used to determine whether a comment belongs to an entity. Can be anything you like.  
`id` - Same using, but it should be item id  
`gcomments` - here is just name of module position
```php
<?php
    // Example for com_content
    // /templates/beez3/html/com_contact/contact/default.php
    $document = \Joomla\CMS\Factory::getDocument();
    $renderer = $document->loadRenderer('modules');
    echo $renderer->render('gcomments', [
        'context' => 'com_content.article',
        'id' => $this->item->id, // For com_content
    ]);
    
    // Example for com_contact
    // /templates/beez3/html/com_content/article/default.php
    $document = \Joomla\CMS\Factory::getDocument();
    $renderer = $document->loadRenderer('modules');
    echo $renderer->render('gcomments', [
    	'context' => 'com_contact.contact',
    	'id' => $this->item->id, // For com_contact, looks like com_content
    ]);
?>
```
