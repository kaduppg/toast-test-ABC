<?php
namespace App\Service;

use SilverStripe\Control\Email\Email;
use SilverStripe\Core\Environment;

class EmailService {

    private $email;
    private $toastAbcEmail;

    public function __construct()
    {
        $this->email = new Email();
        $this->toastAbcEmail = Environment::getEnv('TOAST_ABC_TEST_EMAIL_FROM');
    }

    public function sendContactEmail($contactEmail, $contactName, $company)
    {
        $messageBody = " 
        <h2> Thank you for your contact </h2>
        <p><strong>Name:</strong> {$contactName}</p> 
        <p><strong>Company:</strong> {$company}</p>"; 
        
        $this->email->setFrom($this->toastAbcEmail); 
        $this->email->setTo($contactEmail); 
        $this->email->setSubject('Contact Message from Toast ABC Test'); 
        $this->email->setBody($messageBody); 
        $this->email->send(); 
    }
    
}