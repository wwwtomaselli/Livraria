<?php

namespace LivrariaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;


class CaixaController extends Controller
{
    /**
     * @Route("/caixa")
     */
    public function pdvAction()
    {
       return $this->render("LivrariaBundle:Caixa:index.html.twig");
    }
}
