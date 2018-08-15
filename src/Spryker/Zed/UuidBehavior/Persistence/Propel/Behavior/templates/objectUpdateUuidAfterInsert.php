/**
* @return void
*/
protected function updateUuidAfterInsert()
{
    $this->doSave(Propel::getConnection());
}