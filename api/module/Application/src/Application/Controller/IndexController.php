<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use  Zend\View\Model\JsonModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        return new JsonModel(
            array(
                'status' => 200,
                'name' => 'IWITNESS',
                'description' => 'Welcome to IWITNESS API service',
                'version' => '1.0',
				'build' => '2.0_7_20140731'
            )
        );
    }
}

