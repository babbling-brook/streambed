<?php

class ProfileTest extends CDbTestCase
{
    public $fixtures=array(
        'profile' => ':profile',
        'user' => ':user',
    );

    public function testTableName()
    {
        $profile = new Profile;
        $this->assertEquals("{{profile}}", $profile->tableName());
    }

    public function testRules()
    {
        $profile = new Profile;
        $rules = $profile->rules();
        $this->assertContains('name', $rules[0]);
        $this->assertContains('length', $rules[0]);
        $this->assertArrayHasKey('max', $rules[0]);
        $this->assertEquals($rules[0]['max'], 64);
    }

    public function testAttributeLabels()
    {
        $profile = new Profile;
        $labels = $profile->attributeLabels();
        $this->assertArrayHasKey('name', $labels);
        $this->assertEquals($labels['name'], 'Name');
    }

}

?>
