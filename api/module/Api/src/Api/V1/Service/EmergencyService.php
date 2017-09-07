<?php

namespace Api\V1\Service;

use Api\V1\Entity\Contact;
use Api\V1\Entity\Event;
use Api\V1\Entity\User;
use Doctrine\ORM\EntityManager;
use Exception;
use Perpii\Message\EmailManager;
use Perpii\Message\SmsManager;
use Psr\Log\LoggerInterface;

class EmergencyService extends ServiceAbstract
{
    /**
     * @var EmailManager
     */
    private $emailManager;

    /**
     * @var SmsManager
     */
    private $smsManager;

    /**
     * @param \Perpii\Message\EmailManager $emailManager
     * @param \Perpii\Message\SmsManager $smsManager
     * @param EntityManager $entityManager
     * @param LoggerInterface $logger
     * @internal param \Api\V1\Service\EventService $eventService
     */
    public function __construct(
        EmailManager $emailManager,
        SmsManager $smsManager,
        EntityManager $entityManager,
        LoggerInterface $logger)
    {
        parent::__construct($entityManager, $logger);

        $this->emailManager = $emailManager;
        $this->smsManager = $smsManager;
    }

    /**
     * @param Event $event
     * @param User $user
     */
    public function call911(Event $event, User $user, $safe, $dialno)
    {
        $repository = $this->entityManager->getRepository('Api\V1\Entity\Contact');
		$contacts = $repository->findBy(array('userId' => $user->getId(), 'flags'=> Contact::ACCEPTED));
        
		$lng = $event->getInitialLong();
		$lat = $event->getInitialLat();
		/** @var $contact \Api\V1\Entity\User */
		$alert_link = '/friend-alert?id=' . $event->getId().'&safe='.$safe;
        foreach ($contacts as $contact) {
            $this->sendEmergencyEmail(
                $user->getEmail(),
                $user->getFullName(),
                $contact->getEmail(),
                array(
                    'event' => $event,
                    'from' => $user,
                    'to' => $contact,
					'alertLink' => $alert_link,
					'safe' => $safe,
					'dialno' => $dialno
                )
            );


            if ($contact->getPhone()) {
                $this->sendEmergencySms(
                    $user->getPhone(),
                    $contact->getPhone(),
                    array('from' => $user, 'safe' => $safe,
					'lat' => $lat,
					//'gender' => $user->getGender(),
					'name' => $user->getFullName(),
					'template' => 1,
					'alertLink' => $alert_link,
					'lng' => $lng,
					'dialno' => $dialno
				)
                );
            }
        }
    }

    /**
     * @param $senderEmail string
     * @param $senderName string
     * @param $receiverEmail string
     * @param array $data
     */
    private function sendEmergencyEmail($senderEmail, $senderName, $receiverEmail, array $data)
    {
        try {
            $this
                ->emailManager
                ->setSenderEmail($senderEmail)
                ->setSenderName($senderName)
                ->setReceiver($receiverEmail)
                ->setTemplate('/event/alert-email.phtml')
                ->setTemplateData($data)
                ->send();
        } catch (\Exception $ex) {
            $this->error('Couldn\'t send email to ' . $receiverEmail, ['exception' => $ex]);
        }
    }

    /**
     * @param $senderPhone string
     * @param $receiverPhone string
     * @param $data
     */
    private function sendEmergencySms($senderPhone, $receiverPhone, $data)
    {
		try {
			 /*$gender = $data['gender'];
			 $personalPronoun = ($gender === null) ? 'them' : ($gender == 0 ? 'him' : 'her');
			 $geolocation = $data['lat'].','.$data['lng'];
			 $url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng='.$geolocation.'&sensor=false';
			 $ch = curl_init($url);
			 curl_setopt ($ch, CURLOPT_URL, $url);
			 curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			 $file_contents = curl_exec($ch);
			 $json_decode = json_decode($file_contents);
			 $address = $json_decode->results[0]->formatted_address;*/

             if ($data['safe'] =='danger'){
				 //$message ="Use the link below to see the last GPS location as captured by iWitness before the {$data['dialno']} call.";
				 $message ="Click below for GPS location.";
		     }
			 else{
			     //$message ="Use the link below to see the last GPS location and a video of the event as captured by iWitness before the {$data['dialno']} call.";
				 $message ="Click for GPS location and video.";
		     }

			 $smsdata = array_merge(
				 array(
					 //'personalPronoun' => $personalPronoun,
					 'phone'         => $this->formatPhone($senderPhone),
					 'message'       => $message,
					 //'address'       => "Address: ".$address,
					 'template'      => 1 
				 ),
				 $data
			 );
            $this->smsManager
                ->setSender($senderPhone)
                ->setReceiver($receiverPhone)
                ->setTemplateData($smsdata)
               // ->setTemplate('/event/alert-sms.phtml')
                ->send();

        } catch (\Exception $ex) {
            $this->error('Couldn\'t send sms to ' . $receiverPhone, ['exception' => $ex]);
        }
	}

	/**
		* * @param $phone
		* * @return mixed|string
		* */
	private function formatPhone($phone)
	{
		if (strlen($phone) < 10) {
			return $phone;
		}

		// make sure that valid int'l phone number
		$phone = $this->intlPhone($phone);
		$length = strlen($phone);
		
		$human = substr($phone, $length - 10, 3) . '-'
		       . substr($phone, $length - 7, 3) . '-'
		       . substr($phone, $length - 4);
		
		if ('1' === $phone[0] && $length <= 11) {
		     return $human; // US
		}
		
        return '+' . substr($phone, 0, $length - 10) . '-' . $human;
	}

	/**
	 * * Transforms phone number from human-friendly format to international format
	 * *
	 * * @param string $phone
	 * * @return mixed|string
	 * */
	private function intlPhone($phone)
	{   
		$norm = preg_replace('~[^0-9]~', '', $phone);

		if (10 === strlen($norm)) {
			return '1' . $norm; // US
		}

		return $norm;
	}

		
}
