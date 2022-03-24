
/**
 * @param ConnectionInterface $con
 *
 * @return void
 */
protected function updateUuidAfterInsert(ConnectionInterface $con = null)
{
    if (!$this->getUuid()) {
        $this->setGeneratedUuid();
        $this->doSave($con);
    }
}
