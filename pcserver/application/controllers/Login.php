<?php defined('BASEPATH') OR exit('No direct script access allowed');
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;

/**
 * Controller to generate a solid auth token upon successfull login.
 * This controller should also handle registering???
 */
class Login extends Base{
    public function __construct(){
        parent::__construct();
        parent::_init();
        $this->load->model('user_model');
    }

    public function login_post(){
        $formData = $this->post(null, true);
        $this->_validateRegisterCreds($formData);
    }

    public function register_post(){
        $formData = $this->post(null, true);
        //Validate. The frontend should validate but lets do it again for fun!
        $this->_validateRegisterCreds($formData);
        $thi
    }

    public function facebookRegister_post(){

    }

    public function googleRegister_post(){

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
        if(!_validUsername($data['username']))
            $this->notAcceptable("Username is not a valid set of characters.");
        if(!_validUsername($data['username']))
            $this->notAcceptable("Password is not a valid set of characters.");
    }

    /**
     * A username is a unique identifier given to accounts in websites and social media.
     * 
     * @see https://ihateregex.io/expr/username
     */
    private function _validUsername($username){
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
    private function _validPassword($password){
        if(preg_match('/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$ %^&*-]).{8,}$/', $password))
            return true;
        return false;
    }

    /**
     * Generate a JWT from the login information.
     * TODO
     */
    private function _generateToken($id){
        $signer = new JWT\Signer\Rsa\Sha256();
        $key = new JWT\Signer\Hmac\Key($this->config->item('auth_token_hash'));
        $now  = new DateTimeImmutable();

        return (new Builder())
            ->issuedBy($this->config->item('base_url'))
            ->permittedFor($this->config->item('base_url'))
            ->identifiedBy('pchron')
            ->issuedAt($now)
            ->expiresAt($now->modify('+24 hour'))
            ->withClaim('uid', $id)
            ->getToken($signer, $key);
    }
}