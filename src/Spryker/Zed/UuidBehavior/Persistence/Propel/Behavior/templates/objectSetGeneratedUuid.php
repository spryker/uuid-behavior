/**
* @return void
*/
protected function setGeneratedUuid()
{
    $uuidGenerateUtilService = $this->getUuidGeneratorService();
    $name = <?php echo $name; ?>;
    $uuid = $uuidGenerateUtilService->generateUuid5($name);
    $this->setUuid($uuid);
}
