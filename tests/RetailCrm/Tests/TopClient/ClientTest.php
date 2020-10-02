<?php

/**
 * PHP version 7.3
 *
 * @category ClientTest
 * @package  RetailCrm\Tests\TopClient
 * @author   RetailCRM <integration@retailcrm.ru>
 * @license  MIT
 * @link     http://retailcrm.ru
 * @see      http://help.retailcrm.ru
 */
namespace RetailCrm\Tests\TopClient;

use Http\Message\RequestMatcher\CallbackRequestMatcher;
use Psr\Http\Message\RequestInterface;
use RetailCrm\Builder\ClientBuilder;
use RetailCrm\Component\AppData;
use RetailCrm\Component\Constants;
use RetailCrm\Component\Exception\ValidationException;
use RetailCrm\Model\Entity\CategoryInfo;
use RetailCrm\Model\Enum\FeedOperationTypes;
use RetailCrm\Model\Enum\FeedStatuses;
use RetailCrm\Model\Request\AliExpress\Data\SingleItemRequestDto;
use RetailCrm\Model\Request\AliExpress\PostproductRedefiningCategoryForecast;
use RetailCrm\Model\Request\AliExpress\SolutionFeedListGet;
use RetailCrm\Model\Request\AliExpress\SolutionFeedQuery;
use RetailCrm\Model\Request\AliExpress\SolutionFeedSubmit;
use RetailCrm\Model\Request\AliExpress\SolutionSellerCategoryTreeQuery;
use RetailCrm\Model\Request\Taobao\HttpDnsGetRequest;
use RetailCrm\Model\Response\AliExpress\Data\SolutionFeedSubmitResponseData;
use RetailCrm\Model\Response\AliExpress\Data\SolutionSellerCategoryTreeQueryResponseData;
use RetailCrm\Model\Response\AliExpress\Data\SolutionSellerCategoryTreeQueryResponseDataChildrenCategoryList;
use RetailCrm\Model\Response\AliExpress\PostproductRedefiningCategoryForecastResponse;
use RetailCrm\Model\Response\AliExpress\SolutionFeedListGetResponse;
use RetailCrm\Model\Response\AliExpress\SolutionFeedSubmitResponse;
use RetailCrm\Model\Response\AliExpress\SolutionSellerCategoryTreeQueryResponse;
use RetailCrm\Model\Response\ErrorResponseBody;
use RetailCrm\Model\Response\Taobao\HttpDnsGetResponse;
use RetailCrm\Test\FakeDataRequestDto;
use RetailCrm\Test\TestCase;
use RetailCrm\Test\RequestMatcher;

/**
 * Class ClientTest
 *
 * @category ClientTest
 * @package  RetailCrm\Tests\TopClient
 * @author   RetailDriver LLC <integration@retailcrm.ru>
 * @license  MIT
 * @link     http://retailcrm.ru
 * @see      https://help.retailcrm.ru
 */
class ClientTest extends TestCase
{
    public function testClientRequestException()
    {
        $errorBody = new ErrorResponseBody();
        $errorBody->code = 999;
        $errorBody->msg = 'Mocked error';
        $errorBody->subCode = 'subcode';
        $errorBody->requestId = '1';
        $errorResponse = new HttpDnsGetResponse();
        $errorResponse->errorResponse = $errorBody;

        $mockClient = self::getMockClient();
        $mockClient->on(new CallbackRequestMatcher(function (RequestInterface $request) {
            return true;
        }), $this->responseJson(400, $errorResponse));

        $client = ClientBuilder::create()
            ->setContainer($this->getContainer($mockClient))
            ->setAppData($this->getEnvAppData())
            ->build();

        $this->expectExceptionMessage($errorBody->msg);

        $client->sendRequest(new HttpDnsGetRequest());
    }

    public function testClientRequestXmlUnsupported()
    {
        $client = ClientBuilder::create()
            ->setContainer($this->getContainer(self::getMockClient()))
            ->setAppData($this->getEnvAppData())
            ->build();

        $request = new HttpDnsGetRequest();
        $request->format = 'xml';

        $this->expectExceptionMessage('Client only supports JSON mode, got `xml` mode');
        $client->sendRequest($request);
    }

