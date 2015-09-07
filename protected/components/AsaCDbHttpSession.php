<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 6/24/15
 * Time: 5:43 PM
 */

class AsaCDbHttpSession extends CDbHttpSession {

protected function createSessionTable($db,$tableName)
{
    switch ($db->getDriverName()) {
        case 'mysql':
            $blob = 'LONGBLOB';
            break;
        case 'pgsql':
            $blob = 'BYTEA';
            break;
        case 'sqlsrv':
        case 'mssql':
        case 'dblib':
            $blob = 'VARBINARY(MAX)';
            break;
        default:
            $blob = 'BLOB';
            break;
    }
    $db->createCommand()->createTable($tableName, array(
        'id' => 'CHAR(32) PRIMARY KEY',
        'user_id' => 'integer',
        'client_id' => 'integer',
        'project_id' => 'integer',
        'expire' => 'integer',
        'data' => $blob,
    ));
}
    /**
     * Session write handler.
     * Do not call this method directly.
     * @param string $id session ID
     * @param string $data session data
     * @return boolean whether session write is successful
     */
    public function writeSession($id,$data)
    {
        // exception must be caught in session write handler
        // http://us.php.net/manual/en/function.session-set-save-handler.php
        try
        {
            $expire=time()+$this->getTimeout();
            $db=$this->getDbConnection();
            if($db->getDriverName()=='sqlsrv' || $db->getDriverName()=='mssql' || $db->getDriverName()=='dblib')
                $data=new CDbExpression('CONVERT(VARBINARY(MAX), '.$db->quoteValue($data).')');
            if($db->createCommand()->select('id')->from($this->sessionTableName)->where('id=:id',array(':id'=>$id))->queryScalar()===false)
                $db->createCommand()->insert($this->sessionTableName,array(
                    'id'=>$id,
                    'user_id'=>Yii::app()->user->userID,
                    'client_id' => Yii::app()->user->clientID,
                    'project_id' => Yii::app()->user->projectID,
                    'data'=>$data,
                    'expire'=>$expire,
                ));
            else
                $db->createCommand()->update($this->sessionTableName,array(
                    'user_id'=>Yii::app()->user->userID,
                    'client_id' => Yii::app()->user->clientID,
                    'project_id' => Yii::app()->user->projectID,
                    'data'=>$data,
                    'expire'=>$expire
                ),'id=:id',array(':id'=>$id));
        }
        catch(Exception $e)
        {
            if(YII_DEBUG)
                echo $e->getMessage();
            // it is too late to log an error message here
            return false;
        }
        return true;
    }

    public function destroySessionByUserID($user_id)
    {
        $this->getDbConnection()->createCommand()
            ->delete($this->sessionTableName,'user_id=:id',array(':id'=>$user_id));
        return true;
    }

    public function getUserProject($user_id)
    {
        $db=$this->getDbConnection();
        $project = $db->createCommand()->select('project_id')->from($this->sessionTableName)->where('user_id=:id',array(':id'=>$user_id))->queryScalar();

        return $project;
    }

    public function getUserClient($user_id)
    {
        $db=$this->getDbConnection();
        $client = $db->createCommand()->select('client_id')->from($this->sessionTableName)->where('user_id=:id',array(':id'=>$user_id))->queryScalar();

        return $client;
    }

}