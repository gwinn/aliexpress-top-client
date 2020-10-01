<?php
/**
 * PHP version 7.4
 *
 * @category JsonDeserializationVisitorFactory
 * @package  RetailCrm\Component\JMS\Factory
 * @author   RetailCRM <integration@retailcrm.ru>
 * @license  http://retailcrm.ru Proprietary
 * @link     http://retailcrm.ru
 * @see      http://help.retailcrm.ru
 */

namespace RetailCrm\Component\JMS\Factory;

use RetailCrm\Component\JMS\Visitor\Deserialization\JsonDeserializationVisitor;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\Factory\DeserializationVisitorFactory;

/**
 * Class JsonDeserializationVisitorFactory
 *
 * @category JsonDeserializationVisitorFactory
 * @package  RetailCrm\Component\JMS\Factory
 * @author   RetailDriver LLC <integration@retailcrm.ru>
 * @license  https://retailcrm.ru Proprietary
 * @link     http://retailcrm.ru
 * @see      https://help.retailcrm.ru
 */
class JsonDeserializationVisitorFactory implements DeserializationVisitorFactory
{
    /**
     * @var int
     */
    private $options = 0;

    /**
     * @var int
     */
    private $depth = 512;

    /**
     * @return \JMS\Serializer\Visitor\DeserializationVisitorInterface
     */
    public function getVisitor(): DeserializationVisitorInterface
    {
        return new JsonDeserializationVisitor($this->options, $this->depth);
    }

    /**
     * @param int $options
     *
     * @return $this
     */
    public function setOptions(int $options): self
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @param int $depth
     *
     * @return $this
     */
    public function setDepth(int $depth): self
    {
        $this->depth = $depth;
        return $this;
    }
}