    public function testClientAliexpressSolutionSellerCategoryTreeQueryResponse()
    {
        $json = <<<'EOF'
{
    "aliexpress_solution_seller_category_tree_query_response":{
        "children_category_list":{
            "category_info":[
                {
                    "children_category_id":5090301,
                    "is_leaf_category":true,
                    "level":2,
                    "multi_language_names":"{   \"de\": \"Mobiltelefon\",   \"ru\": \"Мобильные телефоны\",   \"pt\": \"Telefonia\",   \"in\": \"Ponsel\",   \"en\": \"Mobile Phones\",   \"it\": \"Telefoni cellulari\",   \"fr\": \"Smartphones\",   \"es\": \"Smartphones\",   \"tr\": \"Cep Telefonu\",   \"nl\": \"Mobiele telefoons\" }"
                }
            ]
        },
        "is_success":true
    }
}
EOF;
        $expectedLangs = [
            'de' => 'Mobiltelefon',
            'ru' => 'Мобильные телефоны',
            'pt' => 'Telefonia',
            'in' => 'Ponsel',
            'en' => 'Mobile Phones',
            'it' => 'Telefoni cellulari',
            'fr' => 'Smartphones',
            'es' => 'Smartphones',
            'tr' => 'Cep Telefonu',
            'nl' => 'Mobiele telefoons'
        ];

        $mock = self::getMockClient();
        $mock->on(
            RequestMatcher::createMatcher('api.taobao.com')
                ->setPath('/router/rest')
                ->setOptionalQueryParams([
                    'app_key' => self::getEnvAppKey(),
                    'method' => 'aliexpress.solution.seller.category.tree.query',
                    'category_id' => '5090300',
                    'filter_no_permission' => 1,
                    'session' => self::getEnvToken()
                ]),
            $this->responseJson(200, $json)
        );
        $client = ClientBuilder::create()
            ->setContainer($this->getContainer($mock))
            ->setAppData($this->getEnvAppData())
            ->setAuthenticator($this->getEnvTokenAuthenticator())
            ->build();
        $request = new SolutionSellerCategoryTreeQuery();

        $request->categoryId = 5090300;
        $request->filterNoPermission = true;

        /** @var SolutionSellerCategoryTreeQueryResponse $response */
        $result = $client->sendAuthenticatedRequest($request);

        self::assertInstanceOf(SolutionSellerCategoryTreeQueryResponseData::class, $result->responseData);
        self::assertInstanceOf(
            SolutionSellerCategoryTreeQueryResponseDataChildrenCategoryList::class,
            $result->responseData->childrenCategoryList
        );
        self::assertIsArray($result->responseData->childrenCategoryList->categoryInfo);
        self::assertCount(1, $result->responseData->childrenCategoryList->categoryInfo);

        $info = $result->responseData->childrenCategoryList->categoryInfo[0];

        self::assertInstanceOf(CategoryInfo::class, $info);
        self::assertEquals(5090301, $info->childrenCategoryId);
        self::assertTrue($info->isLeafCategory);
        self::assertEquals(2, $info->level);
        self::assertIsArray($info->multiLanguageNames);

        foreach ($expectedLangs as $lang => $value) {
            self::assertArrayHasKey($lang, $info->multiLanguageNames);
            self::assertEquals($value, $info->multiLanguageNames[$lang]);
        }
    }

