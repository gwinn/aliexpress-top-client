<?php
/**
 * PHP version 7.3
 *
 * @category PostproductRedefiningCategoryForecastResponse
 * @package  RetailCrm\Model\Request\AliExpress
 * @author   RetailCRM <integration@retailcrm.ru>
 * @license  http://retailcrm.ru Proprietary
 * @link     http://retailcrm.ru
 * @see      http://help.retailcrm.ru
 */

namespace RetailCrm\Model\Request\AliExpress;

use JMS\Serializer\Annotation as JMS;
use RetailCrm\Model\Request\BaseRequest;
use RetailCrm\Model\Response\AliExpress\PostproductRedefiningCategoryForecastResponse;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PostproductRedefiningCategoryForecastResponse
 *
 * @category PostproductRedefiningCategoryForecastResponse
 * @package  RetailCrm\Model\Request\AliExpress
 * @author   RetailDriver LLC <integration@retailcrm.ru>
 * @license  MIT https://mit-license.org
 * @link     http://retailcrm.ru
 * @see      https://help.retailcrm.ru
 */
class PostproductRedefiningCategoryForecast extends BaseRequest
{
    /**
     * @var string $subject
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("subject")
     * @Assert\LessThanOrEqual(512)
     */
    public $subject;

    /**
     * @var string $locale
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("locale")
     * @Assert\Choice(choices=RetailCrm\Model\Enum\CategoryForecastSupportedLanguages::SUPPORTED_LANGUAGES)
     */
    public $locale;

    /**
     * @inheritDoc
     */
    public function getMethod(): string
    {
        return 'aliexpress.postproduct.redefining.categoryforecast';
    }

    /**
     * @inheritDoc
     */
    public function getExpectedResponse(): string
    {
        return PostproductRedefiningCategoryForecastResponse::class;
    }
}
