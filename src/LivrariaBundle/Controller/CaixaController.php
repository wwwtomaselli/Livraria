<?php

namespace LivrariaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use \LivrariaBundle\Entity\Cupom;
use \LivrariaBundle\Entity\CupomItem;


class CaixaController extends Controller
{
    /**
     * @Route("/caixa", name="caixa")
     */
    public function pdvAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cupom = new Cupom();
        $cupom->setData(new \DateTime());
        $cupom->setValorTotal(0);
        $cupom->setVendedor(1);
        
        $em->persist($cupom); //neste momento gera o 'Id' da entidade
        $em->flush();
        
        $request->getSession()->set("cupom-id", $cupom->getId());
        
        
        
        return $this->render("LivrariaBundle:Caixa:index.html.twig");
    }
    
    /**
     * @Route("/caixa/carregar")
     * @Method("POST")
     */
public function carregarProdutoAction(Request $request, $item) //VERIFICAR!!!
    {
        $codProd = $request->requests->get("codigo");
        $em = $this->getDoctrine()->getManager();
        $produto = $em->getRepository('LivrariaBundle:Produtos')
                ->find($codProd);
        if ($produto == null)
        {
            return $this->json('erro');
        }
        $novoItem = new CupomItem();
        $novoItem->setCupomId($request->getSession()->get("cupom-id"));
        $novoItem->setItemCod($codProd);
        $novoItem->setQuantidade(1);
        $novoItem->setValorUnitario($produto->getPreco());
        
        $em->persist($novoItem);
        $em->flush();
        
        return $this->json('erro');
    }
    
    /**
     * @Route("/caixa/estornar/{item}")
     */
    public function estornarItemAction(Request $request, $item) //VERIFICAR!!!
    {
        $cupomId = $request->getSession()->get("cupom-id");
        $em = $this->getDoctrine()->getManager();
        $itemEstornar = $em->getRepository('LivrariaBundle:CupomItem')
                ->findBy(array(
                    'cupomId' => $cupomId,
                    'ordemItem' => $item
                ));
        $itemEstorno = new CupomItem();
        $itemEstorno->setCupomId($cupomId);
        $itemEstorno->setDescricaoItem("Estorno do Item: $item");
        $itemEstorno->setItemCod(1001); //código exclusivo para identificar estorno
        $itemEstorno->setQuantidade(1);
        $itemEstorno->setValorUnitario($itemEstornar->getValorUnitario() * -1);
        
        $em->persist($itemEstorno);
        $em->flush();
        
        return $this->json('ok');
    }
    
    /**
     * @Route("/caixa/cancelar")
     */
    public function cancelarVendaAction(Request $request)
    {
        $cupomId = $request->getSession()->get("cupom-id");
        $em = $this->getDoctrine()->getManager();
        $cupom = $em->getRepository('LivrariaBundle:Cupom')->find($cupomId);
        $cupom->setStatus('cancelado');
        
        $em->persist($cupom);
        $em->flush();
        
        return $this->json('ok');
    }
    
    /**
     * @Route("/caixa/finalizar")
     */
    public function finalizarVendaAction(Request $request)
    {
        $cupomId = $request->getSession()->get("cupom-id");
        $em = $this->getDoctrine()->getManager();
        $cupom = $em->getRepository('LivrariaBundle:Cupom')->find($cupomId);
        $cupom->setStatus('finalizado');
        
        $em->persist($cupom);
        $em->flush();
        
        //baixar os itens do estoque
        //fechar o total da compra
        
        return $this->json('ok');
    }
    
    /**
     * @Route("/caixa/listar")
     */
    public function listarItensAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $itens = $em->getRepository("LivrariaBundle:CupomItem")
                ->findBy(array(
                    "cupomId" => $request->getSession()->get("cupom-id")
                ));
        
        return $this->json($itens);
    }
}
