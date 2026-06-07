<?php

declare(strict_types=1);
/** @var \CodeIgniter\Router\RouteCollection $routes */
$routes->group('newsletter', ['namespace' => '\App\Controllers\Api\V1\Newsletter'], function ($routes): void {
    // Public routes (no auth required)
    $routes->post('subscribers', 'SubscriberController::subscribe');
    $routes->get('subscribers/confirm/(:segment)', 'SubscriberController::confirm/$1');
    $routes->post('subscribers/unsubscribe', 'SubscriberController::unsubscribe');
    $routes->get('projects/(:segment)/config', 'ProjectController::config/$1');

    // Auth & Admin Protected Group
    $routes->group('', ['filter' => ['domainauth', 'throttle']], function ($routes): void {
        $routes->group('', ['filter' => 'permission:newsletter.projects.read'], function ($routes): void {
            $routes->get('projects', 'ProjectController::index');
            $routes->get('projects/(:num)', 'ProjectController::show/$1');
        });
        $routes->group('', ['filter' => 'permission:newsletter.projects.write'], function ($routes): void {
            $routes->post('projects', 'ProjectController::create');
            $routes->put('projects/(:num)', 'ProjectController::update/$1');
        });
        $routes->group('', ['filter' => 'permission:newsletter.projects.delete'], function ($routes): void {
            $routes->delete('projects/(:num)', 'ProjectController::delete/$1');
        });
        $routes->group('', ['filter' => 'permission:newsletter.subscribers.read'], function ($routes): void {
            $routes->get('subscribers', 'SubscriberController::index');
            $routes->get('subscribers/(:num)', 'SubscriberController::show/$1');
        });
        $routes->group('', ['filter' => 'permission:newsletter.subscribers.write'], function ($routes): void {
            $routes->put('subscribers/(:num)', 'SubscriberController::update/$1');
        });
        $routes->group('', ['filter' => 'permission:newsletter.subscribers.delete'], function ($routes): void {
            $routes->delete('subscribers/(:num)', 'SubscriberController::delete/$1');
        });
        $routes->group('', ['filter' => 'permission:newsletter.campaigns.read'], function ($routes): void {
            $routes->get('campaigns', 'CampaignController::index');
            $routes->get('campaigns/(:num)', 'CampaignController::show/$1');
        });
        $routes->group('', ['filter' => 'permission:newsletter.campaigns.write'], function ($routes): void {
            $routes->post('campaigns', 'CampaignController::create');
            $routes->put('campaigns/(:num)', 'CampaignController::update/$1');
        });
        $routes->group('', ['filter' => 'permission:newsletter.campaigns.delete'], function ($routes): void {
            $routes->delete('campaigns/(:num)', 'CampaignController::delete/$1');
        });
        $routes->group('', ['filter' => 'permission:newsletter.deliveries.read'], function ($routes): void {
            $routes->get('deliveries', 'DeliveryController::index');
            $routes->get('deliveries/(:num)', 'DeliveryController::show/$1');
        });
        $routes->group('', ['filter' => 'permission:newsletter.deliveries.write'], function ($routes): void {
            $routes->post('deliveries', 'DeliveryController::create');
            $routes->put('deliveries/(:num)', 'DeliveryController::update/$1');
        });
        $routes->group('', ['filter' => 'permission:newsletter.deliveries.delete'], function ($routes): void {
            $routes->delete('deliveries/(:num)', 'DeliveryController::delete/$1');
        });
    });
});
