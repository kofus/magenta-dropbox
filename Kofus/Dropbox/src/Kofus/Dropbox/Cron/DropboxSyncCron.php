<?php 

namespace Kofus\Dropbox\Cron;
use Kofus\System\Cron\AbstractCron;


class DropboxSyncCron extends AbstractCron
{
    public function run()
    {
        $spec = $this->getSpecification();
        $service = $this->getServiceLocator()->get('KofusDropboxService');
        $messages = $service->sync($spec);
        foreach ($messages as $message)
            echo $message . "\n";
        
        return 'completed';
    }
    
   
    protected function em()
    {
        return $this->getServiceLocator()->get('Doctrine/ORM/EntityManager');
    }
    
    protected function nodes()
    {
        return $this->getServiceLocator()->get('KofusNodeService');
    }
    
    protected function mailer()
    {
        return $this->getServiceLocator()->get('KofusMailerService');
    }
}