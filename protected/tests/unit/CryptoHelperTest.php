<?php

class CryptoHelperTest extends CTestCase
{
    public $fixtures=array(   );

    public function testMakeUniqueSecret()
    {
        $crypt = new CryptoHelper;
        $uid = $crypt->makeUniqueSecret();
        $uid2 = $crypt->makeUniqueSecret();

        // Check uid is long enough ( > 20 chars)
        $this->assertGreaterThan(20, strlen($uid));

        //check the uids are different
        $this->assertNotEquals($uid, $uid2);
    }

    public function testcheckGuid()
    {
        $crypt = new CryptoHelper;
        //correct guid
        $guid = "21EC2020-3AEA-1069-A2DD-08002B30309D";
        $result = $crypt->checkGuid($guid);
        $this->assertTrue($result);
        //incorrect guid
        $guid = "21EC202H-3AEA-1069-A2DD-08002B30309D";
        $result = $crypt->checkGuid($guid);
        $this->assertFalse($result);
        //incorrect guid ( has brackets )
        $guid = "{21EC202D-3AEA-1069-A2DD-08002B30309D}";
        $result = $crypt->checkGuid($guid);
        $this->assertFalse($result);
    }
    
}

?>
