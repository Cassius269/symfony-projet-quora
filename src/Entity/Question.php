<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class Question
{
    #[Assert\NotBlank(message: 'La thématique de la question ne peut pas être vide')]
    private ?string $title = null;

    #[
        Assert\Length(
            min: 3,
            max: 100,
            minMessage: 'Entrer plus de 3 caractères',
            maxMessage: 'Entrer moins de 100 caractères'
        )
    ]
    private ?string $content = null;



    /**
     * Get the value of subject
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set the value of subjet
     *
     * @return  self
     */
    public function setTitle($title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the value of content
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Set the value of content
     *
     * @return  self
     */
    public function setContent($content): self
    {
        $this->content = $content;

        return $this;
    }
}
