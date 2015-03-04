<?php

class LoginFormTest extends CDbTestCase
{
    public $fixtures=array(
        'site' => ':site',
        'site_access' => ':site_access',
        'user' => ':user',
    );

    public function testRules()
    {
        $login_form = new LoginForm;
        $rules = $login_form->rules();
        $this->assertContains('password', $rules[0]);
        $this->assertContains('required', $rules[0]);
        $this->assertArrayHasKey('on', $rules[0]);
        $this->assertEquals($rules[0]['on'], 'site, user, user_permission');

        $this->assertContains('username', $rules[1]);
        $this->assertContains('required', $rules[1]);
        $this->assertArrayHasKey('on', $rules[1]);
        $this->assertEquals($rules[1]['on'], 'site');

        $this->assertContains('remember_me', $rules[2]);
        $this->assertContains('boolean', $rules[2]);
        $this->assertArrayHasKey('on', $rules[2]);
        $this->assertEquals($rules[2]['on'], 'site, user, user_permission');

        $this->assertContains('remember_me_site', $rules[3]);
        $this->assertContains('boolean', $rules[3]);
        $this->assertArrayHasKey('on', $rules[3]);
        $this->assertEquals($rules[3]['on'], 'user_permission, permission');

        $this->assertContains('password', $rules[4]);
        $this->assertContains('authenticate', $rules[4]);
        $this->assertArrayHasKey('skipOnError', $rules[4]);
        $this->assertEquals($rules[4]['skipOnError'], true);
        $this->assertArrayHasKey('on', $rules[4]);
        $this->assertEquals($rules[4]['on'], 'site, user, user_permission');
    }

    public function testAttributeLabels()
    {
        $test_domain = "mydomain.com";
        $login_form = new LoginForm();
        $login_form->setDomain($test_domain);
        $labels = $login_form->attributeLabels();
        $this->assertArrayHasKey('remember_me', $labels);
        $this->assertArrayHasKey('remember_me_site', $labels);
        $this->assertContains($test_domain, $labels['remember_me_site']);
    }

    public function testSetIdentity()
    {
        try
        {
            $user_test = $this->user['test_user'];
            $id = new UserIdentity($user_test['username'], 'test');
            $login_form = new LoginForm;
            $login_form->setIdentity($id);
        } catch (Exception $ex) {
            $this->fail('setIdentity raised an exception. ' . $ex);
        }    
    }

    public function testSetDomain()
    {
        try
        {
            $test_domain = "mydomain.com";
            $login_form = new LoginForm;
            $login_form->setDomain($test_domain);
        } catch (Exception $ex) {
            $this->fail('setDomain raised an exception. ' . $ex);
        }
    }

    public function testAuthenticate()
    {
        // Test authentication
        $login_form = new LoginForm();
        $user_test = $this->user['test_user'];
        $id = new UserIdentity($user_test['username'], 'test');
        $login_form->setIdentity($id);
        $login_form->username = $user_test['username'];
        $login_form->password = 'test';
        $login_form->authenticate('', '');
        $errors = $login_form->getErrors();
        $this->assertTrue(empty($errors));

        // Test a fake username
        $fake_user = 'fakeusername';
        $login_form->username = $fake_user;
        $login_form->password = 'test';
        $id = new UserIdentity($fake_user, 'test');
        $login_form->setIdentity($id);
        $login_form->authenticate('', '');
        $errors = $login_form->getErrors();
        $this->assertTrue(!empty($errors));

        // Test a wrong password
        $fake_pass = "wrongpassword";
        $login_form->username = $user_test['username'];
        $id = new UserIdentity($user_test['username'], $fake_pass);
        $login_form->setIdentity($id);
        $login_form->password = $fake_pass;
        $login_form->authenticate('', '');
        $errors = $login_form->getErrors();
        $this->assertTrue(!empty($errors));
    }

}

?>
