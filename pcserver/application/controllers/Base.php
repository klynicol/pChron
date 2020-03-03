<?php defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/third_party/RestController.php';
require APPPATH . '/third_party/Format.php';
use chriskacerguis\RestServer\RestController;
use Lcobucci\JWT;

/**
 * Serves as a base for all Controllers
 * 
 * @author Mark Wickine 2020-01-13
 */

class Base extends RestController{

    protected $guzzle; //Guzzle instance https://github.com/guzzle/guzzle
    protected $auth; //The claims from the JWT token.

    function __construct(){
        parent::__construct();

        //TODO go over the rest.php and config.php settings in detail
        //TODO setup log in model or method of authentication
    }

    /**
     * Method to be called from controllers protected by token authorization.
     * This will return not acceptable on bad requests.
     */
    protected function loadAuthToken(){
        if(!$tokenString = $this-->_authTokenExists())
            $this->notAcceptable('Not Authorized!');

        $token = (new JWT\Parser())->parse($tokenString);

        $data = (new JWT\ValidationData())
            ->setIssuer($this->config->item('base_url'))
            ->setAudience($this->config->item('base_url'))
            ->setId($this->config->item('auth_token_hash'));

        if(!$token->validate($token, $data))
            $this->notAcceptable('Not Authorized!');

        //TODO set a new expiration time for the token
        //$data->setCurrentTime($time + 61);

        $this->auth = $token->getClaims();
    }

    /**
     * Initialize resources. To be done when authorized.
     */
    protected function _init(){
        date_default_timezone_set('America/Chicago');
        $this->guzzle = new GuzzleHttp\Client();
        $this->config->load('pChron', true);
    }


    /**
     * Check if the Authorization header exists and return it, else return false.
     * 
     * @return string|false
     */
    private function _authTokenExists(){
        $headers = $this->head_args;
        if(!empty($headers) && is_array($headers) && array_key_exists('Authorization', $headers)) {
            return $headers['Authorization'];
        }
        return false;
    }

    /**
     * A quick method for returning unaceptable information.
     * 
     * @param string $message The error message to display.
     */
    protected function notAcceptable($message){
        $this->response([
            'status' => false,
            'message' => $message
        ], 406);
    }
}