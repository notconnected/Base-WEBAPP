<?php
namespace Controllers;

class PublicPart extends AbstractController
{
    public function getActionIndex()
    {
        $_SESSION['message'] = _('Empty data');
        $this->getOutput()->sendRedirect('/publicPart/error/');
    }
}