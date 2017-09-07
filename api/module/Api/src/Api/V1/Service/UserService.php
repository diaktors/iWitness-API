<?php

namespace Api\V1\Service;

use Api\V1\Entity\Admin;
use Api\V1\Entity\PersonAbstract;
use Api\V1\Entity\Subscription;
use Api\V1\Entity\User;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Perpii\InputFilter\Filter\NormalizePhoneFilter;
use Webonyx\Util\UUID;
use Zend\Crypt\Password\Bcrypt;
use ZF\ApiProblem\ApiProblem;
use Psr\Log\LoggerInterface;
use Perpii\Util\Cryptography;
use Perpii\Message\PushNotificationManager;
use Api\V1\Service\Extension\TokenTrait;

class UserService extends ServiceAbstract
{

    use TokenTrait;

    const ENTITY_CLASS = 'Api\V1\Entity\User';
    const RESET_PASSWORD_TOKEN_EXPIRE_HOURS = 6;
    const RESET_PASSWORD_ROLE = 'reset-password';
    const MAX_DATE = 99999999999;



    /** @var Config */
    private $config = null;

    /**
     * @var \Perpii\Message\PushNotificationManager
     */
    private $pushNotification;

    /**
     * @param array $config
     * @param EntityManager $entityManager
     * @param \Perpii\Message\PushNotificationManager $pushNotification
     * @param LoggerInterface $logger
     */
    public function __construct(
        array $config,
        EntityManager $entityManager,
        PushNotificationManager $pushNotification,
        LoggerInterface $logger)
    {
        parent::__construct($entityManager, $logger);

        $this->config = $config;
        $this->pushNotification = $pushNotification;

        $this->subscriptionRepository = $this->entityManager->getRepository('Api\V1\Entity\Subscription');
    }

    /**
     * @param $subscriptionUuid
     * @param null $userId
     * @return Subscription|bool|ApiProblem
     */
    public function validateSubscription($subscriptionUuid, $userId = null)
    {
        //todo: must implement subscription as new database schema change
        if (null != $subscriptionUuid) {

            /** @var Subscription $subscription */
            $subscription = $this->subscriptionRepository->find($subscriptionUuid);

            //check existing
            if (!$subscription) {
                return new ApiProblem(422, 'Failed Validation', null, null, array(
                    'validation_messages' => ['message' => 'Subscription id ' . $subscriptionUuid . ' does not exist'],
                ));
            }
            //check expire time
            $time = time();
            $subEnd = $subscription->getExpireAt();

            //TODO: temporary fixing wrong timestamp on server.
            if ($time < ($subscription->getStartAt() - 24 * 60 * 60)
                || (($subEnd > 0) && $time > ($subEnd + 24 * 60 * 60))
            ) {
                return new ApiProblem(422, 'Failed Validation', null, null, array(
                    'validation_messages' => ['message' => 'Subscription id ' . $subscriptionUuid . ' has been expired'],
                ));
            }

            //check null for creating (kind of hacking prevention)
            if (!$userId && $subscription->getUser()) {
                return new ApiProblem(422, 'This subscription has been created by another user ');
            }

            //check to prevent using subscription of another user (kind of hacking prevention)
            if ($userId && $subscription->getUser() && ($userId != $subscription->getUser()->getId())) {
                return new ApiProblem(422, 'This subscription has been created by another user ');
            }

            return $subscription;
        }

        return true;
    }

