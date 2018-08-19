
/**
 * @return void
 */
protected function updateUuidBeforeUpdate()
{
    if (empty($this->getUuid())) {
        $this->setGeneratedUuid();
    }
}
