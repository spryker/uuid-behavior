/**
* @return \Spryker\Service\UtilUuidGenerator\UtilUuidGeneratorServiceInterface
*/
protected function getUuidGeneratorService()
{
    if (static::$_uuidGeneratorService === null) {
        static::$_uuidGeneratorService = \Spryker\Zed\Kernel\Locator::getInstance()->utilUuidGenerator()->service();
    }

    return static::$_uuidGeneratorService;
}
