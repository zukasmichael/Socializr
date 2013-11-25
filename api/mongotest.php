<?php

require_once __DIR__.'/../vendor/autoload.php';


try {
    /**
     *
     * Create connection and
     * access mongotest collection
     *
     */
    // open connection to MongoDB server
    $conn = new Mongo('localhost');

    // access database
    $db = $conn->test;

    // access collection
    $collection = $db->mongotest;
    // empty collection
    $collection->remove([]);

    /**
     *
     * Add a product
     *
     */
    // Create an array of values to insert
    $product = [
        'name' => 'Televisions',
        'quantity' => 25,
        'price' => 499.99,
        'note' => '54 inch flat screens'
    ];

    // insert the array
    $collection->insert( $product );

    // execute query
    // retrieve all documents
    $cursor = $collection->find();

    // iterate through the result set
    // print each document
    echo $cursor->count() . ' document(s) found. <br/>';
    foreach ($cursor as $obj) {
        echo 'Name: ' . $obj['name'] . '<br/>';
        echo 'Quantity: ' . $obj['quantity'] . '<br/>';
        echo 'Price: ' . $obj['price'] . '<br/>';
        echo 'Note: ' . $obj['note'] . '<br/>';
        echo '<br/>';
    }

    /**
     *
     * Update a product
     *
     */
    // the array of product criteria
    $product_array = array(
        'name' => 'Televisions',
    );

    // fetch the Jackets record
    $document = $collection->findOne( $product_array );

    // specify new values for Jackets
    $document['name'] = 'Flat televisions';
    $document['quantity'] = 100;
    $document['note'] = 'Quality flat television';

    // save back to the database
    $collection->save( $document );

    // fetch the Jackets record
    $doc = $collection->findOne( ['quantity' => 100] );
    echo 'Name: ' . $doc['name'] . '<br/>';
    echo 'Quantity: ' . $doc['quantity'] . '<br/>';
    echo 'Price: ' . $doc['price'] . '<br/>';
    echo 'Note: ' . $doc['note'] . '<br/>';
    echo '<br/>';


    // disconnect from server
    $conn->close();
} catch (MongoConnectionException $e) {
    die('Error connecting to MongoDB server');
} catch (MongoException $e) {
    die('Error: ' . $e->getMessage());
}

exit;













$app = new Silex\Application();

require __DIR__.'/../resources/config/dev.php';
require __DIR__.'/../app/app.php';

require __DIR__.'/../app/controllers.php';

$app['http_cache']->run();