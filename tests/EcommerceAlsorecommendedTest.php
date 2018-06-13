<?php
class EcommerceAlsorecommendedTest extends SapphireTest
{
    protected $usesDatabase = false;

    protected $requiredExtensions = array();

    public function testMyMethod()
    {
        $exitStatus = shell_exec('php framework/cli-script.php dev/build flush=all  > dev/null; echo $?');
        $exitStatus = intval(trim($exitStatus));
        $this->assertEquals(0, $exitStatus);
    }
}
