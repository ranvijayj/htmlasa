<?php

/**
 * This is the model class for table "po_dists".
 *
 * The followings are the available columns in table 'po_dists':
 * @property integer $PO_Dists_ID
 * @property integer $PO_ID
 * @property string $PO_Dists_GL_Code
 * @property double $PO_Dists_Amount
 * @property string $PO_Dists_Description
 */
class PoDists extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'po_dists';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('PO_ID','required'),
			array('PO_ID', 'numerical', 'integerOnly'=>true),
			array('PO_Dists_Amount', 'numerical'),
			array('PO_Dists_Description', 'length', 'max'=>125),
            array('PO_Dists_GL_Code, PO_Dists_GL_Code_Full', 'length', 'max'=>63),
            array('PO_Dists_Amount', 'length', 'max'=>13),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('PO_Dists_ID, PO_ID, PO_Dists_GL_Code, PO_Dists_Amount, PO_Dists_Description, PO_Dists_GL_Code_Full', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'PO_Dists_ID' => 'Po Dists',
			'PO_ID' => 'Po',
			'PO_Dists_GL_Code' => 'Po Dists Gl Code',
			'PO_Dists_Amount' => 'Po Dists Amount',
			'PO_Dists_Description' => 'Po Dists Description',
            'PO_Dists_GL_Code_Full' => 'PO Dists GL Code Full',
		);
	}

    /**
     * On save event
     * @return bool
     */
    protected function beforeSave() {
        if (isset($this->PO_Dists_Amount) && $this->PO_Dists_Amount == '') {
            $this->PO_Dists_Amount = 0;
        }

        return parent::beforeSave();
    }

    /**
     * Save PO Dists
     * @param $poId
     * @param $distsToSave
     */
    public static function savePODists($poId, $distsToSave)
    {
        PoDists::model()->deleteAllByAttributes(array(
            'PO_ID' => $poId,
        ));
        $coaStructure = Coa::getProjectCoaStructure(Yii::app()->user->projectID);

        foreach ($distsToSave as $distToSave) {


            //no more then 125 chars allowed
            $distToSave['PO_Dists_Description']= substr($distToSave['PO_Dists_Description'],0,125);

            $newDist = new PoDists();
            $newDist->PO_ID = $poId;

            if ($distToSave['PO_Dists_GL_Code'] == '') {
                $newDist->PO_Dists_GL_Code = null;
            } else {


                    //full functionality not available from ver 13210
                    $newDist->Short_Hand = $distToSave['PO_Dists_GL_Code'];

                    $constructed_value = Coa::constructCoaNumber(Yii::app()->user->projectID, $distToSave['PO_Dists_GL_Code']);
                    $newDist->PO_Dists_GL_Code =$constructed_value;
                    $coa=Coa::model()->findByAttributes(array('COA_Acct_Number'=>$newDist->PO_Dists_GL_Code));
                    if ($coa) {$coa->COA_Used = 1;}



            }

            if ($distToSave['PO_Dists_Description'] == '') {
                $newDist->PO_Dists_Description = '-';
            } else {
                $newDist->PO_Dists_Description = $distToSave['PO_Dists_Description'];
            }

            if ($distToSave['PO_Dists_Amount'] == '') {
                $newDist->PO_Dists_Amount = 0;
            } else {
                $newDist->PO_Dists_Amount = $distToSave['PO_Dists_Amount'];
            }

            if ($newDist->validate()) {
                $newDist->save();
            } else {$error_array[]='Dist validation error'.$distToSave['GL_Dist_Detail_COA_Acct_Number'];}

            if ($coa) {
                if ($coa->validate()) {
                    $coa->save();
                } else {$error_array[]='Coa validation error'.$distToSave['GL_Dist_Detail_COA_Acct_Number'];}
            }
        }
    }

    /**
     * Generates from array of dists  array of dist's models. Used for generating PO document without saving metadata to database.
     * @param $poId
     * @param $distsToSave
     */
    public static function preparePODistsArray($poId, $distsToSave)
    {
        $resultArray = array();
        $coaStructure = Coa::getProjectCoaStructure(Yii::app()->user->projectID);

        foreach ($distsToSave as $distToSave) {

            $distToSave['PO_Dists_Description']= substr($distToSave['PO_Dists_Description'],0,125);

            $newDist = new PoDists();
            $newDist->PO_ID = $poId;

            if ($distToSave['PO_Dists_GL_Code'] == '') {
                $newDist->PO_Dists_GL_Code = null;
            } else {


                    //full functionality not available from ver 13210
                    $newDist->Short_Hand = $distToSave['PO_Dists_GL_Code'];
                    $constructed_value = Coa::constructCoaNumber(Yii::app()->user->projectID, $distToSave['PO_Dists_GL_Code']);
                    $newDist->PO_Dists_GL_Code =$constructed_value;
                    $coa=Coa::model()->findByAttributes(array('COA_Acct_Number'=>$newDist->PO_Dists_GL_Code));
                    if ($coa) {$coa->COA_Used = 1;}

            }

            if ($distToSave['PO_Dists_Description'] == '') {
                $newDist->PO_Dists_Description = '-';
            } else {
                $newDist->PO_Dists_Description = $distToSave['PO_Dists_Description'];
            }

            if ($distToSave['PO_Dists_Amount'] == '') {
                $newDist->PO_Dists_Amount = 0;
            } else {
                $newDist->PO_Dists_Amount = $distToSave['PO_Dists_Amount'];
            }

            array_push($resultArray,$newDist);

        }
        return $resultArray;
    }


    public static function getPODists($poId)
    {
        $dists= PoDists::model()->findAllByAttributes(array(
            'PO_ID' => $poId,
        ));
        $i=0;
        if($dists){
            foreach ($dists as $dist) {

                $return_array[$i]['PO_Dists_GL_Code']=$dist->PO_Dists_GL_Code;
                $return_array[$i]['Short_Hand']=$dist->Short_Hand;
                $return_array[$i]['PO_Dists_Amount']=$dist->PO_Dists_Amount;
                $return_array[$i]['PO_Dists_Description']=$dist->PO_Dists_Description;

                $i++;

            }

            for($i = count($return_array); $i < 4; $i++) {
                $return_array[$i] = array(
                    'PO_Dists_GL_Code' => '',
                    'Short_Hand'=>'',
                    'PO_Dists_Amount' => '',
                    'PO_Dists_Description' => '',
                );

            }

        $empty=false;
        } else {
            for($i = 1; $i <= 4; $i++) {
                $return_array[$i] = array(
                    'PO_Dists_GL_Code' => '',
                    'Short_Hand'=>'',
                    'PO_Dists_Amount' => '',
                    'PO_Dists_Description' => '',
                );

            }
        $empty=true;
        }
        return array('empty'=>$empty,'dists'=>$return_array);
    }

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('PO_Dists_ID',$this->PO_Dists_ID);
		$criteria->compare('PO_ID',$this->PO_ID);
		$criteria->compare('PO_Dists_GL_Code',$this->PO_Dists_GL_Code,true);
		$criteria->compare('PO_Dists_Amount',$this->PO_Dists_Amount);
		$criteria->compare('PO_Dists_Description',$this->PO_Dists_Description,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return PoDists the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public static function prepareDistsToSave($post) {

        $distsToSave = array();

        $dists = array();
        foreach ($post as $key => $dist) {
            $dists[$key + 1] = array(
                'PO_Dists_GL_Code' => $dist['PO_Dists_GL_Code'],
                'PO_Dists_Amount' => $dist['PO_Dists_Amount'],
                'Short_Hand' => $dist['Short_Hand'],
                'PO_Dists_Description' => $dist['PO_Dists_Description'],
            );
            if ($dist['PO_Dists_GL_Code'] != '' || $dist['PO_Dists_Amount'] != ''
                || $dist['PO_Dists_Description'] != '') {
                $distsToSave[] = $dist;
            }
        }
        return $distsToSave;
    }
}
