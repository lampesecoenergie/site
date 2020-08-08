<?php

namespace Cminds\AdminLogger\Model\History;

use Cminds\AdminLogger\Helper\DataChecker;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Registry;

/**
 * Class AbstractObject
 *
 * @package Cminds\AdminLogger\Model\History
 */
abstract class AbstractObject
{
    /**
     * @var Session
     */
    private $authSession;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Data
     */
    private $jsonHelper;
    /**
     * @var DataChecker
     */
    protected $dataChecker;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * AbstractObject constructor.
     *
     * @param Session     $authSession
     * @param Request     $request
     * @param Data        $jsonHelper
     * @param DataChecker $dataChecker
     * @param Registry    $registry
     */
    public function __construct(
        Session $authSession,
        Request $request,
        Data $jsonHelper,
        DataChecker $dataChecker,
        Registry $registry
    ) {
        $this->authSession = $authSession;
        $this->request = $request;
        $this->jsonHelper = $jsonHelper;
        $this->dataChecker = $dataChecker;
        $this->registry = $registry;
    }

    /**
     * Prepare data which will be saved.
     *
     * @param      $event
     * @param      $actionType
     * @param null $changedValues
     *
     * @return array
     */
    protected function prepareActionData($event, $actionType, $changedValues = null)
    {
        $actionData = [];
        $entityType = $event->getData('entity_type');

        if ($this->authSession->getUser() === false
            || $this->authSession->getUser() === null
        ) {
            $actionData['admin_id'] = $event->getData('reference_value');
            $actionData['admin_name'] = $event->getData('user_name');
        } else {
            $actionData['admin_id'] = $this->authSession->getUser()->getId();
            $actionData['admin_name'] = $this->authSession->getUser()->getUsername();
        }
        $actionData['action_type'] = $actionType;
        if ($event->getData($entityType) === false
            || $event->getData($entityType) === null
        ) {
            $actionData['reference_value'] = $event->getData('reference_value');
        } else {
            $actionData['reference_value'] = $event->getData($entityType)->getId();
        }

        $actionData['reference_value'] = ($actionData['admin_id'] == '')? 'Unregistered User' : $actionData['reference_value'] ;
        $actionData['admin_id'] = ($actionData['admin_id'] == '')? 0 : $actionData['admin_id'];

        $actionData['ip'] = $this->request->getServerValue('REMOTE_ADDR');
        $actionData['browser_agent'] = $this->request->getServerValue('HTTP_USER_AGENT');

        if ($changedValues) {
            $actionData['old_value'] = $this->jsonHelper->jsonEncode($changedValues['old_value']);
            $actionData['new_value'] = $this->jsonHelper->jsonEncode($changedValues['new_value']);
        }

        return $actionData;
    }
}
