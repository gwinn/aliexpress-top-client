<?php
/**
 * PHP version 7.3
 *
 * @category SolutionOrderGetResponse
 * @package  RetailCrm\Model\Response\AliExpress
 * @author   RetailCRM <integration@retailcrm.ru>
 * @license  http://retailcrm.ru Proprietary
 * @link     http://retailcrm.ru
 * @see      http://help.retailcrm.ru
 */

namespace RetailCrm\Model\Response\AliExpress;

use RetailCrm\Model\Response\BaseResponse;
use JMS\Serializer\Annotation as JMS;

/**
 * Class SolutionOrderGetResponse
 *
 * @category SolutionOrderGetResponse
 * @package  RetailCrm\Model\Response\AliExpress
 * @author   RetailDriver LLC <integration@retailcrm.ru>
 * @license  https://retailcrm.ru Proprietary
 * @link     http://retailcrm.ru
 * @see      https://help.retailcrm.ru
 */
class SolutionOrderGetResponse extends BaseResponse
{
    /**
     * @var \RetailCrm\Model\Response\AliExpress\Data\SolutionOrderGetResponseData $responseData
     *
     * @JMS\Type("RetailCrm\Model\Response\AliExpress\Data\SolutionOrderGetResponseData")
     * @JMS\SerializedName("aliexpress_solution_order_get_response")
     */
    public $responseData;
}
