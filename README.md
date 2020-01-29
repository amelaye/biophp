# amelayes-biophp [![Build Status](https://travis-ci.com/amelaye/biophp.svg?branch=develop)](https://travis-ci.com/amelaye/biophp) [![codecov](https://codecov.io/gh/amelaye/biophp/branch/develop/graph/badge.svg)](https://codecov.io/gh/amelaye/biophp)

## Introduction
You can read the website of the application, with docs and informations about the project : http://www.amelayes-biophp.net.

This is my own version of BioPHP available here : http://biophp.org.

I wanted to remind Symfony's basics for my certification, so I decided to renew this project, my way.
(I'm a PHP certified engineer and just wanted to know a little more about biology :))

The original legacy code is included inside, and the licence is still GPL2.

## Content
At this moment, this is a Symfony3.4 application, divided into 3 bundles :
- **AppBundle :** Core of the application. The entities will be linked to the datatabase, Made to insert sequences data from your experiments.
I took and modelized with Doctrine this structure : http://genephp.sourceforge.net/mysql_dbscripts.html
- **MinitoolsBundle :** Some useful forms, directly inspired from the original Minitools, but refactored. This uses some AppBundle features. 
This bundle will be soon desolidarized from this project, it will be available in another package.

The application is connected with a **REST API**, for the Biology Data, available at : http://api.amelayes-biophp.net.

## Please wait :)
I planned for the future to make a **Packagist library**, available doing a simple composer 
install. Please be patient, refactoring PHP4 scripts is very, very long :) ... 
There are also Unit Tests, running for the MinitoolsBundle.

Only **develop** branch is often up-to-date. **Master** branch will be updated when editing the first beta versions.