    public function testClientAliexpressPostproductRedefiningCategoryForecastEmpty()
    {
        $json = <<<'EOF'
{
    "aliexpress_postproduct_redefining_categoryforecast_response":{
        "result":{
            "error_message":"The result of dii is empty. It should have a correct JSON format data return.",
            "category_suitability_list":{
                "json":[
                    "N\/A"
                ]
            },
            "time_stamp":"20181101111211",
            "error_code":24000011,
            "success":true
        }
    }
}
EOF;
        $mock = self::getMockClient();
        $mock->on(
            RequestMatcher::createMatcher('api.taobao.com')
                ->setPath('/router/rest')
                ->setOptionalQueryParams([
                    'app_key' => self::getEnvAppKey(),
                    'method' => 'aliexpress.postproduct.redefining.categoryforecast',
                    'session' => self::getEnvToken()
                ]),
            $this->responseJson(200, $json)
        );
        $client = ClientBuilder::create()
            ->setContainer($this->getContainer($mock))
            ->setAppData($this->getEnvAppData())
            ->setAuthenticator($this->getEnvTokenAuthenticator())
            ->build();

        $request = new PostproductRedefiningCategoryForecast();
        $request->subject = 'man t-shirt';
        $request->locale = 'en';

        /** @var PostproductRedefiningCategoryForecastResponse $response */
        $response = $client->sendAuthenticatedRequest($request);

        self::assertInstanceOf(PostproductRedefiningCategoryForecastResponse::class, $response);
        self::assertEquals(
            "The result of dii is empty. It should have a correct JSON format data return.",
            $response->responseData->result->errorMessage
        );
        self::assertNull($response->responseData->result->categorySuitabilityList->json);
        self::assertEquals('20181101111211', $response->responseData->result->timeStamp);
        self::assertEquals('24000011', $response->responseData->result->errorCode);
        self::assertTrue($response->responseData->result->success);
    }

    public function testClientAliexpressPostproductRedefiningCategoryForecast()
    {
        $json = <<<'EOF'
{
  "aliexpress_postproduct_redefining_categoryforecast_response": {
    "result": {
      "category_suitability_list": {
        "json": [
          "{\"score\":0.696,\"suitabilityRank\":1,\"categoryId\":200000346}"
        ]
      },
      "success": true,
      "time_stamp": "2019-07-15 13:49:58"
    },
    "request_id": "10ixzzbmna198"
  }
}
EOF;
        $mock = self::getMockClient();
        $mock->on(
            RequestMatcher::createMatcher('api.taobao.com')
                ->setPath('/router/rest')
                ->setOptionalQueryParams([
                    'app_key' => self::getEnvAppKey(),
                    'method' => 'aliexpress.postproduct.redefining.categoryforecast',
                    'session' => self::getEnvToken()
                ]),
            $this->responseJson(200, $json)
        );
        $client = ClientBuilder::create()
            ->setContainer($this->getContainer($mock))
            ->setAppData($this->getEnvAppData())
            ->setAuthenticator($this->getEnvTokenAuthenticator())
            ->build();

        $request = new PostproductRedefiningCategoryForecast();
        $request->subject = 'man t-shirt';
        $request->locale = 'en';

        /** @var PostproductRedefiningCategoryForecastResponse $response */
        $response = $client->sendAuthenticatedRequest($request);
        $items = $response->responseData->result->categorySuitabilityList->json;

        self::assertCount(1, $items);

        $item = $response->responseData->result->categorySuitabilityList->json[0];

        self::assertEquals(0.696, $item->score);
        self::assertEquals(1, $item->suitabilityRank);
        self::assertEquals(200000346, $item->categoryId);
    }

    public function testClientAliexpressSolutionFeedSubmit()
    {
        $json = <<<'EOF'
{
    "aliexpress_solution_feed_submit_response":{
        "job_id":200000000060024475
    }
}
EOF;
        $mock = self::getMockClient();
        $mock->on(
            RequestMatcher::createMatcher('api.taobao.com')
                ->setPath('/router/rest')
                ->setOptionalQueryParams([
                    'app_key' => self::getEnvAppKey(),
                    'method' => 'aliexpress.solution.feed.submit',
                    'session' => self::getEnvToken()
                ]),
            $this->responseJson(200, $json)
        );
        $client = ClientBuilder::create()
            ->setContainer($this->getContainer($mock))
            ->setAppData($this->getEnvAppData())
            ->setAuthenticator($this->getEnvTokenAuthenticator())
            ->build();
        $dto = new FakeDataRequestDto();
        $item = new SingleItemRequestDto();
        $request = new SolutionFeedSubmit();

        $dto->code = 'code';
        $item->itemContent = $dto;
        $item->itemContentId = 'A00000000Y1';
        $request->operationType = FeedOperationTypes::PRODUCT_PRICES_UPDATE;
        $request->itemList = [$item];

        $response = $client->sendAuthenticatedRequest($request);

        self::assertInstanceOf(SolutionFeedSubmitResponseData::class, $response->responseData);
        self::assertEquals(200000000060024475, $response->responseData->jobId);
    }

