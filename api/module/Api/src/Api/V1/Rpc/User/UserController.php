<?php

namespace Api\V1\Rpc\User;

use Api\V1\Controller\BaseActionController;
use Api\V1\Security\Authorization\AclAuthorization;
use Api\V1\Security\Authorization\AuthorizationInterface;
use Api\V1\Service\PhotoService;
use Api\V1\Service\UserService;
use Webonyx\Util\UUID;
use Zend\View\Model\JsonModel;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\View\ApiProblemModel;
use Api\V1\Security\Authentication\AuthenticationServiceInterface;
use Doctrine\ORM\QueryBuilder;
use Psr\Log\LoggerInterface;
use Perpii\Message\EmailManager;

class UserController extends BaseActionController
{

    use UserValidatorTrait;

    /** @var \Api\V1\Service\PhotoService */
    private $photoService;

    /** @var \Api\V1\Service\UserService */
    private $userService;

    /**
     * @var EmailManager
     */
    private $emailManager;

    /**
     * @param \Api\V1\Security\Authentication\AuthenticationServiceInterface $authentication
     * @param AuthorizationInterface $authorization
     * @param \Psr\Log\LoggerInterface $logger
     * @param PhotoService $photoService
     * @param \Api\V1\Service\UserService $userService
     * @param EmailManager $emailManager
     */
    public function __construct(AuthenticationServiceInterface $authentication,
                                AuthorizationInterface $authorization,
                                LoggerInterface $logger,
                                PhotoService $photoService,
                                UserService $userService,
                                EmailManager $emailManager)
    {
        parent::__construct($authentication, $authorization, $logger);
        $this->photoService = $photoService;
        $this->userService = $userService;
        $this->emailManager = $emailManager;
    }

    /**
     * @return JsonModel|ApiProblemModel
     */
    public function uploadAction()
    {
        try {
            $request = $this->getRequest();

            $id = $this
                ->getEvent()
                ->getRouteMatch()
                ->getParam('user_id');

            /** @var \Api\V1\Entity\User $user */
            $user = $this->userService->find($id);
            if (!$user) {
                return new ApiProblemModel (
                    new ApiProblem(404, 'Entity with id ' . $id . ' was not found')
                );
            }

            $result = $this->isAuthorized($user, AclAuthorization::PERMISSION_UPDATE);
            if ($result instanceof ApiProblem) {
                return new ApiProblemModel ($result);
            }

            $files = $request->getFiles()->toArray();
            $fileName = self::getFirstFileName($files);


            // Make certain to merge the files info!
            $data = array_merge_recursive(
                $request->getPost()->toArray(),
                $files
            );

            $validationResult = $this->validateUploadPhoto($user, $data, $fileName);
            if ($validationResult !== true) {
                return new ApiProblemModel($validationResult);
            }

            $requestUri = $this->getRequest()->getUriString();
            $requestUri = substr($requestUri, 0, strpos($requestUri, '/upload'));
            $params = array_merge($data, array('url' => $requestUri . '/photo/default'));

            $this->photoService->uploadPhoto(
                $user,
                $params[$fileName]
            );

            return new JsonModel($params);
        } catch (\Exception $ex) {
            return $this->processUnhandledException($ex);
        }
    }

    /**
     * @return JsonModel|ApiProblemModel
     */
    public function photoAction()
    {
        try {
            $route = $this->getEvent()->getRouteMatch();
            $id = $route->getParam('user_id');
            $photoName = $route->getParam('name', null);
            $size = $route->getParam('size', 'default');
            $validateResult = $this->photoService->validatePhoto($id, $photoName, $size);

            if ($validateResult instanceof ApiProblem) {
                return new ApiProblemModel($validateResult);
            }

            $photoName = $validateResult;
            $resizePhoto = $this->photoService->config['cacheDir'] . '/' . $size . '/' . $photoName;

            if (!file_exists($resizePhoto)) {
                $this->photoService->resizeAndCachePhoto($photoName);
            }

            $imageContent = null;
            if (file_exists($resizePhoto)) {
                $imageContent = file_get_contents($resizePhoto);
            } else {
                return new ApiProblemModel(new ApiProblem(404, 'The requested photo does not exists'));
            }

            // get image content
            $response = $this->getResponse();
            $response->setContent($imageContent);
            $response
                ->getHeaders()
                ->addHeaderLine('Content-Transfer-Encoding', 'binary')
                ->addHeaderLine('Content-Type', 'image/jpeg')
                ->addHeaderLine('Cache-Control', 'max-age=0')
                ->addHeaderLine('Pragma', 'public')
                ->addHeaderLine('Content-Length', mb_strlen($imageContent));

            return $response;
        } catch (\Exception $ex) {
            return $this->processUnhandledException($ex);
        }
    }

