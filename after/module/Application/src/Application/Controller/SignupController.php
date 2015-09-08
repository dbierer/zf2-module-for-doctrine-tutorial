<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Controller\Plugin\PostRedirectGet;
use Zend\Mvc\Controller\Plugin\Redirect;
use Zend\Mvc\InjectApplicationEventInterface;
use Zend\View\Model\ViewModel;

class SignupController extends AbstractActionController 
                       implements InjectApplicationEventInterface, RepoAwareInterface
{

    use RepoTrait;
    
    public function indexAction()
    {
        $eventId = (int) $this->params('event');

        if ($eventId) {
            return $this->eventSignup($eventId);
        }

        $events = $this->eventRepo->findAll();
        return new ViewModel(array('events' => $events));
    }

    public function thanksAction()
    {
        return new ViewModel();
    }

    protected function eventSignup($eventId)
    {
        $event = $this->eventRepo->findById($eventId);

        if (!$event) {
            return $this->indexAction();
        }

        $messages = array();
        if ($this->request->isPost()) {
            $regData = ['firstName' => $this->params()->fromPost('firstName'),
                        'lastName' => $this->params()->fromPost('lastName'),
            ];
            $ticketData = $this->params()->fromPost('ticket');
            $this->filterData($messages, $regData, $ticketData);
            if ($this->processForm($ticketData, $event, $regData))
                return $this->redirect()->toUrl('/thank-you');
        }

        $vm = new ViewModel(['event' => $event,
                             'messages' => $messages,
        ]);
        $vm->setTemplate('application/signup/form.phtml');
        return $vm;
    }

    protected function filterData(&$messages, $regData, $ticketData)
    {
        $filter = $this->getServiceLocator()->get('application-data-filter');
        foreach ($regData as $key => $value) {
            $regData[$key] = $filter->filter($value);
        }
        foreach ($ticketData as $key => $value) {
            $ticketData[$key] = $filter->filter($value);
        }
    }
    
    protected function processForm(array $ticketData, $event, $regData)
    {
        $reg = $this->registrationRepo->persist($event, $regData);
        $event->setRegistrations($reg);
        $this->eventRepo->save($event);
        foreach ($ticketData as $nameOnTicket) {
            $attendee = $this->attendeeRepo->persist($reg, $nameOnTicket);
            $reg->setAttendees($attendee);
            $this->registrationRepo->update($reg);
        }
        
        return true;
    }

    /**
     * @return the $regForm
     */
    public function getRegForm()
    {
        if (!$this->regForm) {
            $this->regForm = $this->getServiceLocator()->get('application-form-registration');
        }
        return $this->regForm;
    }

    /**
     * @return the $baseForm
     */
    public function getBaseForm()
    {
        if (!$this->baseForm) {
            $this->baseForm = $this->getServiceLocator()->get('application-form-base');
        }
        return $this->baseForm;
    }

    /**
     * @return the $attendeeForm
     */
    public function getAttendeeForm()
    {
        if (!$this->attendeeForm) {
            $this->attendeeForm = $this->getServiceLocator()->get('application-form-attendee');
        }
        return $this->attendeeForm;
    }

}
