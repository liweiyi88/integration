<?php
namespace AppBundle\Messaging;

interface MessageHandler
{
    public function handle($message);
}