    /**
     * @throws \Exception
     * @return JsonModel|ApiProblemModel
     */
    public function validatePhoneAction()
    {
        try {
            $router = $this->getEvent()->getRouteMatch();
            $phone = $router->getParam('phone');
            $ignoreUserId = $router->getParam('ignoreUserId');

            if (empty($phone)) {
                throw new \Exception('Please provide phone number', 417);
            }

            /** @var  \Api\V1\Rest\User\UserCollection $users */
            $users = $this->userService->fetchAll(
                array(),
                null,
                function (QueryBuilder &$queryBuilder) use ($phone, $ignoreUserId) {
                    //get admin or user
                    $nodes = array('Api\V1\Entity\User', 'Api\V1\Entity\Admin');
                    $classes = array();
                    foreach ($nodes as $class) {
                        $classes[] = "row INSTANCE OF " . $class;
                    }
                    $queryBuilder->andWhere(call_user_func_array(array($queryBuilder->expr(), 'orx'), $classes));

                    if (!empty($ignoreUserId)) {
                        $queryBuilder->andWhere('row.phone = :phone AND row.id <> :ignoreUserId');
                        $queryBuilder->setParameter(':ignoreUserId', UUID::toBinary($ignoreUserId));
                    } else {
                        $queryBuilder->andWhere('row.phone = :phone');
                    }
                    $queryBuilder->setParameter(':phone', $phone);
                },
                'Api\V1\Rest\User\UserCollection'
            );

            if ($users->count() > 0) {
                throw new \Exception('Account already exists for your phone number', 417);
            } else {
                return new JsonModel(array('status' => '200', 'message' => 'Phone number is valid'));
            }
        } catch (\Exception $ex) {
            return $this->processUnhandledException($ex);
        }
    }


    /**
     * @return JsonModel|ApiProblemModel
     * @throws \Api\V1\Service\Extension\BusinessException
     */
    public function forgotPasswordAction()
    {
        try {
            $router = $this->getEvent()->getRouteMatch();
            $emailOrPhone = trim($router->getParam('email'));

            if (filter_var($emailOrPhone, FILTER_VALIDATE_EMAIL)) {
                $user = $this->userService->findByEmail($emailOrPhone);
            } else {
                $user = $this->userService->findByPhone($emailOrPhone);
            }

            if (!$user) {
                throw new \Exception('The email address or wireless number could not be found.', 404);
			}

			$user_details = $this->userService->findByPhoneNameEmail($emailOrPhone);

			//print_r($user_details);exit;

			$token = $this->userService->generatePasswordToken($user, $user_details[0]['secret_key'], UserService::RESET_PASSWORD_ROLE);

			//$token = $user_details[0]['secret_key'];
			//$role = UserService::RESET_PASSWORD_ROLE;
//$token = Token::sign(time() . ':' . $role . ':' . $user->getId(), $token);

            $this
                ->emailManager
                ->setReceiver($user->getEmail())
                ->setTemplate('/user/forgot-password-email.phtml')
                ->setTemplateData(
                    array('to' => $user, 'token' => $token, 'expireHours' => UserService::RESET_PASSWORD_TOKEN_EXPIRE_HOURS)
                )
                ->send();

            return new JsonModel(array('status' => '200', 'message' => 'Instructions sent to your email', 'email' => $emailOrPhone));

        } catch (\Exception $ex) {
            return $this->processUnhandledException($ex);
        }
    }

