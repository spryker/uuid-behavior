/**
* @return void
*/
protected function setGeneratedKey()
{
    $uuidGenerateUtilService = $this->getUuidGeneratorService();
    $name = <?php echo $name; ?>;
    $uuid = $uuidGenerateUtilService->generateUuid5($name);
    $this->setUuid($uuid);
}
