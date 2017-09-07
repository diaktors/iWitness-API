#!/usr/bin/env php
<?php
// application.php
require dirname(__FILE__) . '/Bootstrap.php';

use Api\V1\Service\Command\AssetCommand;
use Api\V1\Service\Command\EventCommand;
use Symfony\Component\Console\Application;

Bootstrap::initializeLogger('logger-video');
$application = new Application();
$application->add(Bootstrap::getServiceManager()->get('Api\V1\Service\Command\AssetCommand'));
$application->add(Bootstrap::getServiceManager()->get('Api\V1\Service\Command\EventCommand'));
$application->run();
