<?php

class SiteTest extends CDbTestCase
{
    public $fixtures=array(
        'site' => ':site',
        'site_access' => ':site_access',
        'user' => ':user',
    );

    public function testTableName()
    {
        $sa = new Site;
        $this->assertEquals("{{site}}", $sa->tableName());
    }

    public function testRelations()
    {
        $site = new Site;
        $rel = $site->relations();
        $this->assertArrayHasKey('site_access', $rel);
        $site_access = $rel['site_access'];
        $this->assertContains('CHasManyRelation', $site_access);
        $this->assertContains('SiteAccess', $site_access);
        $this->assertContains('site_id', $site_access);
    }

    public function testRules()
    {
        $site = new Site;
        $rules = $site->rules();
        $this->assertContains('domain', $rules[0]);
        $this->assertContains('required', $rules[0]);
    }

    public function testAttributeLabels()
    {
        $site = new Site;
        $labels = $site->attributeLabels();
        $this->assertArrayHasKey('domain', $labels);
        $this->assertEquals($labels['domain'], 'Domain');
    }

}

?>