    /**
     * @param $filteredData
     * @param \Api\V1\Entity\Subscription $subscription
     * @return \Api\V1\Entity\User
     */
    public function createUser($filteredData, Subscription $subscription = null)
    {
        $userUuid = UUID::generate();
        $bcrypt = new Bcrypt;
        $bcrypt->setCost(10);
        $password = $bcrypt->create($filteredData['password']);

        $filteredData['password'] = $password;
        $filteredData['secretKey'] = sha1(uniqid(md5(srand()), true));
        $filteredData['createdAt'] = time();
        $filteredData['updatedAt'] = time();

        //update data into database
        $userClass = static::ENTITY_CLASS;

        /** @var \Api\V1\Entity\User $user */
        $user = new $userClass($userUuid);
        $this->hydrator->hydrate($filteredData, $user);

        if (null !== $subscription) {
            $user
                ->setSubscriptionId($subscription->getId())
                ->setSubscriptionStartAt($subscription->getStartAt())
                ->setSubscriptionExpireAt($subscription->getExpireAt());
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->updateSubscriptionOwner($user, $subscription);

        return $user;
    }

    /**
     * @param PersonAbstract $user
     * @param $data
     * @param Subscription $subscription
     * @return PersonAbstract
     */
    public function updateUser(PersonAbstract $user, $data, Subscription $subscription = null)
    {
        if ($subscription) {
            $data['subscriptionUuid'] = $subscription->getId();
        } else {
            unset($data['subscriptionUuid']);
        }

        //update password field if user want to change it
        if (isset($data['newPassword'])) {
            $data['password'] = Cryptography::createPassword($data['newPassword']);
        } else {
            unset($data['password']); //don't update this
        }
        unset($data['newPassword']);

        $data['modified'] = time();

        $this->patch($user, $data);

        //automatic property $subscription should be placed at end of function.
        //Whenever 'updateUser($user, $data)' is called, this method
        //will throw exception if the function signature is: updateSubscriptionOwner($subscription, $user)
        $this->updateSubscriptionOwner($user, $subscription);
        $this->updateUserSubscription($user, $subscription);

        return $user;
    }

    /**
     * @param Subscription $subscription
     * @param PersonAbstract $user
     */
    private function updateSubscriptionOwner(PersonAbstract $user, Subscription $subscription = null)
    {
        //update subscription according to new user
        if ((null !== $subscription) && (!$subscription->getUser())) {
            $this->entityManager->merge($subscription);
            $subscription->setUser($user);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }

    /**
     * @param User $user
     * @param Subscription $subscription
     */
    public function updateUserSubscription(User $user, Subscription $subscription = null)
    {
        if ($user && $subscription) {
            if ($user instanceof Admin) {
                $userExpire = 0; //never expire
            } else {
                //if user has bought many subscription, it should be added to current date
                $userExpire = $user->getSubscriptionExpireAt();

                //has expired users
                if ($userExpire == 0) {
                    $userExpire = 0; //never expire
                } elseif ($userExpire < time()) {
                    $userExpire = $subscription->getExpireAt();
                } else {
                    $userExpire += ($subscription->getExpireAt() - $subscription->getStartAt());
                    $userExpire = \DateTime::createFromFormat('U', $userExpire)
                        ->setTime(23, 59, 59)
                        ->getTimestamp();
                }
            }
            /** @var User $dbUser */
            $dbUser = $this->find($user->getId());
            $dbUser
                ->setSubscriptionId($subscription->getId())
                ->setSubscriptionStartAt($subscription->getStartAt())
                ->setSubscriptionExpireAt($userExpire);
            $this->entityManager->flush($dbUser);
        }
    }

    /**
     * @param $id
     * @return null|\Api\V1\Entity\User
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function getUserById($id)
    {
        $user = $this->entityManager->find('Api\V1\Entity\PersonAbstract', $id);
        if ($user instanceof \Api\V1\Entity\User || $user instanceof \Api\V1\Entity\Admin) {
            return $user;
        }
        return null;
    }

    /**
     * @param \Api\V1\Entity\User $user
     * @param array $queryParams
     * @param $collectionClass
     * @internal param array $params
     * @internal param $id
     * @return null|\Api\V1\Entity\User
     */
    public function fetchUsers($user, $queryParams = array(), $collectionClass)
    {
        if (isset($queryParams['query']['phone'])) {
            $phoneFilter = new NormalizePhoneFilter();
            $phone = $phoneFilter->filter($queryParams['query']['phone']);
            $queryParams['query']['phone'] = $phone;
        }

        /**
         * @param QueryBuilder $queryBuilder
         * @throws \Exception
         */
        $select = function (QueryBuilder &$queryBuilder) use ($user, &$queryParams) {
            if (!$user->isAdmin()) {
                $parameter = uniqid('id');
                $queryBuilder
                    ->andWhere($queryBuilder->expr()->eq('row.id', ":$parameter"))
                    ->setParameter($parameter, UUID::toBinary($user->getId()));
            }
            //get admin or user
            $nodes = array('Api\V1\Entity\User', 'Api\V1\Entity\Admin');
            $classes = array();
            foreach ($nodes as $class) {
                $classes[] = "row INSTANCE OF " . $class;
            }
            $queryBuilder->andWhere(call_user_func_array(array($queryBuilder->expr(), 'orx'), $classes));

            //search by created date
            if (isset($queryParams['query']['created_search'])) {
                list($from, $to) = $this->extractSearchFromToInQueryParams($queryParams, 'created_search');
                $queryBuilder
                    ->andWhere('row.created BETWEEN :created_search_from AND :created_search_to ')
                    ->setParameter('created_search_from', $from)
                    ->setParameter('created_search_to', $to);
                unset($queryParams['query']['created_search']);
            }

            //search by expired date
            if (isset($queryParams['query']['expired_search'])) {
                list($from, $to) = $this->extractSearchFromToInQueryParams($queryParams, 'expired_search');
                //this is really special case, subscriptionExpireAt = 0 is never expire
                if($to != self::MAX_DATE && $from == 0){
                    $from = 1;
                }
                $queryBuilder
                    ->andWhere('row.subscriptionExpireAt BETWEEN :expired_search_from AND :expired_search_to ')
                    ->setParameter('expired_search_from', $from, Type::INTEGER)
                    ->setParameter('expired_search_to', $to, Type::INTEGER);
                //$queryBuilder->andWhere('row.subscriptionExpireAt = 1000 ');
                unset($queryParams['query']['expired_search']);
            }

            // search by first name
            if (isset($queryParams['query']['search_phase'])) {
                $searchPhase = $queryParams['query']['search_phase'];
                if(!empty($searchPhase)) {
                    $queryBuilder
                        ->andWhere('row.firstName LIKE :searchPhase OR row.lastName LIKE :searchPhase OR row.email LIKE :searchPhase OR row.phone LIKE :searchPhase')
                        ->setParameter('searchPhase', $searchPhase . '%');
                }
                unset($queryParams['query']['search_phase']);
            }
        };

        return $this->fetchAll(
            $queryParams,
            null,
            $select,
            $collectionClass
        );
    }

    /**
     * @param $queryParams
     * @param $key
     * @return array
     * @throws \Exception
     */
    private function extractSearchFromToInQueryParams($queryParams, $key)
    {
        $params = explode(',', $queryParams['query'][$key]);
        $count = count($params);
        $to = 0;

        if ($count == 1) {
            $from = trim($params[0]);
        } elseif ($count == 2) {
            $from = trim($params[0]);
            $to = trim($params[1]);
        } else {
            throw new  \Exception('Invalid search parameter format. It should be search=from,to');
        }

        if (!is_numeric($from) || !is_numeric($to)) {
            throw new  \Exception('Invalid From To format.');
        }

        if ($to == 0) {
            $to = self::MAX_DATE;
        }

        return array($from, $to);
    }

    /**
     * @param $xday
     * @return mixed
     */
    public function getXDaysExpire($xday)
    {
        return $this->getUserRepository()->getXDaysExpire($xday);
    }

    /**
     * @return array of User
     */
    public function getExpired()
    {
        return $this->getUserRepository()->getExpired();
    }


    /**
     * @param $email
     * @return \Api\V1\Entity\User
     */
    public function findByEmail($email)
    {
        $user = $this->getUserRepository()->findOneBy(array('email' => $email));
        return $user;
    }

    /**
     * @param $phone
     * @return \Api\V1\Entity\User
     */
    public function findByPhone($phone)
    {
        $phoneFilter = new NormalizePhoneFilter();
        $phone = $phoneFilter->filter($phone);
        $user = $this->getUserRepository()->findOneBy(array('phone' => $phone));
        return $user;
    }


    /**
     * Get user repository out of service
     * @deprecated this function is used in check duplicate User only, please don't use it elsewhere
     * @return \Doctrine\ORM\EntityRepository|null
     */
    public function getUserRepository()
    {
        return $this->getRepository();
    }

   

    /**
     * @param User $user
     * @param $message
     */
    public function pushNotification(User $user, $message)
    {
        if (!$user || !$message) {
            return;
        }

        /** @var \Api\V1\Repository\DeviceRepository $deviceRepository */
        $deviceRepository = $this->entityManager->getRepository('Api\V1\Entity\Device');
        $devices = $deviceRepository->findByUser($user);

        foreach ($devices as $device) {
            /** @var \Api\V1\Entity\Device $device */
            if ($device && $device->getToken()) {
                $this->pushNotification
                    ->setModel($device->getModel())
                    ->setMessage($message)
                    ->setDeviceToken($device->getToken())
                    ->send();
            }
        }
    }

    /**
     * @param $token
     * @param $password
     * @return User
     */
    public function resetPassword($token, $password)
    {
        /** @var  \Api\V1\Entity\User $user */
        $user = $this->assertValidToken($token, self::RESET_PASSWORD_ROLE, self::RESET_PASSWORD_TOKEN_EXPIRE_HOURS);
        if ($user) {
            $bcrypt = new Bcrypt;
            $bcrypt->setCost(10);
            $password = $bcrypt->create($password);
            $user->setPassword($password);
            $this->entityManager->flush($user);
        }
        return $user;
    }





    /**
     * @param $email
     * @return \Api\V1\Entity\User
     */
    public function findByPhoneNameEmail($email)
    {
        $user = $this->getUserRepository()->findByPhoneNameEmail($email);
        return $user;
    }

	/**
	 * developer - raviteja
	 *
	 * @param $userId
	 * @return boolean true
	 */
	public function updateLogoutFlag($userId) {
		$sql = "UPDATE user SET login_flag = 0 WHERE UUID_TO_STR(id) =:user_id" ;
		$params['user_id'] = $userId;
		$stmt = $this->entityManager->getConnection()->prepare($sql);
		$stmt->execute($params);
		return $stmt->rowCount();	
	}
	/**
		* * developer - raviteja
		* *
		* * @param $userId
		* * @return boolean true
		* */
	public function removeAllTokens($userId) {
		$sql = "DELETE FROM oauth_access_tokens  WHERE  client_id!='e114cbaa-f5a1-11e3-bc94-000c29c9a052' and user_id =:user_id" ;
		$params['user_id'] = $userId;
		$stmt = $this->entityManager->getConnection()->prepare($sql);
		$stmt->execute($params);
		return $stmt->rowCount();	
	}

}
