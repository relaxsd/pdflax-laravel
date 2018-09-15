<?php

use PHPUnit\Framework\TestCase;

class PdflaxServiceProviderTest extends TestCase
{

    /** @var PHPUnit_Framework_MockObject_MockObject|Illuminate\Foundation\Application */
    protected $applicationMock;

    /** @var \Pdflax\Laravel\PdflaxServiceProvider */
    protected $serviceProvider;

    protected function setUp()
    {
        parent::setUp();

        $this->applicationMock = $this->getMock('\Illuminate\Contracts\Foundation\Application', ['bind', 'make', 'singleton']);

        $this->serviceProvider = new \Pdflax\Laravel\PdflaxServiceProvider($this->applicationMock);
    }

    /**
     * @test
     */
    public function it_can_be_constructed()
    {
        $this->assertInstanceOf('Pdflax\Laravel\PdflaxServiceProvider', $this->serviceProvider);
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

        // We need to mock the object under test because we want to intercept its call to parent::package()
        /** @var \Pdflax\Laravel\PdflaxServiceProvider $serviceProviderMock */
        $serviceProviderMock = $this->getMock('Pdflax\Laravel\PdflaxServiceProvider', ['package'], [$this->applicationMock]);

        // We also need to mock a registry because the ServiceProvider will register a PdfCreator implementation
        $registryMock = $this->getMock('Pdflax\Registry\RegistryWithDefault', ['register']);

        // We expect the service provider to register itself as a package
        $serviceProviderMock
            ->expects($this->once())
            ->method('package')
            ->with('relaxsd/pdflax-laravel');

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
            ->with('fpdf', 'Pdflax\Fpdf\FPdfPdfCreator', true);

        $serviceProviderMock->boot();
    }

}
