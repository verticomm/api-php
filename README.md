verticomm-api-php
=================================
Simple PHP Wrapper for Verticomm API v.0.1


The aim of this class is simple. You need to:

- Send us request for api_key and secret_hash
- Include the class in your PHP code
- Enable read/write access for your verticomm app
- Choose a Verticomm API endpoint to make the request to
- Choose either GET / POST (depending on the request) 
- Choose the fields you want to send with the request (example: `array('username' => 'johndoe')`)

You really can't get much simpler than that.

How To Use
------
#### Include the class file ####

    include_once 'ApiVerticomm.php';

#### Initialize Verticomm API class with api key token and optionally secret hash for POST requests ####

    $verticomm_api = new \Verticomm\Api( 'api_key', 'secret_hash' );

#### Choose POST fields ####

    $params =  array( 
        'username' => ‘johndoe’,
        'password' => ‘secretpass123,
);

#### Perform the request! ####

    print_r( $verticomm_api->userLogin( $params ) );

GET Request Example with some params
----------------

    $verticomm_api = new \Verticomm\Api( 'api_key', 'secret_hash' );
    $params = array(
        ‘params’ => array(
            ‘k’ => ‘vinos’
        )
    );
    print_r( $verticomm_api->getFilters( $params ) );


That is it! Really simple, works great.

To see full list of endpoints please go to the our docs in http://docs.verticommtest.apiary.io/
