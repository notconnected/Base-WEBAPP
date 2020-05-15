<?php
namespace Controllers;

class Api extends AbstractController
{
    public function getActionIndex()
    {
        $this->getOutput()->send(''); //empty data
        return false;
    }
}