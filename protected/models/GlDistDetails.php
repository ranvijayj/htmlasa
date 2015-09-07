<?php

/**
 * This is the model class for table "gl_dist_details".
 *
 * The followings are the available columns in table 'gl_dist_details':
 * @property string $GL_Dist_Detail_ID
 * @property string $AP_ID
 * @property string $GL_Dist_Detail_COA_Acct_Number
 * @property string $GL_Dist_Detail_Desc
 * @property string $GL_Dist_Detail_Amt
 * @property string $Short_Hand
 */
class GlDistDetails extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'gl_dist_details';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('AP_ID', 'required'),
			array('AP_ID', 'length', 'max'=>10),
			array('GL_Dist_Detail_COA_Acct_Number', 'length', 'max'=>63),
			array('GL_Dist_Detail_Desc', 'length', 'max'=>125),
			array('GL_Dist_Detail_Amt', 'length', 'max'=>13),
			array('Short_Hand', 'length', 'max'=>63),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('GL_Dist_Detail_ID, AP_ID, GL_Dist_Detail_COA_Acct_Number, GL_Dist_Detail_Desc, GL_Dist_Detail_Amt, Short_Hand', 'safe', 'on'=>'search'),
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
			'GL_Dist_Detail_ID' => 'Gl Dist Detail',
			'AP_ID' => 'Ap',
			'GL_Dist_Detail_COA_Acct_Number' => 'Gl Dist Detail Coa Acct Number',
			'GL_Dist_Detail_Desc' => 'Gl Dist Detail Desc',
			'GL_Dist_Detail_Amt' => 'Gl Dist Detail Amt',
			'Short_Hand' => 'Gl Dist Detail Coa Short Hand',
		);
	}

    /**
     * On save event
     * @return bool
     */
    protected function beforeSave () {
        if (isset($this->GL_Dist_Detail_Amt) && ($this->GL_Dist_Detail_Amt == '' || $this->GL_Dist_Detail_Amt == 0)) {
            $this->GL_Dist_Detail_Amt = null;
        }
        return parent::beforeSave();
    }

    /**
     * Save AP Dists
     * @param $apId
     * @param $distsToSave
     */
    public static function saveAPDists($apId, $distsToSave)
    {
        GlDistDetails::model()->deleteAllByAttributes(array(
            'AP_ID' => $apId,
        ));
        $error_array = array();
        $coaStructure = Coa::getProjectCoaStructure(Yii::app()->user->projectID);

        foreach ($distsToSave as $distToSave) {
            $newDist = new GlDistDetails();
            $newDist->AP_ID = $apId;

            if ($distToSave['GL_Dist_Detail_COA_Acct_Number'] == '') {
                $newDist->GL_Dist_Detail_COA_Acct_Number = null;
            } else {

                    //full functionality not available from ver 13210
                    $newDist->Short_Hand = $distToSave['GL_Dist_Detail_COA_Acct_Number'];

                    $constructed_value = Coa::constructCoaNumber(Yii::app()->user->projectID, $distToSave['GL_Dist_Detail_COA_Acct_Number']);
                    //$newDist->GL_Dist_Detail_COA_Acct_Number =$distToSave['GL_Dist_Detail_COA_Acct_Number'];
                    $newDist->GL_Dist_Detail_COA_Acct_Number =$constructed_value;
                    $coa=Coa::model()->findByAttributes(array('COA_Acct_Number'=>$newDist->GL_Dist_Detail_COA_Acct_Number));
                    if ($coa) {$coa->COA_Used = 1;}


            }

            if ($distToSave['GL_Dist_Detail_Desc'] == '') {
                $newDist->GL_Dist_Detail_Desc = '-';
            } else {
                $newDist->GL_Dist_Detail_Desc = Helper::shortenString($distToSave['GL_Dist_Detail_Desc'],125);
            }

            if ($distToSave['GL_Dist_Detail_Amt'] == '') {
                $newDist->GL_Dist_Detail_Amt = 0;
            } else {
                $newDist->GL_Dist_Detail_Amt = $distToSave['GL_Dist_Detail_Amt'];
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

        return $error_array;
    }

    /**
     * Prepare AP Dists Models
     * @param $apId
     * @param $distsToSave
     */
    public static function prepareAPDistsModelsArray($apId, $distsToSave)
    {

        $result = array();

        $coaStructure = Coa::getProjectCoaStructure(Yii::app()->user->projectID);

        foreach ($distsToSave as $distToSave) {
            $newDist = new GlDistDetails();
            $newDist->AP_ID = $apId;

            if ($distToSave['GL_Dist_Detail_COA_Acct_Number'] == '') {
                $newDist->GL_Dist_Detail_COA_Acct_Number = null;
            } else {


                    //full functionality not available from ver 13210
                    $newDist->Short_Hand = $distToSave['GL_Dist_Detail_COA_Acct_Number'];

                    $constructed_value = Coa::constructCoaNumber(Yii::app()->user->projectID, $distToSave['GL_Dist_Detail_COA_Acct_Number']);
                    //$newDist->GL_Dist_Detail_COA_Acct_Number =$distToSave['GL_Dist_Detail_COA_Acct_Number'];
                    $newDist->GL_Dist_Detail_COA_Acct_Number =$constructed_value;
                    $coa=Coa::model()->findByAttributes(array('COA_Acct_Number'=>$newDist->GL_Dist_Detail_COA_Acct_Number));
                    if ($coa) {$coa->COA_Used = 1;}


            }

            if ($distToSave['GL_Dist_Detail_Desc'] == '') {
                $newDist->GL_Dist_Detail_Desc = '-';
            } else {
                $newDist->GL_Dist_Detail_Desc = $distToSave['GL_Dist_Detail_Desc'];
            }

            if ($distToSave['GL_Dist_Detail_Amt'] == '') {
                $newDist->GL_Dist_Detail_Amt = 0;
            } else {
                $newDist->GL_Dist_Detail_Amt = $distToSave['GL_Dist_Detail_Amt'];
            }

            array_push($result,$newDist);

        }

        return $result;
    }


    public static function getAPDists($apId)
    {
        $dists= GlDistDetails::model()->findAllByAttributes(array(
            'AP_ID' => $apId,
        ));
        $i=0;
        if($dists){
            foreach ($dists as $dist) {

                $return_array[$i]['GL_Dist_Detail_COA_Acct_Number']=$dist->GL_Dist_Detail_COA_Acct_Number;
                $return_array[$i]['Short_Hand']=$dist->Short_Hand;
                $return_array[$i]['GL_Dist_Detail_Desc']=$dist->GL_Dist_Detail_Desc;
                $return_array[$i]['GL_Dist_Detail_Amt']=$dist->GL_Dist_Detail_Amt;

                $i++;

            }
            for($i = count($return_array); $i < 4; $i++) {
                $return_array[$i] = array(
                    'GL_Dist_Detail_COA_Acct_Number' => '',
                    'Short_Hand'=>'',
                    'GL_Dist_Detail_Desc' => '',
                    'GL_Dist_Detail_Amt' => '',
                );

            }
        $empty =false;
        } else {
            for($i = 1; $i <= 4; $i++) {
                $return_array[$i] = array(
                    'GL_Dist_Detail_COA_Acct_Number' => '',
                    'Short_Hand'=>'',
                    'GL_Dist_Detail_Desc' => '',
                    'GL_Dist_Detail_Amt' => '',
                );

            }
        $empty =true;
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

		$criteria->compare('GL_Dist_Detail_ID',$this->GL_Dist_Detail_ID,true);
		$criteria->compare('AP_ID',$this->AP_ID,true);
		$criteria->compare('GL_Dist_Detail_COA_Acct_Number',$this->GL_Dist_Detail_COA_Acct_Number,true);
		$criteria->compare('GL_Dist_Detail_Desc',$this->GL_Dist_Detail_Desc,true);
		$criteria->compare('GL_Dist_Detail_Amt',$this->GL_Dist_Detail_Amt,true);
		$criteria->compare('Short_Hand',$this->Short_Hand,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return GlDistDetails the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public static function prepareDistsToSave($post) {
        // set dists
        $dists = array();
        $distsToSave = array();
        foreach ($post as $key => $dist) {
            $dists[$key + 1] = array(
                'GL_Dist_Detail_COA_Acct_Number' => $dist['GL_Dist_Detail_COA_Acct_Number'],
                'Short_Hand' => $dist['Short_Hand'],
                'GL_Dist_Detail_Amt' => $dist['GL_Dist_Detail_Amt'],
                'GL_Dist_Detail_Desc' => $dist['GL_Dist_Detail_Desc'],
            );
            if ($dist['GL_Dist_Detail_Amt'] != '' || $dist['GL_Dist_Detail_Desc'] != '') {
                array_push($distsToSave,$dist);
            }
        }

        return $distsToSave;
    }
}
