<?php

namespace Siphoc\PdfBundle\Tests\Generator;

use Symfony\Component\Templating\PhpEngine;
use Siphoc\PdfBundle\Generator\PdfGenerator;

class PDFGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function test_it_stores_converter()
    {
        $cssToInline = $this->getCssToInlineMock();
        $jsToHTML = $this->getJSToHTMLMock();
        $snappy = $this->getSnappyMock();
        $templateEngine = $this->getEngineMock();

        $generator = new PdfGenerator(
            $cssToInline, $jsToHTML, $snappy, $templateEngine
        );

        $this->assertSame($cssToInline, $generator->getCssConverter());
        $this->assertSame($jsToHTML, $generator->getJSConverter());
        $this->assertSame($snappy, $generator->getGenerator());
        $this->assertSame($templateEngine, $generator->getTemplatingEngine());
    }

    public function test_it_stores_name()
    {
        $generator = $this->getPdfGenerator();
        $generator->setName('download.pdf');

        $this->assertEquals('download.pdf', $generator->getName());
    }

    public function test_it_converts_html()
    {
        $generator = $this->getPdfGenerator();
        $output = $generator->getOutputFromHtml(
            '<html><head></head><body></body></html>'
        );

        $this->assertEquals('PDFOutput', $output);
    }

    public function test_it_creates_response_for_download()
    {
        $generator = $this->getPdfGenerator();

        $this->assertInstanceOf(
            'Symfony\Component\HttpFoundation\Response',
            $generator->downloadFromView('AcmeDemoBundle:Foo:bar.html.twig')
        );
    }

    public function test_it_logs_messages()
    {
        $cssToInline = $this->getCssToInlineMock();
        $jsToHTML = $this->getJSToHTMLMock();
        $snappy = $this->getSnappyMock();
        $templateEngine = $this->getEngineMock();
        $logger = $this->getMock('Symfony\Component\HttpKernel\Log\LoggerInterface');
        $logger->expects($this->once())
            ->method('debug')
            ->with($this->equalTo('Get output from html.'));

        $generator = new PdfGenerator(
            $cssToInline, $jsToHTML, $snappy, $templateEngine, $logger
        );

        $this->assertSame($logger, $generator->getLogger());
        // this will call our debug functionality as stated in the mock generator
        $generator->getOutputFromHtml('<html></html>');
    }

    private function getSnappyMock()
    {
        // we're not going to test the actual PDF convertion. That's a job for
        // the guys at KNP.
        $snappy = $this->getMockBuilder('Knp\Snappy\Pdf')
            ->disableOriginalConstructor()->getMock();

        $snappy->expects($this->any())
            ->method('getOutputFromHtml')
            ->will($this->returnValue('PDFOutput'));

        return $snappy;
    }

    private function getJSToHTMLMock()
    {
        $converter = $this->getMockBuilder('Siphoc\PdfBundle\Converter\JSToHTML')
            ->disableOriginalConstructor()->getMock();

        $converter->expects($this->any())
            ->method('convertToString')
            ->will($this->returnValue($this->getHTML()));

        return $converter;
    }

    private function getCssToInlineMock()
    {
        $converter = $this->getMockBuilder('Siphoc\PdfBundle\Converter\CssToHTML')
            ->disableOriginalConstructor()->getMock();

        $converter->expects($this->any())
            ->method('convertToString')
            ->will($this->returnValue($this->getHTML()));

        return $converter;
    }

    private function getPdfGenerator()
    {
        $cssToInline = $this->getCssToInlineMock();
        $jsToHTML = $this->getJSToHTMLMock();
        $snappy = $this->getSnappyMock();
        $templateEngine = $this->getEngineMock();

        $generator = new PdfGenerator(
            $cssToInline, $jsToHTML, $snappy, $templateEngine
        );

        return $generator;
    }

    private function getEngineMock()
    {
        $engine = $this->getMockBuilder('Symfony\Component\Templating\PhpEngine')
            ->disableOriginalConstructor()->getMock();

        return $engine;
    }

    private function getHTML()
    {
        return file_get_contents(
            __DIR__ . '/../Fixtures/converted_css_data.html'
        );
    }
}
