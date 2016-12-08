<?php

namespace LivrariaBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Description of Produtos
 * @author aluno
 * 
 * @ORM\Entity
 * @ORM\Table(name="produtos")
 */
class Produtos 
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank(message="Informe o nome do produto")
     */
    private $nome;
    
    /**
     * @ORM\Column(type="integer", length=5)
     * @Assert\NotBlank(message="Informe a quantia de itens do produto")
     * @Assert\GreaterThanOrEqual(
     *      value = 0,
     *      message = "A quantidade deve ser 0 (zero) ou mais produtos"
     * )
     * @Assert\Type(
     *     type="integer",
     *     message="O valor {{value}} não é um número inteiro positivo ou zero."
     * )
     */
    private $quantidade;
    
    /**
     * @ORM\Column(type="decimal", scale=2)
     * @Assert\NotBlank(message="Informe o preço de cada unidade do produto")
     * @Assert\GreaterThanOrEqual(
     *      value = 0,
     *      message = "O preço do produto deve ser no mínimo R$ 0.00"
     * )
     */
    private $preco;
    
    /**
     * @ORM\Column(type="string", length=80)
     * @Assert\NotBlank(message="Informe o tipo do produto")
     */
    private $tipo;
        
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $imagem;
    
    /**
     * @ORM\ManyToOne(targetEntity="Genero")
     * @ORM\JoinColumn(name="genero_id", referencedColumnName="id")
     * @Assert\NotBlank(message="Escolha o gênero do produto")
     */
    private $genero;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nome
     *
     * @param string $nome
     *
     * @return Produtos
     */
    public function setNome($nome)
    {
        $this->nome = $nome;

        return $this;
    }

    /**
     * Get nome
     *
     * @return string
     */
    public function getNome()
    {
        return $this->nome;
    }

    /**
     * Set quantidade
     *
     * @param integer $quantidade
     *
     * @return Produtos
     */
    public function setQuantidade($quantidade)
    {
        $this->quantidade = $quantidade;

        return $this;
    }

    /**
     * Get quantidade
     *
     * @return integer
     */
    public function getQuantidade()
    {
        return $this->quantidade;
    }

    /**
     * Set preco
     *
     * @param string $preco
     *
     * @return Produtos
     */
    public function setPreco($preco)
    {
        $this->preco = $preco;

        return $this;
    }

    /**
     * Get preco
     *
     * @return string
     */
    public function getPreco()
    {
        return $this->preco;
    }

    /**
     * Set tipo
     *
     * @param string $tipo
     *
     * @return Produtos
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * Get tipo
     *
     * @return string
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * Set imagem
     *
     * @param string $imagem
     *
     * @return Produtos
     */
    public function setImagem($imagem)
    {
        $this->imagem = $imagem;

        return $this;
    }

    /**
     * Get imagem
     *
     * @return string
     */
    public function getImagem()
    {
        return $this->imagem;
    }

    /**
     * Set genero
     *
     * @param \LivrariaBundle\Entity\Genero $genero
     *
     * @return Produtos
     */
    public function setGenero(\LivrariaBundle\Entity\Genero $genero = null)
    {
        $this->genero = $genero;

        return $this;
    }

    /**
     * Get genero
     *
     * @return \LivrariaBundle\Entity\Genero
     */
    public function getGenero()
    {
        return $this->genero;
    }
}
