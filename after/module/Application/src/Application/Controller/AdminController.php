<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AdminController extends AbstractActionController implements RepoAwareInterface
{

    use RepoTrait;
    
    public function indexAction()
    {
        $eventEntity = FALSE;
        $eventId = (int) $this->params('event');
        if ($eventId) {
            return $this->listRegistrations($eventId);
        } else {
            return $this->listEvents();
        }
    }

    protected function listEvents()
    {
        $viewModel = new ViewModel(
            ['events' => $this->eventRepo->findAll()]);
        $viewModel->setTemplate('application/admin/index');
        return $viewModel;
    }
    
    protected function listRegistrations($eventId)
    {        
        $eventEntity = $this->eventRepo->findById($eventId);
        $vm = new ViewModel(array('event' => $eventEntity));
        $vm->setTemplate('application/admin/list.phtml');
        return $vm;
    }

}
