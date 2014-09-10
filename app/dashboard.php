<?php

namespace OCA\Dashboard\App;

use \OCP\AppFramework\App;
use \OCA\Dashboard\Controller\PageController;
use \OCA\Dashboard\Controller\APIStatsController;
use \OCA\Dashboard\Service\StatService;
use \OCA\Dashboard\Service\HistoryService;
use \OCA\Dashboard\Service\StatsTaskService;
use \OCA\Dashboard\Db\HistoryMapper;

class Dashboard extends App {

    /**
     * Define your dependencies in here
     */
    public function __construct(array $urlParams=array()){
        parent::__construct('dashboard', $urlParams);

        $container = $this->getContainer();

        /**
         * Controllers
         */
        $container->registerService('PageController', function($c){
            return new PageController(
                $c->query('AppName'),
                $c->query('Request'),
                $c->query('CoreConfig'),
                $c->query('UserId'),
                $c->query('StatService')
            );
        });

        $container->registerService('ApiStatsController', function($c){
            return new APIStatsController(
                $c->query('AppName'),
                $c->query('Request'),
                $c->query('CoreConfig'),
                $c->query('UserId'),
                $c->query('StatService'),
                $c->query('HistoryService')
            );
        });

        $container->registerService('StatService', function($c){
            return new StatService(
                $c->query('UserManager')
            );
        });

        $container->registerService('HistoryService', function($c){
            return new HistoryService(
                $c->query('HistoryMapper')
            );
        });

        $container->registerService('UserManager', function($c) {
            return $c->query('ServerContainer')->getUserManager();
        });

        $container->registerService('StatsTaskService', function($c) {
            return new StatsTaskService(
                $c->query('StatService'),
                $c->query('HistoryMapper')
            );
        });

        /**
         * Database Layer
         */
        $container->registerService('HistoryMapper', function($c) {
            return new HistoryMapper(
                $c->query('ServerContainer')->getDb()
            );
        });

        /**
         * Core
         */
        $container->registerService('UserId', function($c) {
            return \OCP\User::getUser();
        });

        $container->registerService('L10N', function($c) {
            return \OC_L10N::get($c['AppName']);
        });

        $container->registerService('CoreConfig', function($c) {
            return $c->query('ServerContainer')->getConfig();
        });

    }


}