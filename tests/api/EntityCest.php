<?php declare(strict_types=1);
/**
 * @package   Elabftw\Elabftw
 * @author    Nicolas CARPi <nico-git@deltablot.email>
 * @copyright 2012 Nicolas CARPi
 * @license   https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0
 * @see       https://www.elabftw.net Official website
 */

use \Codeception\Util\HttpCode;

class EntityCest
{
    public function _before(ApiTester $I)
    {
        $I->haveHttpHeader('Authorization', 'apiKey4Test');
    }

    public function getAllExpTest(ApiTester $I)
    {
        $I->wantTo('Get all visible experiments');
        $I->sendGET('/experiments');
        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        // now with search query
        $I->sendGET('/experiments/?search=a');
        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
    }

    public function getAllItemsTest(ApiTester $I)
    {
        $I->wantTo('Get all visible items');
        $I->sendGET('/items');
        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
    }

    public function getOneExpTest(ApiTester $I)
    {
        $I->wantTo('Get the first experiment');
        $I->sendGET('/experiments/1');
        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
    }

    public function getOneItemTest(ApiTester $I)
    {
        $I->wantTo('Get the first item');
        $I->sendGET('/items/1');
        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
    }

    public function updateExpTest(ApiTester $I)
    {
        $I->wantTo('Update an experiment');
        $I->sendPOST('/experiments/1', array('title' => 'new title', 'date' => '20191231', 'body' => 'new body', 'metadata' => '{"foo":1}'));
        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(array('result' => 'success'));
    }

    public function addTagTest(ApiTester $I)
    {
        $I->wantTo('Add a tag to an experiment');
        $I->sendPOST('/experiments/1', array('tag' => 'some tag'));
        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(array('result' => 'success'));
    }

    public function addLinkTest(ApiTester $I)
    {
        $I->wantTo('Add a link to an experiment pointing to an item');
        $I->sendPOST('/experiments/1', array('link' => 1));
        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(array('result' => 'success'));

        $I->wantTo('Add a link to an item pointing to an item');
        $I->sendPOST('/items/1', array('link' => 1));
        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(array('result' => 'success'));
    }

    public function createExpTest(ApiTester $I)
    {
        $I->wantTo('Create an experiment');
        $I->sendPOST('/experiments');
        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(array('result' => 'success'));
    }

    public function createItemTest(ApiTester $I)
    {
        $I->wantTo('Create an item');
        $I->sendPOST('/items/1');
        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(array('result' => 'success'));
    }

    public function createItemWrongCategoryTest(ApiTester $I)
    {
        $I->wantTo('Create an item with wrong category');
        $I->sendPOST('/items/2');
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
    }

    public function updateItemTest(ApiTester $I)
    {
        $I->wantTo('Update an item');
        $I->sendPOST('/items/1', array('category' => 2));
        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(array('result' => 'success'));
    }

    public function notFoundTest(ApiTester $I)
    {
        $I->wantTo('Find a non existing resource');
        $I->sendGET('/items/9001');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }
}
