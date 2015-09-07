<?php

class NewCompanyForm extends CFormModel
{
    /**
     * Form params
     */
    public  $Fed_ID;
    public  $Company_Name;
    public  $Address1;
    public  $City;
    public  $State;
    public  $ZIP;

    /**
     * Declares the validation rules.
     * The rules state that username and password are required,
     * and password needs to be authenticated.
     */
    public function rules()
    {
        return array (
            array('Fed_ID', 'check_fed_id'),
            array('Company_Name, Address1, City, State, Fed_ID, ZIP', 'required'),
            array('State', 'length', 'max' => '4'),
            array('Address1, City', 'length', 'max' => '45'),
            array('ZIP', 'length', 'max' => '15'),
            array('ZIP', 'numerical'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'Company_Name' => 'Company name',
            'Address1' => 'Address',
            'City' => 'City',
            'Fed_ID' => 'Fed ID',
            'State' => 'State',
            'ZIP' => 'ZIP Code',
        );
    }


    /**
     * Check Fed ID rule
     */
    public function check_fed_id() {
        $company = Companies::model()->find('Company_Fed_ID=:Fed_ID',
            array(':Fed_ID'=>$this->Fed_ID));
        if($company != null) {
            $this->addError('Fed_ID','Company with this Fed ID already exists');
        } else if (!preg_match('/^(\d{2}\-\d{7})|(\d{3}\-\d{2}\-\d{4})$/', $this->Fed_ID)) {
            $this->addError('Fed_ID','Invalid Fed ID, correct formatting: xx-xxxxxxx');
        }
    }
}