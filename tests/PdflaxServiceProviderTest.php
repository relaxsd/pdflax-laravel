<?php

use PHPUnit\Framework\TestCase;

class PdflaxServiceProviderTest extends TestCase
{

    /** @var PHPUnit_Framework_MockObject_MockObject|Illuminate\Contracts\Foundation\Application */
    protected $applicationMock;

    /** @var \Relaxsd\Pdflax\Laravel\PdflaxServiceProvider */
    protected $serviceProvider;

    protected function setUp() : void
    {
        parent::setUp();

        $this->applicationMock = $this->createMock('\Illuminate\Contracts\Foundation\Application');

        $this->serviceProvider = new \Relaxsd\Pdflax\Laravel\PdflaxServiceProvider($this->applicationMock);
    }

    /**
     * @test
     */
    public function it_can_be_constructed()
    {
        $this->assertInstanceOf('Relaxsd\Pdflax\Laravel\PdflaxServiceProvider', $this->serviceProvider);
    }

    /**
     * @test
     */
    public function it_registers_a_singleton_registry_and_factory() {

        // We expect the service provider to register 'pdflax-registry' as a singleton
        $this->applicationMock
            ->expects($this->once())
            ->method('singleton')
            ->with('pdflax-registry', $this->isInstanceOf('Closure'));

        // and a 'pdflax' binding (no singleton)
        $this->applicationMock
            ->expects($this->once())
            ->method('bind')
            ->with('pdflax', $this->isInstanceOf('Closure'));

        $this->serviceProvider->register();
    }

    /**
     * @test
     */
    public function it_boots_to_registers_the_package_and_fpdf_implementation() {

        // We also need to mock a registry because the ServiceProvider will register a PdfCreator implementation
        /** @var PHPUnit_Framework_MockObject_MockObject|\Relaxsd\Pdflax\Registry\RegistryWithDefault $registryMock */
        $registryMock = $this->createMock('Relaxsd\Pdflax\Registry\RegistryWithDefault');

        // We expect the service provider to ask for the 'pdflax-registry' (and we'll give it a mock)
        $this->applicationMock
            ->expects($this->once())
            ->method('make')
            ->with('pdflax-registry')
            ->willReturn($registryMock);

        // ...and register the FPdf implementation with it.
        $registryMock
            ->expects($this->once())
            ->method('register')
            ->with('fpdf', 'Relaxsd\Pdflax\Fpdf\FPdfPdfCreator', true);

        $this->serviceProvider->boot();
    }

}
