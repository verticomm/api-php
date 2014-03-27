<?php
namespace Verticomm;

/**
 * This class allows the user to consume Verticomm Api services
 *
 * @author <piotr.gawronski(at)verticomm.eu>
 * @version 0.1
 */ 
class Api
{
    /**
     * @var string User's api key
     */
    private $api_key;
        
    /**
     * @var string User's secret
     */
    private $secret;
    
    /**
     * @var string Language flag
     */ 
    private $language = 'es_ES';
    
    /**
     * @var string Current instance flag
     */
    private $instance = 'uvinum';
    
    /**
     * @var string Request method flag
     */
    private $request_method = 'GET';
    
    /**
     * Class constructor. Sets user's api key and secret.
     * 
     * @param string $api_key
     * @param string $secret
     * 
     * @throws \InvalidArgumentException
     */
    public function __construct( $api_key, $secret )
    {
        if ( empty( $api_key ) || empty( $secret ) )
        {
            throw new \InvalidArgumentException( 'You need to provide an api key.' );
        }
        
        $this->setApiKey( $api_key );
        $this->setSecret( $secret );
    }
    
    private function setApiKey( $api_key )
    {
        $this->api_key = $api_key;
    }
    
    private function getApiKey()
    {
        return $this->api_key;
    }
    
    private function setSecret( $secret )
    {
        $this->secret = $secret;
    }
    
    private function getSecret()
    {
        return $this->secret;
    }
    
    private function getLanguage( )
    {
        return $this->language;
    }
    
    public function setLanguage( $language )
    {
        $this->language = $language;
    }
    
    private function getInstance( )
    {
        return $this->instance;
    }
    
    public function setInstance( $instance )
    {
        $this->instance = $instance;
    }

	/**
	 * Makes a request
	 *
	 * @param string $method
	 * @param array $post_params
	 * @throws \InvalidArgumentException
	 *
	 * @return resource
	 *
	 */
    private function request( $method, $post_params = array() )
    {
        if ( 'POST' === $this->request_method && false === $this->getSecret() )
        {
            throw new \InvalidArgumentException( 'You must provide a request secret' );
        }

        $curl_url = $this->constructCurlUrl( $method );
        
        // Call the API
        $curl       = curl_init( $curl_url );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, 10 );
		    curl_setopt( $curl, CURLOPT_TIMEOUT, 30 );
        if ( 'POST' === $this->request_method )
        {
            $post_params['secure_hash'] = $this->calculateSecureHash( $curl_url );
            
            curl_setopt( $curl, CURLOPT_POST, true );
			      curl_setopt( $curl, CURLOPT_VERBOSE, false );
			      curl_setopt( $curl, CURLOPT_POSTFIELDS, http_build_query( $post_params ) );
        }
        $response   = curl_exec( $curl );
        curl_close( $curl );

        // Return
        return $response;
    }
    
    /**
     * Sets request method to POST if given endpoint is in the list
     * of POST endpoints
     * 
     * @param string $endpoint
     * @return string
     */
    private function setRequestMethod( $endpoint )
    {
        $post_endpoints = array(
            'shoppingCartCreate',
            'getShoppingCart',
            'shoppingCartUpdate',
            'applyDiscount',
            'removeDiscount',
            'checkout',
            'paymentFinish',
            'paymentCheck',
            'userLogin',
            'getOrdersHistory'
        );
        
        if ( in_array( $endpoint, $post_endpoints ) )
        {
            $this->request_method = 'POST';
        }
    }
    
    /**
     * Builds final url for given method name
     * 
     * @param string $method
     * @return string
     */
    private function constructCurlUrl( $method )
    {
        $query_params['api_key']  = $this->getApiKey();
        $query_params['language'] = $this->getLanguage();
        $query_params['instance'] = $this->getInstance();
        
        return 'https://api.vcst.net/' . $method . '?' . http_build_query( $query_params, '', '&' );
    }
    
    /**
     * Calculates secure hash for given url
     *  
     * @param string $url
     * @return string
     */
    private function calculateSecureHash( $url )
    {
        return sha1( $url . $this->secret );
    }

	/**
	 * Constructs final URI.
	 *
	 * @param string $method
	 * @param array|bool $filters
	 * @return string
	 */
    private function methodConstruct( $method, $filters = false )
    {
        if ( false !== $filters && is_array( $filters ) )
        {
            // pagination
            $page = '';
            if ( isset( $filters['page'] ) )
            {
                $page .= ':' . $filters['page'];
                unset( $filters['page'] );
            }

            foreach( $filters as $key => $param )
            {
                $method .= ':' . $key . ':' . $param;
            }
            
            $method .= $page;
        }

        return $method;
    }
    
    /**
     * Process given params and called requested endPoint
     * 
     * @param string $endpoint
     * @param array $params
     * @return resource
     * 
     * @throws \BadMethodCallException
     */
    public function __call( $endpoint, $params )
    {
        if ( !isset( $endpoint ) )
        {
            throw new \BadMethodCallException( 'You need to provide an valid method' );
        }

        // GET PARAMS i.e. k => vinos
        $get_params = false;
        if ( isset( $params[0]['params'] ) )
        {
            $get_params = $params[0]['params'];
            unset( $params[0]['params'] );
        }
        
        // POST params
        $post_params = ( isset( $params[0] ) ) ? $params[0] : array();

        $this->setRequestMethod( $endpoint );
        
        $method = $this->methodConstruct( $endpoint, $get_params );

        return $this->request( $method, $post_params );
    }

	/**
	 * Calls productSearch endpoint.
	 *
	 * @param string $params
	 * @return resource
	 */
    public function productSearch( $params )
    {
        $method = 'productSearch/' . urlencode( $params['params'] );
        
        return $this->request( $method );
    }
}
