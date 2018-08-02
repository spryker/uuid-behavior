/**
* @return void
*/
protected function setGeneratedUuid()
{
    $uuidGenerateUtilService = $this->getUuidGeneratorService();
    $name = <?php echo $keyStatement; ?>;
    $uuid = $uuidGenerateUtilService->generateUuid5FromObjectId($name);
    $this->setUuid($uuid);
}
