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
    }

    public function login_post(){
        $username = $this->post('username', true);
        $password = $this->post('password', true);
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