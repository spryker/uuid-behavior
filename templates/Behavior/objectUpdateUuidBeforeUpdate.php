
/**
 * @return void
 */
protected function updateUuidBeforeUpdate()
{
    if (!$this->getUuid()) {
        $this->setGeneratedUuid();
    }
}
