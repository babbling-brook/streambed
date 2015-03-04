<?php

class OtherDomainsHelperTest extends CTestCase
{
    public $fixtures=array(   );
    
    public function testCheckUserExists()
    {
        $user = "http://cobaltcascade.localhost/test";
        $store = new OtherDomainsHelper;
        $result = $store->checkUserExists($user);
        $this->assertTrue($result);
        $not_a_user = "http://cobaltcascade.localhost/notauser";
        $result = $store->checkUserExists($not_a_user);
        $this->assertFalse($result);
    }

    public function testNotifyOfTransaction()
    {
        $user = "test";
        $with = "http://cobaltcascade.localhost/test2";
        $type = "TestTransaction";
        $role = 1;
        $my_value = 5;
        $with_value = 6;
        $origin = "http://cobaltcascade.localhost";
        $guid = "21EC2020-3AEA-1069-A2DD-08002B30309D";
        $store = new OtherDomainsHelper;
        $result = $store->notifyOfTransaction($user, $type, $role, $with, $my_value, $with_value, $origin, $guid);
        $this->assertTrue($result);
        $not_a_user = "http://cobaltcascade.localhost/notauser";
        $result = $store->checkUserExists($not_a_user);
        $this->assertFalse($result);
    }
    
}
?>
