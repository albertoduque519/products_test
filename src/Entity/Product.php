<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 * @UniqueEntity(
 *     fields={"code", "name"},
 *     errorPath="product",
 *     message="Este codigo ya lo tiene el producto "
 * )
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(
     *      min = 2,
     *      max = 10,
     *      minMessage = "El código debe tener mínimo {{ limit }} caracteres",
     *      maxMessage = "El código debe tener máximo {{ limit }} caracteres"
     * )
     * @Assert\Regex(
     *     pattern="/^[\w]*$/",
     *     match=true,
     *     message="Caracteres no permitidos"
     * )
     * @ORM\Column(type="string", length=255)
     */
    private $code;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(
     *      min = 4,
     *      minMessage = "El nombre debe tener mínimo {{ limit }} caracteres"
     * )
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=255)
     */
    private $brand;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="products")
     */
    private $category;

    /**
     * @Assert\NotBlank()
     * @Assert\PositiveOrZero
     * @ORM\Column(type="float")
     */
    private $price;

    /**
     * @Assert\NotBlank()
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @Assert\NotBlank()
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
