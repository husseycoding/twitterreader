<?php
class HusseyCoding_TwitterReader_Model_Config_CoreData extends Mage_Core_Model_Config_Data
{
    public function disableSave()
    {
        $this->_dataSaveAllowed = false;
    }
}