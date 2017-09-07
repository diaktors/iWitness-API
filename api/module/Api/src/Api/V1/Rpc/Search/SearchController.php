<?php

namespace Api\V1\Rpc\Search;

use Api\V1\Controller\BaseActionController;
use Api\V1\Security\Authentication\AuthenticationServiceInterface;
use Api\V1\Security\Authorization\AclAuthorization;
use Api\V1\Security\Authorization\AuthorizationInterface;
use Api\V1\Service\UserService;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Psr\Log\LoggerInterface;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\View\ApiProblemModel;

class SearchController extends BaseActionController
{
    /** @var array */
    private $config = null;
    /** @var UserService $userService */
    private $userService = null;

    /**
     * @param array $config
     * @param \Api\V1\Service\UserService $userService
     * @param AuthenticationServiceInterface|UserService $authentication
     * @param AuthorizationInterface $authorization
     * @param LoggerInterface $logger
     */
    public function __construct(
        array $config,
        UserService $userService,
        AuthenticationServiceInterface $authentication,
        AuthorizationInterface $authorization,
        LoggerInterface $logger
    )
    {
        parent::__construct($authentication, $authorization, $logger);

        $this->config = $config;
        $this->userService = $userService;
    }

    /**
     * Index action
     *
     * @return boolean
     */
    public function indexAction()
    {
        try {
            //admin only right now
            if (!$this->isAdmin()) {
                return new ApiProblemModel(new ApiProblem(401, 'Unauthorized'));
            }

            $route = $this->getEvent()->getRouteMatch();
            $type = $route->getParam('type');

            if (empty($type)) {
                return new ApiProblemModel(new ApiProblem(412, 'The type parameter in form post data cannot be empty'));
            }

            // searching for user
            if ($type == 'user') {
                $request = $this->getRequest();
                $term = $request->getQuery('term');
                if (empty($term)) {
                    return new ApiProblemModel(
                        new ApiProblem(412, 'The phones parameter in form post data cannot be empty')
                    );
                }

                $viewModel = new JsonModel(
                    array('user' => $this->userService->findByPhoneNameEmail($term))
                );
                $viewModel->setTerminal(true);

                return $viewModel;
            }

            return new ApiProblemModel(new ApiProblem(412, 'Unsupported searching type ' . $type));

        } catch (Exception $ex) {
            return $this->processUnhandledException($ex);
        }
    }

    /**
     * @return string
     */
    public function getResourceId()
    {
        return AclAuthorization::RESOURCE_USER; //todo: may be wrong with other case
    }
}