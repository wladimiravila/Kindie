<?php
namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Entity
 */
class Comentario
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     *
     * @var integer
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     * @Assert\Length(
     *      max = 500,
     *      maxMessage = "El comentario no puede tener más de {{ limit }} caracteres"
     * )
     */
    protected $texto;

    /**
     * @ORM\Column(type="datetime")
     *
     * @var \Datetime
     */
    protected $fecha;

    /**
 * @ORM\ManyToOne(targetEntity="Usuario", inversedBy="comentarios")
 *
 * @var Usuario
 */
    protected $usuario;
    /**
     * @ORM\Column(type="boolean")
     *
     * @var boolean
     */
    protected $denunciado;

    /**
     * @ORM\ManyToOne(targetEntity="Proyecto", inversedBy="comentarios")
     * @ORM\JoinColumn(nullable=true)
     * @var Proyecto
     */
    protected $proyecto;



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
     * Set texto
     *
     * @param string $texto
     * @return Comentario
     */
    public function setTexto($texto)
    {
        $this->texto = $texto;

        return $this;
    }

    /**
     * Get texto
     *
     * @return string 
     */
    public function getTexto()
    {
        return $this->texto;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return Comentario
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;

        return $this;
    }

    /**
     * Get fecha
     *
     * @return \DateTime 
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * Set denunciado
     *
     * @param boolean $denunciado
     * @return Mensaje
     */
    public function setDenunciado($denunciado)
    {
        $this->denunciado = $denunciado;

        return $this;
    }

    /**
     * Get denunciado
     *
     * @return boolean
     */
    public function getDenunciado()
    {
        return $this->denunciado;
    }

    /**
     * Set usuario
     *
     * @param \AppBundle\Entity\Usuario $usuario
     * @return Comentario
     */
    public function setUsuario(\AppBundle\Entity\Usuario $usuario = null)
    {
        $this->usuario = $usuario;

        return $this;
    }

    /**
     * Get usuario
     *
     * @return \AppBundle\Entity\Usuario 
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * Set proyecto
     *
     * @param \AppBundle\Entity\Proyecto $proyecto
     * @return Comentario
     */
    public function setProyecto(\AppBundle\Entity\Proyecto $proyecto = null)
    {
        $this->proyecto = $proyecto;

        return $this;
    }

    /**
     * Get proyecto
     *
     * @return \AppBundle\Entity\Proyecto 
     */
    public function getProyecto()
    {
        return $this->proyecto;
    }
}
