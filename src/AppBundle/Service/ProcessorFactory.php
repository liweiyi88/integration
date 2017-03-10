<?php
namespace AppBundle\Service;


class ProcessorFactory
{
    /**
     * @param string $processor
     * @return mixed
     */
    public function get($processor)
    {
        switch ($processor) {
            case 'confirmation_email':
                return $this->getContainer()->get('processor.confirmation.email');
            case 'mailchimp':
                return $this->getContainer()->get('processor.mailchimp');
        }

        throw new \InvalidArgumentException('Unsupported Processor');
    }

}