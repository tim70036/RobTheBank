<?php
namespace AWSCognitoApp;

use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;

class AWSCognitoWrapper
{
    private $COOKIE_NAME = 'aws-cognito-app-access-token';
    private $region;
    private $client_id;
    private $userpool_id;

    private $client;

    private $user = null;

    public function __construct()
    {
        $this->region = getenv('REGION') ? getenv('REGION') : 'us-east-1';
        $this->client_id = getenv('CLIENT_ID') ?  getenv('CLIENT_ID') : '5pa821ikfkhe7btv4kkbqunl23';
        $this->userpool_id = getenv('USERPOOL_ID') ? getenv('USERPOOL_ID') : 'us-east-1_36LJEEBUl';
    }

    public function initialize()
    {
        $this->client = new CognitoIdentityProviderClient([
          'version' => '2016-04-18',
          'region' => $this->region,
        ]);

        try {
            $this->user = $this->client->getUser([
                'AccessToken' => $this->getAuthenticationCookie()
            ]);
        } catch(\Exception  $e) {
            // an exception indicates the accesstoken is incorrect - $this->user will still be null
        }
    }

    public function authenticate(string $username, string $password)
    {
        try {
            $result = $this->client->adminInitiateAuth([
                'AuthFlow' => 'ADMIN_NO_SRP_AUTH',
                'ClientId' => $this->client_id,
                'UserPoolId' => $this->userpool_id,
                'AuthParameters' => [
                    'USERNAME' => $username,
                    'PASSWORD' => $password,
                ],
            ]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        $this->setAuthenticationCookie($result->get('AuthenticationResult')['AccessToken']);

        return '';
    }

    public function signup(string $username, string $email, string $password)
    {
        try {
            $result = $this->client->signUp([
                'ClientId' => $this->client_id,
                'Username' => $username,
                'Password' => $password,
                'UserAttributes' => [
                    [
                        'Name' => 'name',
                        'Value' => $username
                    ],
                    [
                        'Name' => 'email',
                        'Value' => $email
                    ]
                ],
            ]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return '';
    }

    public function resendConfirmCode(string $username)
    {
        try {
            $result = $this->client->resendConfirmationCode([
                'ClientId' => $this->client_id,
                'Username' => $username
            ]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return '';
    }

    public function confirmSignup(string $username, string $code)
    {
        try {
            $result = $this->client->confirmSignUp([
                'ClientId' => $this->client_id,
                'Username' => $username,
                'ConfirmationCode' => $code,
            ]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return '';
    }

    public function sendPasswordResetMail(string $username)
    {
        try {
            $this->client->forgotPassword([
                'ClientId' => $this->client_id,
                'Username' => $username
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return '';
    }

    public function resetPassword(string $code, string $password, string $username)
    {
        try {
            $this->client->confirmForgotPassword([
                'ClientId' => $this->client_id,
                'ConfirmationCode' => $code,
                'Password' => $password,
                'Username' => $username
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return '';
    }

    public function isAuthenticated()
    {
        return null !== $this->user;
    }

    public function getPoolMetadata()
    {
        $result = $this->client->describeUserPool([
            'UserPoolId' => $this->userpool_id,
        ]);

        return $result->get('UserPool');
    }

    public function getPoolUsers()
    {
        $result = $this->client->listUsers([
            'UserPoolId' => $this->userpool_id,
        ]);

        return $result->get('Users');
    }

    public function getUser()
    {
        return $this->user;
    }

    public function logout()
    {
        if(isset($_COOKIE[$this->COOKIE_NAME])) {
            unset($_COOKIE[$this->COOKIE_NAME]);
            setcookie($this->COOKIE_NAME, '', time() - 3600000);
        }
    }

    private function setAuthenticationCookie(string $accessToken)
    {
        /*
         * Please note that plain-text storage of the access token is insecure and
         * not recommended by AWS. This is only done to keep this example
         * application as easy as possible. Read the AWS docs for more info:
         * http://docs.aws.amazon.com/cognito/latest/developerguide/amazon-cognito-user-pools-using-tokens-with-identity-providers.html
        */
        setcookie($this->COOKIE_NAME, $accessToken, time() + 3600000);
    }

    private function getAuthenticationCookie()
    {
        return isset($_COOKIE[$this->COOKIE_NAME]) ? $_COOKIE[$this->COOKIE_NAME]: '';
    }
}
