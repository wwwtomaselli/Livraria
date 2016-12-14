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
     * @Security("has_role('ROLE_ADMIN')")
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
     * //Observacao: o mais adequado é comunicar o item a estornar via POST
     * @Route("/caixa/estornar/{item}")
     */
    public function estornarItemAction(Request $request, $item)
    {
        $cupomId = $request->getSession()->get("cupom-id");
        $em = $this->getDoctrine()->getManager();
        $itemEstornar = $em->getRepository('LivrariaBundle:CupomItem')
                ->findOneBy(array(
                    'cupomId' => $cupomId,
                    'ordemItem' => $item
                ));
        $quantItem = $em->getRepository('LivrariaBundle:CupomItem')
                ->findBy(array("cupomId" => $cupomId));
        $cupom = $em->getRepository('LivrariaBundle:Cupom')
                ->find($cupomId);
        $itemEstorno = new CupomItem();
        $itemEstorno->setCupomId($cupom);
        $itemEstorno->setDescricaoItem("Estorno do Item: $item");
        $itemEstorno->setItemCod(9999); //código exclusivo para identificar estorno
        $itemEstorno->setQuantidade(0);
        $itemEstorno->setOrdemItem(count($quantItem) + 1);
        $itemEstorno->setValorUnitario(-1 * $itemEstornar->getValorUnitario());
        $itemEstornar->setQuantidade(0);
        
        $em->persist($itemEstorno);
        $em->persist($itemEstornar);
        $em->flush();
        
        return $this->redirectToRoute('caixa');
    }
    
    /**
     * @Route("/caixa/cancelar", name="cancelar")
     */
    public function cancelarVendaAction(Request $request)
    {
        $cupomId = $request->getSession()->get("cupom-id");
        $em = $this->getDoctrine()->getManager();
        $cupom = $em->getRepository('LivrariaBundle:Cupom')->find($cupomId);
        $cupom->setStatus('cancelado');
        
        $em->persist($cupom);
        $em->flush();
        
        $request->getSession()->set("cupom-id", null);
        return $this->redirectToRoute('caixa');
    }
    
    /**
     * @Route("/caixa/finalizar", name="concluir")
     */
    public function finalizarVendaAction(Request $request)
    {
        //obter o cupom a fechar e chamar a gestao do banco de dados
        $cupomId = $request->getSession()->get("cupom-id");
        $em = $this->getDoctrine()->getManager();
        
        //calcular o total da compra e baixar o estoque
        $valorTotal = 0.0;
        $itens = $em->getRepository("LivrariaBundle:CupomItem")
                ->findBy(array(
                    "cupomId" => $request->getSession()->get("cupom-id")
                ));
        foreach($itens as $item)
        {
            $valorTotal += $item->getValorUnitario() * $item->getQuantidade();
            $itemCod = $item->getItemCod();
            if($itemCod != 9999)
            {
                $produto = $em->getRepository("LivrariaBundle:Produtos")
                    ->find($itemCod);
                $produto->setQuantidade($produto->getQuantidade()-1);
                $em->persist($produto);
            }
        }
        
        //finalizar a compra
        $cupom = $em->getRepository('LivrariaBundle:Cupom')->find($cupomId);
        $cupom->setValorTotal($valorTotal);
        $cupom->setStatus('finalizado');
        $em->persist($cupom);
        
        //gravar no banco de dados
        $em->flush();
        
        //retornar ao caixa para uma nova compra
        $request->getSession()->set("cupom-id", null);
        return $this->redirectToRoute('caixa');
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
