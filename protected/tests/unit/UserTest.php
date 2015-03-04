<?php

class UserTest extends CDbTestCase
{
    public $fixtures=array(
        'profile' => ':profile',
        'site' => ':site',
        'site_access' => ':site_access',
        'user' => ':user',
    );

    public function testTableName()
    {
        $user = new User;
        $this->assertEquals("{{user}}", $user->tableName());
    }

    public function testRelations()
    {
        $user = new User;
        $rel = $user->relations();
        $this->assertArrayHasKey('site_access', $rel);
        $site_access = $rel['site_access'];
        $this->assertContains('CHasManyRelation', $site_access);
        $this->assertContains('SiteAccess', $site_access);
        $this->assertContains('user_id', $site_access);
    }

    public function testRules()
    {
        $user = new User;
        $rules = $user->rules();
        $this->assertContains('username, password, ', $rules[0]);
        $this->assertContains('required', $rules[0]);
        $this->assertContains('username, password, email', $rules[1]);
        $this->assertContains('length', $rules[1]);
        $this->assertContains('128', $rules[1]);
        $this->assertArrayHasKey('max', $rules[1]);
        $this->assertContains('username', $rules[2]);
        $this->assertContains('isUserUnique', $rules[2]);
        $this->assertContains('email', $rules[3]);
        $this->assertContains('CEmailValidator', $rules[3]);
    }

    public function testAttributeLabels()
    {
        $user = new User;
        $labels = $user->attributeLabels();
        $this->assertArrayHasKey('username', $labels);
        $this->assertArrayHasKey('password', $labels);
        $this->assertArrayHasKey('email', $labels);
        $this->assertEquals($labels['username'], 'Username');
        $this->assertEquals($labels['password'], 'Password');
        $this->assertEquals($labels['email'], 'Email');
    }

    public function testIsUserUnique()
    {
        $user = new User;
        $user_test = $this->user['test_user'];
        $user->username = $user_test['username'];
        $user->password = 'test';
        $user->isUserUnique('', '');
        $errors = $user->getErrors();
        $this->assertTrue(!empty($errors));

        $user = new User;
        $user->username = 'doesnotexist';
        $user->password = 'test';
        $user->isUserUnique('', '');
        $errors = $user->getErrors();
        $this->assertTrue(empty($errors));
    }

}
?>
