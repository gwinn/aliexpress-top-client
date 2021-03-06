<?php

/**
 * PHP version 7.3
 *
 * @category TopApiException
 * @package  RetailCrm\Component\Exception
 * @author   RetailCRM <integration@retailcrm.ru>
 * @license  MIT
 * @link     http://retailcrm.ru
 * @see      http://help.retailcrm.ru
 */
namespace RetailCrm\Component\Exception;

use Exception;
use RetailCrm\Model\Response\ErrorResponseBody;
use Throwable;

/**
 * Class TopApiException
 *
 * @category TopApiException
 * @package  RetailCrm\Component\Exception
 * @author   RetailDriver LLC <integration@retailcrm.ru>
 * @license  MIT
 * @link     http://retailcrm.ru
 * @see      https://help.retailcrm.ru
 */
class TopApiException extends Exception
{
    /**
     * @var ErrorResponseBody $error
     */
    private $error;

    /**
     * TopApiException constructor.
     *
     * @param \RetailCrm\Model\Response\ErrorResponseBody $responseBody
     * @param \Throwable|null                             $previous
     */
    public function __construct(ErrorResponseBody $responseBody, Throwable $previous = null)
    {
        parent::__construct($responseBody->msg, $responseBody->code, $previous);

        $this->error = $responseBody;
    }

    /**
     * @return \RetailCrm\Model\Response\ErrorResponseBody
     */
    public function getError(): ErrorResponseBody
    {
        return $this->error;
    }
}
