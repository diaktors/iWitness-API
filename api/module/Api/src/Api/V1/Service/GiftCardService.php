<?php

namespace Api\V1\Service;

use Api\V1\Entity\GiftCard;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;

class GiftCardService extends ServiceAbstract
{
    const ENTITY_CLASS = 'Api\V1\Entity\GiftCard';

    /**
     * @param EntityManager $entityManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityManager $entityManager,
        LoggerInterface $logger = null)
    {
        parent::__construct($entityManager, $logger);
    }

    /**
     * @param  string $email
     * @return GiftCard | null
     */
    public function findByRecipientEmail($email)
    {
        return $this->getRepository()->findOneBy(array('recipientEmail' => $email), array('deliveryDate' => 'DESC'));
    }

    /**
     * @return array
     */
    public function findTodayDeliverGift()
    {
        return $this->getRepository()->getTodayDeliverGift();
    }

    /**
     * @param GiftCard $gift
     */
    public function setDelivered(GiftCard $gift)
    {
        $gift->setIsDelivered(true);
        $this->entityManager->merge($gift);
        $this->entityManager->flush($gift);
    }
}