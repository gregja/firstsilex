<?php

use Silex\Application;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
//use Silex\Provider\SessionServiceProvider;

//use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;

// formulaire
use Silex\Provider\FormServiceProvider;
//use Silex\Provider\TranslationServiceProvider;

$app = new Application();
//$app->redirect(new SessionServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new AssetServiceProvider());
//$app->register(new UrlGeneratorServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new TwigServiceProvider(), array(
    'twig.path'    => array(__DIR__.'/../templates'),
    'twig.options' => array('cache' => __DIR__.'/../cache'),
));
$app->register(new HttpFragmentServiceProvider());
$app['twig'] = $app->extend('twig', function ($twig, $app) {
    // add custom globals, filters, tags, ...
//$app->register(new Silex\Provider\TranslationServiceProvider());
$app['translator.messages'] = array();
    return $twig;
});



// formulaire
$app->register(new FormServiceProvider());
//$app->register(new TranslationServiceProvider());


return $app;
