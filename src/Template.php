<?php

declare (strict_types=1);

namespace Fpp;

class Template
{
    /**
     * @var string
     */
    private $classTemplate;

    /**
     * @var string[]
     */
    private $bodyTemplates;

    public function __construct(string $classTemplate, array $bodyTemplates)
    {
        $this->classTemplate = $classTemplate;
        $this->bodyTemplates = $bodyTemplates;
    }

    public function classTemplate(): string
    {
        return $this->classTemplate;
    }

    /**
     * @return string[]
     */
    public function bodyTemplates(): array
    {
        return $this->bodyTemplates;
    }
}
