<?php namespace korchasa\Telegram\Tests\Structs;

use PHPUnit\Framework\TestCase;
use korchasa\Telegram\Structs\Update;
use korchasa\Telegram\Unstructured;

class UpdateTest extends TestCase
{
    /**
     * @dataProvider constructorProvider
     */
    function testConstructorPositive($json)
    {
        $update = new Update(new Unstructured($json));
        $this->assertNotNull($update);
        $this->assertInternalType('object', $update);
    }

    function constructorProvider()
    {
        $cases = array_fill_keys(glob(__DIR__.'/fixtures/*.json'), null);
        array_walk($cases, function(&$value, $file) {
            $value = [
                file_get_contents($file)
            ];
        });
        return $cases;
    }
}
