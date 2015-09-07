<?php

/**
 * This is the model class for table "file_cache".
 *
 * The followings are the available columns in table 'file_cache':
 * @property integer $cash_id
 * @property string $fileId
 * @property string $path
 * @property integer $userId
 * @property integer $clientId
 */
class FileCache extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'file_cache';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('file_id, path, user_id, client_id', 'required'),
			array('user_id, client_id', 'numerical', 'integerOnly'=>true),
			array('file_id', 'length', 'max'=>100),
			array('path', 'length', 'max'=>250),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('cash_id, file_id, path, user_id, client_id', 'safe', 'on'=>'search'),
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
			'cash_id' => 'Cash',
			'file_id' => 'File',
			'path' => 'Path',
			'user_id' => 'User',
			'client_id' => 'Client',
		);
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

		$criteria->compare('cash_id',$this->cash_id);
		$criteria->compare('file_id',$this->file_id,true);
		$criteria->compare('path',$this->path,true);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('client_id',$this->client_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return FileCache the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * Adding file to cashe table and to file system (folder /filecache).$fileitentifier - doc_id or full path to file.
     * @param $fileitentifier
     * @return int|string
     */
    public static function addToFileCache($fileitentifier){

        $path = FileModification::createDirectory('filecache');
        $path = FileModification::createDirectory('filecache/'.Yii::app()->user->clientID);

        if (is_file($fileitentifier)) {
            //if this if image from filesystem

            //convert to pdf
            $result_file_array = FileModification::PdfByFilePath($fileitentifier);
            $filepath = $result_file_array['filepath'];

            $path_to_base = $filepath;
            $fileId = sha1_file($filepath).sha1($filepath);

            $file_cache_item = null;

        } else {
            //if this is image from database
            $file_cache_item = FileCache::model()->findByAttributes(
                array('file_id'=>$fileitentifier)
            );

            if ($file_cache_item) {
                $fileId = intval($fileitentifier);
                $path_to_base = $file_cache_item->path;

            } else {
                $image = Images::model()->findByAttributes(array(
                    'Document_ID' => intval($fileitentifier)
                ));

                if($image) {
                    $mime = explode('/', $image->Mime_Type);
                    $temp_file_path = $path . '/' . $image->File_Name;
                    $infile = stripslashes($image->Img);
                    file_put_contents($temp_file_path, $infile);

                    $fileId = intval($fileitentifier);
                    $path_to_base = $temp_file_path;
                }
            }


        }


        if (!$file_cache_item) {
            $fileCash = FileCache::model()->findByAttributes(
                array(
                    'file_id'=>$fileId,
                    'user_id'=>Yii::app()->user->userID,
                    'client_id'=>Yii::app()->user->clientID,
                )
            );

            if (!$fileCash) {
                $fileCash = new FileCache();
            }

            $fileCash->file_id = $fileId;
            $fileCash->path = $path_to_base;
            $fileCash->client_id = Yii::app()->user->clientID;
            $fileCash->user_id = Yii::app()->user->userID;
            $fileCash->Created = time();
            $fileCash->save();

            $result_id = $fileCash->file_id;

        } else {
            $result_id = $file_cache_item->file_id;
        }

        return $result_id;
    }

    /**
     * Updates file in cashe. Even if it already in it.
     * @param $fileitentifier
     */
    public static function updateFileInCache($fileitentifier) {
        $path = FileModification::createDirectory('filecache');
        $path = FileModification::createDirectory('filecache/'.Yii::app()->user->clientID);

        if (is_file($fileitentifier)) {
            //if this if image from filesystem

            //convert to pdf
            $result_file_array = FileModification::PdfByFilePath($fileitentifier);
            $filepath = $result_file_array['filepath'];
            $filename = $result_file_array['filename'];

            $path_to_base = $filepath;
            $fileId = sha1_file($filepath).sha1($filepath);

        } else {
            //if this is image from database
                $image = Images::model()->findByAttributes(array(
                    'Document_ID' => intval($fileitentifier)
                ));

                if($image) {
                    $mime = explode('/', $image->Mime_Type);
                    $temp_file_path = $path . '/' . $image->File_Name;
                    $infile = stripslashes($image->Img);
                    file_put_contents($temp_file_path, $infile);

                    $fileId = intval($fileitentifier);
                    $path_to_base = $temp_file_path;
                }
        }


        FileCache::deleteFromCacheById($fileitentifier);
        FileCache::deleteFromCacheById($fileId);

        $fileCash = new FileCache();

            $fileCash->file_id = $fileId;
            $fileCash->path = $path_to_base;
            $fileCash->client_id = Yii::app()->user->clientID;
            $fileCash->user_id = Yii::app()->user->userID;
            $fileCash->Created = time();
            $fileCash->save();

            $result_id = $fileCash->file_id;

        return $result_id;
    }

    /**
     * Deletes both db-record and file on the filesystem
     * @param $fileitentifier
     */
    public static function deleteBothFromCacheById($fileitentifier) {
        $fileCashItems = FileCache::model()->findAllByAttributes(
            array('file_id'=>$fileitentifier)
        );

        foreach ($fileCashItems as $fileCashItem) {
            if ($fileCashItem) {

                @unlink($fileCashItem->path);
                $fileCashItem->delete();


            } else {

            }
        }

    }

    /**
     * Deletes cache record from database only
     * @param $fileitentifier
     */
    public static function deleteFromCacheById($fileitentifier) {
        $fileCashItems = FileCache::model()->findAllByAttributes(
            array('file_id'=>$fileitentifier)
        );

        foreach ($fileCashItems as $fileCashItem) {
            if ($fileCashItem) {
                $fileCashItem->delete();
            } else {

            }
        }

    }





    public static function getCacheFilePath($fileId) {
        $fileCash = FileCache::model()->findByAttributes(
            array(
                'file_id'=>$fileId
            )
        );

        if ($fileCash) {
            return $fileCash->path;
        } else {
            return false;
        }
    }

    public static function getCacheOlderThenDays($days) {

        $days_ago = time() - (intval($days) * 24 * 60 * 60);

        $condition = new CDbCriteria();
        $condition->addCondition('Created >='. $days_ago);

        $fileCashItems = FileCache::model()->findAll($condition);

        return $fileCashItems;

    }

    public static function deleteCacheOlderThenDays($days) {

        $days_ago = time() - (intval($days) * 24 * 60 * 60);
        $i = 0;

        $condition = new CDbCriteria();
        $condition->addCondition('Created <='. $days_ago);

        $fileCashItems = FileCache::model()->findAll($condition);

        foreach ($fileCashItems as $item) {
            @unlink($item->path);
            $item->delete();
            $i++;
        }

        return $i;
    }

    public static function deleteCache($clientId='',$userId='') {

        $i=0;
        if ($clientId) {
            $condition = new CDbCriteria();
            $condition->addCondition('client_id <='. $clientId);
            $fileCashItems = FileCache::model()->findAll($condition);

        } elseif ($userId) {
            $condition = new CDbCriteria();
            $condition->addCondition('user_id <='. $userId);
            $fileCashItems = FileCache::model()->findAll($condition);

        } elseif ($clientId && $userId) {
            $condition = new CDbCriteria();
            $condition->addCondition('user_id <='. $userId);
            $condition->addCondition('client_id <='. $clientId);
            $fileCashItems = FileCache::model()->findAll($condition);
        } else {
            $fileCashItems = FileCache::model()->findAll();
        }

        //Yii::app()->db->createCommand()->truncateTable(self::model()->tableName());


        foreach ($fileCashItems as $item) {
            @unlink($item->path);
            $item->delete();
            $i++;
        }
    }

}
