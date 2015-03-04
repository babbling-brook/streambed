<?php

class SiteAccessTest extends CDbTestCase
{
    public $fixtures=array(
        'site' => ':site',
        'site_access' => ':site_access',
        'user' => ':user',
    );

    public function testTableName()
    {
        $sa = new SiteAccess;
        $this->assertEquals("{{site_access}}", $sa->tableName());
    }


    public function testRelations()
    {
        $sa = new SiteAccess;
        $rel = $sa->relations();
        $this->assertArrayHasKey('site', $rel);
        $this->assertArrayHasKey('user', $rel);
        $site = $rel['site'];
        $this->assertContains('CBelongsToRelation', $site);
        $this->assertContains('Site', $site);
        $this->assertContains('site_id', $site);
        $this->assertArrayHasKey('joinType', $site);
        $this->assertEquals($site['joinType'], 'INNER JOIN');
        $user = $rel['user'];
        $this->assertContains('CBelongsToRelation', $user);
        $this->assertContains('User', $user);
        $this->assertContains('user_id', $user);
        $this->assertArrayHasKey('joinType', $user);
        $this->assertEquals($user['joinType'], 'INNER JOIN');
    }

}
    
?>
