<?php declare(strict_types=1);
/**
 * @author Nicolas CARPi <nico-git@deltablot.email>
 * @copyright 2012 Nicolas CARPi
 * @see https://www.elabftw.net Official website
 * @license AGPL-3.0
 * @package elabftw
 */

namespace Elabftw\Models;

use Elabftw\Enums\Action;
use Elabftw\Exceptions\ImproperActionException;

class ApiKeysTest extends \PHPUnit\Framework\TestCase
{
    private ApiKeys $ApiKeys;

    protected function setUp(): void
    {
        $this->ApiKeys = new ApiKeys(new Users(1, 1));
    }

    public function testCreate(): void
    {
        $this->assertIsInt($this->ApiKeys->postAction(Action::Create, array('name' => 'test key', 'canwrite' => 1)));
    }

    public function testPatch(): void
    {
        $this->expectException(ImproperActionException::class);
        $this->ApiKeys->patch(Action::Update, array());
        $this->expectException(ImproperActionException::class);
        $this->ApiKeys->patch(Action::Archive, array());
    }

    public function testReadOne(): void
    {
        $this->assertIsArray($this->ApiKeys->readOne());
    }

    public function testGetPage(): void
    {
        $this->assertIsString($this->ApiKeys->getPage());
    }

    public function testCreateKnown(): void
    {
        $this->ApiKeys->createKnown('phpunit');
        $this->assertIsArray($this->ApiKeys->readFromApiKey('phpunit'));
        $this->expectException(ImproperActionException::class);
        $this->ApiKeys->readFromApiKey('unknown key');
    }

    public function testReadAll(): void
    {
        $res = $this->ApiKeys->readAll();
        $this->assertIsArray($res);
        $this->assertSame('test key', $res[1]['name']);
        $this->assertSame(1, $res[1]['can_write']);
    }

    public function testDestroy(): void
    {
        $this->ApiKeys->setId(2);
        $this->assertTrue($this->ApiKeys->destroy());
    }
}
