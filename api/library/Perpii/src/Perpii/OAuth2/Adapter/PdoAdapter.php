<?php


namespace Perpii\OAuth2\Adapter;

use Perpii\InputFilter\Filter\NormalizePhoneFilter;
use Webonyx\Util\UUID;

class PdoAdapter extends \ZF\OAuth2\Adapter\PdoAdapter
{
    /**
     * Get user from database
     * @param $username
     * @throws \Exception
     * @return array|bool
     */
    public function getUser($username)
    {
        $phoneFilter = new NormalizePhoneFilter();
        $username = $phoneFilter->filter($username);
        $userInfo = $this->getUserByUserName($username);
        return $userInfo;
    }

    /**
     * @param string $username
     * @param string $password
     * @param null $firstName
     * @param null $lastName
     * @return bool|void
     * @throws \Exception
     */
    public function setUser($username, $password, $firstName = null, $lastName = null)
    {
        throw new \Exception('Method ' . __METHOD__ . ' was not implemented');
    }

    /**
     * @param $username
     * @return bool|mixed
     */
    private function  getUserByUserName($username)
    {
        $stmt = $this->db->prepare($sql = sprintf(
                'SELECT * from %s where (type = 1 OR type = 2) AND ' . $this->config['field_username'] . '=:username', $this->config['user_table']
            )
        );

        $stmt->execute(array('username' => $username));

        if (!$userInfo = $stmt->fetch()) {
            return false;
        }

        // the default behavior is to use "username" as the user_id
        $userInfo['user_id'] = UUID::toStr($userInfo[$this->config['field_userid']]);
        return $userInfo;
    }


    /**
     * @param string $client_id
     * @param null $client_secret
     * @return bool
     */
    public function checkClientCredentials($client_id, $client_secret = null)
    {
        $stmt = $this->db->prepare(sprintf('SELECT * from %s where client_id = :client_id', $this->config['client_table']));
        $stmt->execute(compact('client_id'));
        $result = $stmt->fetch();
        // make this extensible
        return $result && $result['client_secret'] == $client_secret;
    }

    /**
     * @param string $user
     * @param string $password
     * @return bool
     */
    public function checkPassword($user, $password)
    {
        if (parent::verifyHash($password, $user['password'])) {
            return true;
        } else {
            //support old API before migration, should be remove latter
            $phone = $user['phone'];
            $realm = $this->config['digest_auth_realm'];
            $hashPassword = md5("$phone:$realm:$password");
            if ($hashPassword == $user['password']) {
                $this->setToNewPasswordEncryption($user, $password);
                return true;
            }
            return false;
        }
    }

    /**
     * @param $user
     * @param $password
     */
    private function setToNewPasswordEncryption($user, $password)
    {
        try {
            //encrypt password
            $this->createBcryptHash($password);
            $idKey = $this->config['field_userid'];
            $sql = sprintf('UPDATE %s SET password = :password WHERE %s = :id', $this->config['user_table'], $idKey);
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':password', $password, \PDO::PARAM_STR);
            $stmt->bindValue(':id', $user[$idKey], \PDO::PARAM_INT);
            $stmt->execute();
        } catch (\Exception $ex) {
            error_log($ex->getMessage());
        }
    }
}