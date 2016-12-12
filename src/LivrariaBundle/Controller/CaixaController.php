<?php

namespace LivrariaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use \LivrariaBundle\Entity\Cupom;
use \LivrariaBundle\Entity\CupomItem;
use \LivrariaBundle\Entity\Produtos;


class CaixaController extends Controller
{
    /**
     * @Route("/caixa", name="caixa")
     */
    public function pdvAction(Request $request)
    {
        $cupomId = $request->getSession()->get("cupom-id", null);
        
        if(!$cupomId)
        {
            $cupom = new Cupom();
            $cupom->setData(new \DateTime());
            $cupom->setValorTotal(0);
            $cupom->setVendedor(1);

            $em = $this->getDoctrine()->getManager();
            $em->persist($cupom); //neste momento gera o 'Id' da entidade
            $em->flush();

            $request->getSession()->set("cupom-id", $cupom->getId());
        }
        
        
        return $this->render("LivrariaBundle:Caixa:index.html.twig");
    }
    
    /**
     * @Route("/caixa/carregar", name="pesquisar_produto")
     * @Method("POST")
     */
public function carregarProdutoAction(Request $request, $item)
    {
        $codProd = $request->request->get("codigo");
        $em = $this->getDoctrine()->getManager();
        $produto = $em->getRepository('LivrariaBundle:Produtos')
                ->find($codProd);
        $cupomId = $request->getSession()->get("cupom-id");
        $cupom = $em->getRepository('LivrariaBundle:Cupom')
                ->find($cupomId);
        $quantItem = $em->getRepository('LivrariaBundle:CupomItem')
                ->findBy(array("cupomId" => $cupomId));
        
        if ($produto instanceof Produtos)
        {
            $novoItem = new CupomItem();
            $novoItem->setCupomId($cupom);
            $novoItem->setDescricaoItem($produto->getNome());
            $novoItem->setItemCod($codProd);
            $novoItem->setQuantidade(1);
            $novoItem->setValorUnitario($produto->getPreco());
            $novoItem->setOrdemItem(count($quantItem) + 1);
            

            $em->persist($novoItem);
            $em->flush();
            
            $retorno['status'] = 'ok';
            $retorno['produto'] = $produto;

            
        } else {
            $retorno['status'] = 'erro';
            $retorno['mensagem'] = "Produto não encontrado";
        }
        
        return $this->json($retorno);
    }
    
    /**
     * @Route("/caixa/estornar/{item}")
     */
    public function estornarItemAction(Request $request, $item)
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
     * @Route("/caixa/listar", name="listagem")
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
