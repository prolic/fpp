<?php

declare(strict_types=1);

namespace FppTest;

use Fpp\Argument;
use Fpp\Constructor;
use Fpp\Definition;
use Fpp\Deriving;
use PHPUnit\Framework\TestCase;
use function Fpp\loadTemplate;

class LoadTemplateTest extends TestCase
{
    /**
     * @var string
     */
    private $templatePath;

    protected function setUp(): void
    {
        $this->templatePath = __DIR__ . '/../src/templates/';
    }

    /**
     * @test
     */
    public function it_loads_default_class_template(): void
    {
        $constructor = new Constructor('Bar');
        $definition = new Definition('Foo', 'Bar', [$constructor]);

        $template = loadTemplate($definition);

        $this->assertSame(file_get_contents($this->templatePath . '/class.template'), $template->classTemplate());
        $this->assertCount(0, $template->bodyTemplates());
    }

    /**
     * @test
     */
    public function it_loads_string_template(): void
    {
        $constructor = new Constructor('String');
        $definition = new Definition('Foo', 'Bar', [$constructor]);

        $template = loadTemplate($definition);

        $this->assertSame(file_get_contents($this->templatePath . '/string.template'), $template->classTemplate());
        $this->assertCount(0, $template->bodyTemplates());
    }

    /**
     * @test
     */
    public function it_loads_class_template_with_deriving_body_templates(): void
    {
        $constructor = new Constructor('Bar', [new Argument('name', 'string')]);
        $definition = new Definition('Foo', 'Bar', [$constructor], [new Deriving\ToString(), new Deriving\FromString()]);

        $template = loadTemplate($definition);

        $this->assertSame(file_get_contents($this->templatePath . '/class.template'), $template->classTemplate());
        $this->assertCount(2, $template->bodyTemplates());
        $this->assertSame(file_get_contents($this->templatePath . '/tostring.template'), $template->bodyTemplates()[0]);
        $this->assertSame(file_get_contents($this->templatePath . '/fromstring.template'), $template->bodyTemplates()[1]);
    }

    /**
     * @test
     */
    public function it_loads_uuid_template(): void
    {
        $constructor = new Constructor('Bar');
        $definition = new Definition('Foo', 'Bar', [$constructor], [new Deriving\Uuid()]);

        $template = loadTemplate($definition);

        $this->assertSame(file_get_contents($this->templatePath . '/uuid.template'), $template->classTemplate());
    }
}
