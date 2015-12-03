# SamsonPHP Instagram API module

[![Latest Stable Version](https://poser.pugx.org/samsonos/php_instagram/v/stable.svg)](https://packagist.org/packages/samsonos/php_instagram) 
[![Build Status](https://scrutinizer-ci.com/g/samsonos/php_instagram/badges/build.png?b=master)](https://scrutinizer-ci.com/g/samsonos/php_instagram/badges/build.png?b=master)
[![Code Coverage](https://scrutinizer-ci.com/g/samsonos/php_instagram/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/samsonos/php_instagram/?branch=master)
[![Total Downloads](https://poser.pugx.org/samsonos/php_instagram/downloads.svg)](https://packagist.org/packages/samsonos/php_instagram)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/samsonos/php_instagram/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/samsonos/php_instagram/?branch=master)
[![Stories in Ready](https://badge.waffle.io/samsonos/php_instagram.png?label=ready&title=Ready)](https://waffle.io/samsonos/php_instagram)

## Configuration

First of all you need to create configuration class which is working thanks to [SamsonPHP module/service configuration](https://github.com/samsonphp/config):

```php
class InstagramConfig extends \samsonphp\config\Entity
{
    public $appId = 'YOUR_CLIENT_ID';

    public $appSecret = 'YOUR_CLIENT_SECRET';
}
```

## Get list of posts by tag

After creating configuration you can using module methods.
First method uses Instagram [Tag Endpoints](https://www.instagram.com/developer/endpoints/tags/) API.
Used method is ```listByTag($tag, $getInstaResult = false, $count = 0, $maxTagID = null, $minTagID = null)```.
Here the second parameter define your response. It can be past object (if true) or just post image (if false).

For example you want to get 10 posts by hashtag ```adventure```:

```php
/** @var \samson\instagram\Instagram $instagram Get SamsonPHP Instagram module */
$instagram = m('instagram');

// Define tag
$myTag = 'adventure';

// Get list of posts
$posts = $instagram->listByTag($myTag, true, 10);
```

## Like post

This method uses Instagram [Like Endpoints](https://www.instagram.com/developer/endpoints/likes/) API.
For using it you just need to know users access token for your application, and media identifier.
Notice, that this method can toggle like status, so if you create this request for already liked media, this media will unliked.
Simple example:

```php
/** @var \samson\instagram\Instagram $instagram Get SamsonPHP Instagram module */
$instagram = m('instagram');

// Define media to like
$myMediaID = '657988443280050001_25025320';

// Get list of posts
$posts = $instagram->likeMedia($myMediaID, $access_token);
```


This module is working using [Instagram API](https://www.instagram.com/developer/)
