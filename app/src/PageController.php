<?php

namespace {

    use App\Service\EmailService;
    use Psr\Log\LoggerInterface;
    use SilverStripe\CMS\Controllers\ContentController;
    use SilverStripe\Control\HTTPRequest;
    use SilverStripe\Forms\EmailField;
    use SilverStripe\Forms\FieldList;
    use SilverStripe\Forms\Form;
    use SilverStripe\Forms\FormAction;
    use SilverStripe\Forms\RequiredFields;
    use SilverStripe\Forms\TextField;
    use SilverStripe\ORM\ValidationResult;

class PageController extends ContentController
    {

        private static $allowed_actions = [
            'ContactForm'
        ];

        private static $dependencies = [
            'EmailService' => '%$'. EmailService::class,
            'Logger' => '%$' . LoggerInterface::class
        ];

        /**
         * @var EmailService
         */
        private $emailService;

        /**
         * @var LoggerInterface
         */
        private $logger;

        /**
         * @param EmailService $emailService
         * @return $this
         */
        public function setEmailService(EmailService $emailService)
        {
            $this->emailService = $emailService;
            return $this;
        }

        /**
         * @param LoggerInterface $logger
         * @return $this
         */
        public function setLogger(LoggerInterface $logger)
        {
            $this->logger = $logger;
            return $this;
        }

        protected function init()
        {
            parent::init();
            // You can include any CSS or JS required by your project here.
            // See: https://docs.silverstripe.org/en/developer_guides/templates/requirements/
        }


        public function index(HTTPRequest $request)
        {
            return [
                'Title' =>'Test ABC Toast',
                'Content' => 'Getting in touch to upgrade'
            ] ;
        }


        public function ContactForm()
        {
            $fields = new FieldList(
                TextField::create('Name', 'Name'),
                EmailField::create('Email', 'Email'),
                TextField::create('Company', 'Company'),
            );

            $actions = new FieldList(
                FormAction::create('handleSubmit')->setTitle('Submit')
            );

            $required = new RequiredFields('Name', 'Email', 'Company');
            $form = new Form($this, 'ContactForm', $fields, $actions, $required);

            return $form;
        }

        public function handleSubmit($data, Form $form)
        {

            $exists = Contact::get()->filter('Email', $data['Email'])->first();
            if ($exists) {
                $validationResult = new ValidationResult();
                $validationResult->addFieldError('Email', 'This email already exists');
                $form->setSessionValidationResult($validationResult);
                $form->setSessionData($form->getData());
                return $this->redirectBack();
            }

            try {
                $contact = Contact::create();
                $contact->Name = $data['Name'];
                $contact->Email = $data['Email'];
                $contact->Company = $data['Company'];
                $contact->write();
               
                $this->emailService->sendContactEmail($data['Email'],$data['Name'],$data['Company']);
                
                $form->sessionMessage('Thanks for your contact '. $data['Name'] ,'good');
                return $this->redirectBack();

            } catch (Exception $e) {
                $form->sessionMessage('Something went wrong');
                $this->logger->debug($e->getMessage());
                
                return $this->redirectBack();
            }
           
        }

    }
}
