<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Controller\Plugin\PostRedirectGet;
use Zend\Mvc\Controller\Plugin\Redirect;
use Zend\Mvc\InjectApplicationEventInterface;
use Zend\View\Model\ViewModel;
use Zend\Filter;

class SignupController extends AbstractActionController implements InjectApplicationEventInterface
{

    public function indexAction()
    {
        $eventId = (int) $this->params('event');

        if ($eventId) {
            return $this->eventSignup($eventId);
        }

        /** @var \Application\Model\EventTable $eventTable */
        $eventTable = $this->serviceLocator->get('EventTable');
        $events = $eventTable->findAll();
        return new ViewModel(array('events' => $events));
    }

    public function thanksAction()
    {
        return new ViewModel();
    }

    protected function eventSignup($eventId)
    {
        /** @var \Application\Model\EventTable $eventTable */
        $eventTable = $this->serviceLocator->get('EventTable');
        $event = $eventTable->findById($eventId);

        if (!$event) {
            // better 404 experience?
            return $this->notFoundAction();
        }

        if ($this->request->isPost()) {
            $this->processForm($this->params()->fromPost(), $eventId);
            return $this->redirect()->toUrl('/thank-you');
        }

        $vm = new ViewModel(array('event' => $event));
        $vm->setTemplate('application/signup/form.phtml');
        return $vm;
    }

    protected function processForm(array $formData, $eventId)
    {
        $formData = $this->sanitizeData($formData);
        /** @var \Application\Model\RegistrationTable $regTable */
        $regTable = $this->serviceLocator->get('RegistrationTable');
        /** @var \Application\Model\AttendeeTable $attendeeTable */
        $attendeeTable = $this->serviceLocator->get('AttendeeTable');

        $reg = $regTable->persist($eventId, $formData['first_name'], $formData['last_name']);

        $ticketData = $formData['ticket'];
        foreach ($ticketData as $nameOnTicket) {
            $attendeeTable->persist($reg['id'], $nameOnTicket);
        }

        return true;
    }

    protected function sanitizeData(array $data)
    {
        $filter = $this->getServiceLocator()->get('RegDataFilter');
        $clean  = array();
        foreach ($data as $key => $item) {
            if (is_array($item)) {
                foreach ($item as $subKey => $subItem) {
                    $clean[$key][$subKey] = $filter->filter($subItem);
                }
            } else {
                $clean[$key] = $filter->filter($item);
            }
        }
        return $clean;
    }

}
