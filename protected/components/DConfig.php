<?php

class DConfig extends CApplicationComponent
{
    protected $data = array();

    public function init()
    {
        $items = Config::model()->findAll();
        foreach ($items as $item){
            if ($item->Param)
                $this->data[$item->Param] = $item->Value;
        }
        parent::init();
    }

    public function get($key)
    {
        if (array_key_exists($key, $this->data)){
            return $this->data[$key];
        } else {
            throw new CException('Undefined parameter '.$key);
        }
    }
}