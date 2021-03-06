# amelayes-biophp [![Build Status](https://travis-ci.com/amelaye/biophp.svg?branch=develop)](https://travis-ci.com/amelaye/biophp) [![codecov](https://codecov.io/gh/amelaye/biophp/branch/develop/graph/badge.svg)](https://codecov.io/gh/amelaye/biophp)

## Introduction
You can read the website of the application, with docs and informations about the project : http://www.amelayes-biophp.net.

This is my own version of BioPHP available here : http://biophp.org. It requires at least PHP 7.2 and every framework which can work on PHP 7.2, 
as Symfony 4.

The original legacy code is included inside, and the licence is still GPL2.

It works very easily :

![alt text](http://www.amelayes-biophp.net/img/biophp2.png "BioPHP schema")

The application is connected with a **REST API**, for the Biology Data, available at : http://api.amelayes-biophp.net.
If you want to use your own API, it must implements my schema API (cf. code documentation), and then you can have fun !

The **BioTools** (amelaye/biotools) package is actually in working progress, please be patient, and you'll can have more features soon !
Instead of it, you can use your own application.

## Content
BioPHP is actually a Symfony 4 bundle. Create your own PHP application and run :

```shell
$ composer require amelaye/biophp
```
If you want to use the predefined data schema, please run after :

```bash
$ bin/console doctrine:schema:create
```
And then it creates the tables structure.

## Using a Symfony 4 application

You can have some examples for a Symfony 4 application here : http://demo.amelayes-biophp.net

## Using you own code (standalone)

If you have your standalone version, you can use it like this (after installing composer and creating a composer.json file) :

```php
<?php
require_once 'vendor/autoload.php';

use Amelaye\BioPHP\Domain\Sequence\Service\SequenceManager;
use Doctrine\Common\Annotations\AnnotationRegistry;
AnnotationRegistry::registerLoader('class_exists');

$client = new GuzzleHttp\Client([
    'base_uri' => 'http://api.amelayes-biophp.net'
]);
$serializer = JMS\Serializer\SerializerBuilder::create()
    ->build();

$aminoApiManager  = new \Amelaye\BioPHP\Api\AminoApi($client, $serializer);
$nucleotidManager = new \Amelaye\BioPHP\Api\NucleotidApi($client, $serializer);
$elementManager   = new \Amelaye\BioPHP\Api\ElementApi($client, $serializer);
$sequenceManager  = new SequenceManager($aminoApiManager, $nucleotidManager, $elementManager);

$aMirrors = $sequenceManager->findMirror("AGGGAATTAAGTAAATGGTAGTGG", 6, 8, 'E');

echo "<pre>";
var_dump($aMirrors);
echo "<pre>";
?>
```

## Important
This version is an ALPHA version. That means it could be quite unstable, and modifications in the system could be done. 
Please read often the documentation, I try to update it as regularly as possible :)