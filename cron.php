<?php
require __DIR__ . '/vendor/autoload.php';
/**
 * This script will be called periodically as a cron job.
 */

use otn\linkeddatex2\gather\DeployingWriter;
use otn\linkeddatex2\gather\GraphProcessor;
use GO\Scheduler;

// TODO FIRST deploy this to testing laptop

// Scheduler setup
//$scheduler = new Scheduler();
//$scheduler->call('acquire_data')->at('* * * * *');

acquire_data();

function acquire_data() {
    $KiB = 1024; // readability
    date_default_timezone_set("Europe/Brussels");
    $writer = new DeployingWriter(__DIR__ . '/public/parking', 0.5*$KiB); // 10 KiB for testing

    // GRAPH CONSTRUCTION AND DATA STRIPPING
    // TODO CACHE HEADERS?
    // https://github.com/peppeocchi/php-cron-scheduler
    $result = GraphProcessor::construct_graph(true);
    $arr_graph = $result["graph"];
    $static_headers = $result["static_headers"];
    $dynamic_headers = $result["dynamic_headers"]; //TODO when website uses caching headers, we can use these to regulate querying frequency
    //$arr_graph = GraphProcessor::construct_stub_graph(); // Use this for testing if site is down
    $parkings = GraphProcessor::get_parkings_from_graph($arr_graph);
    $static_data = GraphProcessor::strip_static_data_from_parkings($parkings);
    $writer->set_deployment_metadata($static_data); // TODO this shouldn't happen every time, use cache headers
    $dynamic_data = GraphProcessor::strip_dynamic_data_from_parkings($parkings);
    $writer->write(json_encode($dynamic_data));
}