    /**
     * @return JsonModel|ApiProblemModel
     */
    public function validateChangePasswordTokenAction()
    {
        try {
            $router = $this->getEvent()->getRouteMatch();
            $token = $router->getParam('token');

            if (empty($token)) {
                throw new \Exception('Token is required', 417);
            }

            if ($this->userService->assertValidToken($token, UserService::RESET_PASSWORD_ROLE, UserService::RESET_PASSWORD_TOKEN_EXPIRE_HOURS)) {
                return new JsonModel(array('status' => '200', 'message' => 'Token is valid'));
            }

            return new JsonModel(array('status' => '404', 'message' => 'Token is invalid'));
        } catch (\Exception $ex) {
            return $this->processUnhandledException($ex);
        }
	}


    /**
     * @return JsonModel|ApiProblemModel
     * @throws \Api\V1\Service\Extension\BusinessException
     */
    public function emeertrgencyMailAction()
    {
        try {
            //$router = $this->getEvent()->getRouteMatch();

            //$this
              //  ->emailManager
                //->setReceiver('rteja71@gmail.com')
                //->setBodyText('working fine')
               // ->send();
			return "ok";
            //return new JsonModel(array('status' => '200', 'message' => 'Instructions sent to your email', 'email' => 'rteja71@gmail.com'));

        } catch (\Exception $ex) {
            return $this->processUnhandledException($ex);
        }
    }


    /**
     * @return JsonModel|ApiProblemModel
     */
    public function resetPasswordAction()
    {
        try {
            $content = json_decode($this->getRequest()->getContent(), true);
            if (!isset($content['token']) || empty($content['token'])) {
                throw new \Exception('Token is required', 417);
            }

            if (!isset($content['password']) || empty($content['password'])) {
                throw new \Exception('Password is required', 417);
            }

            $user = $this->userService->resetPassword($content['token'], $content['password']);

            if ($user) {
                return new JsonModel(array('status' => '200', 'message' => 'Password has been changed.'));
            }
            return new JsonModel(array('status' => '404', 'message' => 'Token is invalid'));
        } catch (\Exception $ex) {
            return $this->processUnhandledException($ex);
        }
    }

    /**
     * @return string
     */
    public function getResourceId()
    {
        return AclAuthorization::RESOURCE_USER;
    }

    /**
     * @param array $files
     * @param string $default
     * @return string
     */
    private static function  getFirstFileName(array &$files, $default = 'photo')
    {
        $fileName = $default;
        if (count($files) > 0) {
            $keys = array_keys($files);
            $fileName = $keys[0];
        }
        return $fileName;
	}
	/**
	 * developer - raviteja
	 *
	 * @return boolean
	 * @throws \Api\V1\Service\Extension\BusinessException
	 */
	public function logoutAction()
	{
		try {
			$router = $this->getEvent()->getRouteMatch();
			$userId = trim($router->getParam('user_id'));

			$stmnt = $this->userService->updateLogoutFlag($userId);
			if($stmnt > 0) {
				return new JsonModel(array('status' => '200', 'message' => 'Logged out successfully'));
			} else {
				return new ApiProblemModel(new ApiProblem(404, 'Unauthorized Access'));
			}
		} catch (\Exception $ex) {
			return $this->processUnhandledException($ex);
		}
	}
	/**
		* * developer - raviteja
		* *
		* * @return boolean
		* * @throws \Api\V1\Service\Extension\BusinessException
		* */
	public function logoutAllAction()
	{
		try {
			$router = $this->getEvent()->getRouteMatch();
			$userId = trim($router->getParam('user_id'));
			$stmnt1 = $this->userService->removeAllTokens($userId);
			$stmnt2 = $this->userService->updateLogoutFlag($userId);

			if($stmnt2 > 0) {
				return new JsonModel(array('status' => '200', 'message' => 'Logged out from all devices successfully'));
			} else {
				return new ApiProblemModel(new ApiProblem(404, 'Unauthorized Access'));
			}
		} catch (\Exception $ex) {
			return $this->processUnhandledException($ex);
		}
	}
}
