<?php
namespace AppBundle\Messaging;

interface MessageProcessInterface
{
    public function process($message);
}