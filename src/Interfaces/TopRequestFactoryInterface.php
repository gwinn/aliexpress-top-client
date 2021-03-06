<?php

/**
 * PHP version 7.3
 *
 * @category TopRequestFactoryInterface
 * @package  RetailCrm\Interfaces
 * @author   RetailCRM <integration@retailcrm.ru>
 * @license  MIT https://mit-license.org
 * @link     http://retailcrm.ru
 * @see      http://help.retailcrm.ru
 */

namespace RetailCrm\Interfaces;

use Psr\Http\Message\RequestInterface;
use RetailCrm\Model\Request\BaseRequest;

/**
 * Interface TopRequestFactoryInterface
 *
 * @category TopRequestFactoryInterface
 * @package  RetailCrm\Interfaces
 * @author   RetailDriver LLC <integration@retailcrm.ru>
 * @license  MIT https://mit-license.org
 * @link     http://retailcrm.ru
 * @see      https://help.retailcrm.ru
 */
interface TopRequestFactoryInterface
{
    /**
     * @param \RetailCrm\Model\Request\BaseRequest         $request
     * @param \RetailCrm\Interfaces\AppDataInterface       $appData
     *
     * @return RequestInterface
     * @throws \RetailCrm\Component\Exception\FactoryException
     * @throws \RetailCrm\Component\Exception\ValidationException
     */
    public function fromModel(
        BaseRequest $request,
        AppDataInterface $appData
    ): RequestInterface;

    /**
     * @param \RetailCrm\Model\Request\BaseRequest $request
     *
     * @return array
     */
    public function getRequestArray(BaseRequest $request): array;
}