    public function testClientAliexpressSolutionFeedQuery()
    {
        $json = <<<'EOF'
{
    "aliexpress_solution_feed_query_response":{
        "job_id":200000000060054475,
        "success_item_count":1,
        "result_list":{
            "single_item_response_dto":[
                {
                    "item_execution_result":"{\"productId\":33030372006,\"success\":true}",
                    "item_content_id":"A00000000Y1"
                }
            ]
        },
        "total_item_count":1
    }
}
EOF;
        $mock = self::getMockClient();
        $mock->on(
            RequestMatcher::createMatcher('api.taobao.com')
                ->setPath('/router/rest')
                ->setOptionalQueryParams([
                    'app_key' => self::getEnvAppKey(),
                    'method' => 'aliexpress.solution.feed.query',
                    'session' => self::getEnvToken()
                ]),
            $this->responseJson(200, $json)
        );
        $client = ClientBuilder::create()
            ->setContainer($this->getContainer($mock))
            ->setAppData($this->getEnvAppData())
            ->setAuthenticator($this->getEnvTokenAuthenticator())
            ->build();
        $request = new SolutionFeedQuery();
        $request->jobId = 200000000060054475;

        /** @var \RetailCrm\Model\Response\AliExpress\SolutionFeedQueryResponse $response */
        $response = $client->sendAuthenticatedRequest($request);

        self::assertEquals(200000000060054475, $response->responseData->jobId);
        self::assertEquals(1, $response->responseData->successItemCount);
        self::assertNotNull($response->responseData->resultList);
        self::assertNotNull($response->responseData->resultList->singleItemResponseDto);
        self::assertCount(1, $response->responseData->resultList->singleItemResponseDto);

        $item = $response->responseData->resultList->singleItemResponseDto[0];

        self::assertEquals("A00000000Y1", $item->itemContentId);
        self::assertNotNull($item->itemExecutionResult);
        self::assertTrue($item->itemExecutionResult->success);
        self::assertEquals(33030372006, $item->itemExecutionResult->productId);
    }

    public function testAliexpressSolutionFeedListGet()
    {
        $json = <<<'EOF'
{
    "aliexpress_solution_feed_list_get_response":{
        "current_page":3,
        "job_list":{
            "batch_operation_job_dto":[
                {
                    "status":"PROCESSING",
                    "operation_type":"PRODUCT_CREATE",
                    "job_id":2000000000123456
                }
            ]
        },
        "page_size":20,
        "total_count":300,
        "total_page":15
    }
}
EOF;
        $mock = self::getMockClient();
        $mock->on(
            RequestMatcher::createMatcher('api.taobao.com')
                ->setPath('/router/rest')
                ->setOptionalQueryParams([
                    'app_key' => self::getEnvAppKey(),
                    'method' => 'aliexpress.solution.feed.list.get',
                    'session' => self::getEnvToken()
                ]),
            $this->responseJson(200, $json)
        );
        $client = ClientBuilder::create()
            ->setContainer($this->getContainer($mock))
            ->setAppData($this->getEnvAppData())
            ->setAuthenticator($this->getEnvTokenAuthenticator())
            ->build();
        /** @var SolutionFeedListGetResponse $response */
        $response = $client->sendAuthenticatedRequest(new SolutionFeedListGet());

        self::assertEquals(3, $response->responseData->currentPage);
        self::assertEquals(20, $response->responseData->pageSize);
        self::assertEquals(300, $response->responseData->totalCount);
        self::assertEquals(15, $response->responseData->totalPage);
        self::assertNotNull($response->responseData->jobList);
        self::assertCount(1, $response->responseData->jobList->batchOperationJobDto);

        $item = $response->responseData->jobList->batchOperationJobDto[0];

        self::assertEquals(FeedStatuses::PROCESSING, $item->status);
        self::assertEquals(FeedOperationTypes::PRODUCT_CREATE, $item->operationType);
        self::assertEquals(2000000000123456, $item->jobId);
    }
}
