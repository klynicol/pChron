<?php defined('BASEPATH') OR exit('No direct script access allowed');
include_once APPPATH . 'controllers/Base.php';

/**
 * Controller to generate a solid auth token upon successfull login.
 * This controller should also handle registering???
 */
class Login extends Base{
    public function __construct(){
        parent::__construct();
        parent::_init();
        $this->load->model('objects/user_object', 'user_object');
    }

    public function login_post(){
        $formData = $this->post(null, true);
        $this->_validateRegisterCreds($formData);
        if(!$this->user_object->loadFromUsername($formData['username']))
            $this->notAcceptable("Password or username is incorrect!");
        if(!$this->user_object->verifyPassword($formData['password']))
            $this->notAcceptable("Password or username is incorrect!");
        $token = $this->_generateToken($this->user_object->id, $this->user_object->username);
        $this->response([
            'status' => true,
            'message' => 'User loaded successfully!',
            'data' => [ 'user' => $this->user_object, 'token' => $token->__toString() ]
        ], 200);
    }

    public function register_post(){
        $formData = $this->post(null, true);
        //Validate. The frontend should validate but lets do it again for fun!
        $this->_validateRegisterCreds($formData);
		$this->user_object->username = $formData['username'];
		$this->user_object->hashPassword($formData['password']);
		$this->user_object->insertThis();
        $this->response([
            'status' => true,
            'message' => "User created succesfully!",
            'data' => [ 'user' => $this->user_object ]
        ], 200);
    }

    public function facebookRegister_post(){

    }

    public function googleRegister_post(){
        $config = $this->config->item('pcron')['google_oath'];
        $parms = [
            'client_id' => $config['client_id'],
            'redirect_url' => $config['redirect_uri'],
            'response_type' => 'code',
            'scope' => 'email profile openid'
        ];
        $response = $this->guzzle->request('POST', 'https://accounts.google.com/o/oauth2/v2/auth', $parms);
        $this->response([
            'status' => true,
            'data' => $response->getBody()
        ], 200);
    }

    public function facebookLogin_post(){

    }

    public function googleLogin_post(){

    }

    public function forgotPassword_get(){

    }

    public function forgotUsername_get(){

    }

    /**
     * Sweet, a new user! Let get them set up
     */
    private function _createUser(){

    }

    /**
     * If username and password is coming from a form, let's validaate
     * the whole kit and kabudable.
     */
    private function _validateRegisterCreds($data){
        if(!isset($data['username']))
            $this->notAcceptable("Username is not set.");
        if(!isset($data['password']))
            $this->notAcceptable("Password is not set.");
        if(!$this->_isValidUsername($data['username']))
            //$this->notAcceptable("Username is not a valid set of characters.");
            $this->notAcceptable($data);
        if(!$this->_isValidPassword($data['password']))
            $this->notAcceptable("Password is not a valid set of characters.");
    }

    /**
     * A username is a unique identifier given to accounts in websites and social media.
     * 
     * @see https://ihateregex.io/expr/username
     */
    private function _isValidUsername($username){
        if(preg_match('/^[a-z0-9_-]{3,15}$/', $username))
            return true;
        return false;
    }

    /**
     * Minimum eight characters, at least one upper case English letter, 
     * one lower case English letter, one number and one special character.
     * 
     * @see https://ihateregex.io/expr/password.
     */
    private function _isValidPassword($password){
        if(preg_match('/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$ %^&*-]).{8,}$/', $password))
            return true;
        return false;
    }

    /**
     * Generate a JWT from the login information.
     * TODO
     */
    private function _generateToken($id, $username){

        //Using pulic and private Rsa keys. I'm pretty sure this is faulting because an ssl license is not installed.
        //$signer = new Lcobucci\JWT\Signer\Rsa\Sha256();
        //$key = new Lcobucci\JWT\Signer\Key($this->config->item('pcron')['jwt_private_key']);

        //Using simple key for testing
        $signer = new Lcobucci\JWT\Signer\Hmac\Sha256();
        $key = new Lcobucci\JWT\Signer\Key('simpleKey');

        $now  = time();

        return (new Lcobucci\JWT\Builder())
            ->issuedBy($this->config->item('base_url'))
            ->permittedFor($this->config->item('base_url'))
            ->identifiedBy('pchron', true)
            ->issuedAt($now)
            ->expiresAt($now + 3600)
            ->withClaim('username', $username)
            ->withClaim('id', $id)
            ->getToken($signer, $key);
    }
}