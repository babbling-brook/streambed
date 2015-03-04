<?php

class UserIdentityTest extends CDbTestCase
{
    public $fixtures=array(
        'site' => ':site',
        'site_access' => ':site_access',
        'user' => ':user',
    );

    public function testLoginUser()
    {
        $user_test = $this->user['test_user'];
        $id = new UserIdentity($user_test['username'], 'test');
        $site_test = $this->site['sotresalt3'];
        $id->loginUser($site_test['domain'], '', false, false);
        $this->assertFalse(Yii::app()->user->isGuest);
        $results = SiteAccess::getSiteAccess($user_test['username'], $site_test['domain'], false);
        $this->assertEquals($results->user_id , $user_test['user_id']);
        $this->assertEquals($results->site_id , $site_test['site_id']);
        Yii::app()->user->logout();

        $id = new UserIdentity('Not a user', 'test');
        try{
            $id->loginUser($site_test['domain'], '', false, false);
            $this->fail('An expected Exception has not been raised.');
        } catch (Exception $ex) {}
    }

    public function testAuthenticate()
    {
        $user_test = $this->user['test_user'];
        $id = new UserIdentity($user_test['username'], 'test');
        $this->assertTrue($id->authenticate());
        $this->assertEquals($id->errorCode, UserIdentity::ERROR_NONE);

        $id = new UserIdentity('fakeuser', 'test');
        $this->assertTrue(!$id->authenticate());
        $ff = $id->errorCode;
        $this->assertEquals($id->errorCode, UserIdentity::ERROR_USERNAME_INVALID);

        //failed password
        $user_test = $this->user['test_user'];
        $id = new UserIdentity($user_test['username'], 'wrongpassword');
        $id->authenticate();
        $this->assertEquals($id->errorCode, UserIdentity::ERROR_PASSWORD_INVALID);

    }

    public function testUsernameUnique()
    {
        // New user.
        $id = new UserIdentity('newuser', 'test');
        $id->usernameUnique();
        $this->assertEquals($id->errorCode, UserIdentity::ERROR_UNKNOWN_IDENTITY);    // Will have failed when created

        // Existing user
        $user_test = $this->user['test_user'];
        $id = new UserIdentity($user_test['username'], 'test');
        $id->usernameUnique();
        $this->assertEquals($id->errorCode, UserIdentity::ERROR_USERNAME_INVALID);

        // Username as a reserverd word
        $id = new UserIdentity('site', 'test');
        $id->usernameUnique();
        $this->assertEquals($id->errorCode, UserIdentity::ERROR_USERNAME_INVALID);
    }

    public function testGetId()
    {
        $user_test = $this->user['test_user'];
        $id = new UserIdentity($user_test['username'], 'test');
        $this->assertTrue($id->authenticate());
        $this->assertEquals($id->getId(), '1');
    }

    public function testHashPassword()
    {
        $user_test = $this->user['test_user'];
        $id = new UserIdentity($user_test['username'], 'test');
        $hash = $id->hashPassword($user_test['salt']);
        $this->assertEquals($hash, $user_test['password']);
    }

    public function testSignUp()
    {
        $username = "newuser";
        $password = "test";
        $email = "testemail@example.com";
        $id = new UserIdentity($username, $password);
        $id->signUp($email);
        $user = new UserMulti();
        $this->assertTrue($user->userExists($username));
    }

}

?>
