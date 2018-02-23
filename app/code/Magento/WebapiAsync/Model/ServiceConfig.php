<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\WebapiAsync\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Webapi\Model\Cache\Type\Webapi as WebapiCache;
use Magento\WebapiAsync\Model\ServiceConfig\Converter;
use Magento\WebapiAsync\Model\ServiceConfig\Reader;

/**
 * This class gives access to consolidated web API configuration from <Module_Name>/etc/webapi_async.xml files.
 *
 * @api
 */
class ServiceConfig
{
    const CACHE_ID = 'webapi_async_config';

    /**
     * @var WebapiCache
     */
    private $cache;

    /**
     * @var Reader
     */
    private $configReader;

    /**
     * @var array
     */
    private $services;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Initialize dependencies.
     *
     * @param WebapiCache $cache
     * @param Reader $configReader
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        WebapiCache $cache,
        Reader $configReader,
        SerializerInterface $serializer = null
    ) {
        $this->cache = $cache;
        $this->configReader = $configReader;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
    }

    /**
     * Return services loaded from cache if enabled or from files merged previously
     *
     * @return array
     */
    public function getServices()
    {
        if (null === $this->services) {
            $services = $this->cache->load(self::CACHE_ID);
            if ($services && is_string($services)) {
                $this->services = $this->serializer->unserialize($services);
            } else {
                $this->services = $this->configReader->read()[Converter::KEY_SERVICES];
                $this->cache->save($this->serializer->serialize($this->services), self::CACHE_ID);
            }
        }
        return $this->services;
    }
}
