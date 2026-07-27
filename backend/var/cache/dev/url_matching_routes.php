<?php

/**
 * This file has been auto-generated
 * by the Symfony Routing Component.
 */

return [
    false, // $matchHost
    [ // $staticRoutes
        '/_wdt/styles' => [[['_route' => '_wdt_stylesheet', '_controller' => 'web_profiler.controller.profiler::toolbarStylesheetAction'], null, null, null, false, false, null]],
        '/_profiler' => [[['_route' => '_profiler_home', '_controller' => 'web_profiler.controller.profiler::homeAction'], null, null, null, true, false, null]],
        '/_profiler/search' => [[['_route' => '_profiler_search', '_controller' => 'web_profiler.controller.profiler::searchAction'], null, null, null, false, false, null]],
        '/_profiler/search_bar' => [[['_route' => '_profiler_search_bar', '_controller' => 'web_profiler.controller.profiler::searchBarAction'], null, null, null, false, false, null]],
        '/_profiler/phpinfo' => [[['_route' => '_profiler_phpinfo', '_controller' => 'web_profiler.controller.profiler::phpinfoAction'], null, null, null, false, false, null]],
        '/_profiler/xdebug' => [[['_route' => '_profiler_xdebug', '_controller' => 'web_profiler.controller.profiler::xdebugAction'], null, null, null, false, false, null]],
        '/_profiler/open' => [[['_route' => '_profiler_open_file', '_controller' => 'web_profiler.controller.profiler::openAction'], null, null, null, false, false, null]],
        '/api/alerts' => [[['_route' => 'app_api_alert_index', '_controller' => 'App\\Controller\\Api\\AlertController::index'], null, ['GET' => 0], null, false, false, null]],
        '/api/health' => [[['_route' => 'app_api_health__invoke', '_controller' => 'App\\Controller\\Api\\HealthController'], null, ['GET' => 0], null, false, false, null]],
        '/api/maintenance-tickets' => [
            [['_route' => 'app_api_maintenance_index', '_controller' => 'App\\Controller\\Api\\MaintenanceController::index'], null, ['GET' => 0], null, false, false, null],
            [['_route' => 'app_api_maintenance_create', '_controller' => 'App\\Controller\\Api\\MaintenanceController::create'], null, ['POST' => 0], null, false, false, null],
        ],
        '/api/robots' => [
            [['_route' => 'app_api_robot_index', '_controller' => 'App\\Controller\\Api\\RobotController::index'], null, ['GET' => 0], null, false, false, null],
            [['_route' => 'app_api_robot_create', '_controller' => 'App\\Controller\\Api\\RobotController::create'], null, ['POST' => 0], null, false, false, null],
        ],
        '/api/simulation/tick' => [[['_route' => 'app_api_simulation_tick', '_controller' => 'App\\Controller\\Api\\SimulationController::tick'], null, ['POST' => 0], null, false, false, null]],
        '/api/technicians' => [[['_route' => 'app_api_technician__invoke', '_controller' => 'App\\Controller\\Api\\TechnicianController'], null, ['GET' => 0], null, false, false, null]],
    ],
    [ // $regexpList
        0 => '{^(?'
                .'|/_(?'
                    .'|error/(\\d+)(?:\\.([^/]++))?(*:38)'
                    .'|wdt/([^/]++)(*:57)'
                    .'|profiler/(?'
                        .'|font/([^/\\.]++)\\.woff2(*:98)'
                        .'|([^/]++)(?'
                            .'|/(?'
                                .'|search/results(*:134)'
                                .'|router(*:148)'
                                .'|exception(?'
                                    .'|(*:168)'
                                    .'|\\.css(*:181)'
                                .')'
                            .')'
                            .'|(*:191)'
                        .')'
                    .')'
                .')'
                .'|/api/(?'
                    .'|alerts/([^/]++)/(?'
                        .'|acknowledge(*:240)'
                        .'|resolve(*:255)'
                    .')'
                    .'|maintenance\\-tickets/([^/]++)(*:293)'
                    .'|robots/([^/]++)(?'
                        .'|(*:319)'
                        .'|/(?'
                            .'|status(*:337)'
                            .'|t(?'
                                .'|elemetry(*:357)'
                                .'|imeline(*:372)'
                            .')'
                        .')'
                    .')'
                .')'
            .')/?$}sDu',
    ],
    [ // $dynamicRoutes
        38 => [[['_route' => '_preview_error', '_controller' => 'error_controller::preview', '_format' => 'html'], ['code', '_format'], null, null, false, true, null]],
        57 => [[['_route' => '_wdt', '_controller' => 'web_profiler.controller.profiler::toolbarAction'], ['token'], null, null, false, true, null]],
        98 => [[['_route' => '_profiler_font', '_controller' => 'web_profiler.controller.profiler::fontAction'], ['fontName'], null, null, false, false, null]],
        134 => [[['_route' => '_profiler_search_results', '_controller' => 'web_profiler.controller.profiler::searchResultsAction'], ['token'], null, null, false, false, null]],
        148 => [[['_route' => '_profiler_router', '_controller' => 'web_profiler.controller.router::panelAction'], ['token'], null, null, false, false, null]],
        168 => [[['_route' => '_profiler_exception', '_controller' => 'web_profiler.controller.exception_panel::body'], ['token'], null, null, false, false, null]],
        181 => [[['_route' => '_profiler_exception_css', '_controller' => 'web_profiler.controller.exception_panel::stylesheet'], ['token'], null, null, false, false, null]],
        191 => [[['_route' => '_profiler', '_controller' => 'web_profiler.controller.profiler::panelAction'], ['token'], null, null, false, true, null]],
        240 => [[['_route' => 'app_api_alert_acknowledge', '_controller' => 'App\\Controller\\Api\\AlertController::acknowledge'], ['id'], ['PATCH' => 0], null, false, false, null]],
        255 => [[['_route' => 'app_api_alert_resolve', '_controller' => 'App\\Controller\\Api\\AlertController::resolve'], ['id'], ['PATCH' => 0], null, false, false, null]],
        293 => [[['_route' => 'app_api_maintenance_update', '_controller' => 'App\\Controller\\Api\\MaintenanceController::update'], ['id'], ['PATCH' => 0], null, false, true, null]],
        319 => [[['_route' => 'app_api_robot_show', '_controller' => 'App\\Controller\\Api\\RobotController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        337 => [[['_route' => 'app_api_robot_status', '_controller' => 'App\\Controller\\Api\\RobotController::status'], ['id'], ['PATCH' => 0], null, false, false, null]],
        357 => [[['_route' => 'app_api_robot_telemetry', '_controller' => 'App\\Controller\\Api\\RobotController::telemetry'], ['id'], ['GET' => 0], null, false, false, null]],
        372 => [
            [['_route' => 'app_api_robot_timeline', '_controller' => 'App\\Controller\\Api\\RobotController::timeline'], ['id'], ['GET' => 0], null, false, false, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];
