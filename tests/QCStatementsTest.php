<?php

namespace eIDASCertificate\tests;

use PHPUnit\Framework\TestCase;
use eIDASCertificate\Certificate\X509Certificate;
use eIDASCertificate\QCStatements;

class QCStatementsTest extends TestCase
{
    const jmcrtfile = 'Jean-Marc Verbergt (Signature).crt';
    const eucrtfile = 'European-Commission.crt';
    const mocrtfile = 'Maarten Joris Ottoy.crt';

    public function setUp()
    {
        $this->jmcrt = new X509Certificate(
            file_get_contents(
                __DIR__ . "/certs/" . self::jmcrtfile
            )
        );
        $this->mocrt = new X509Certificate(
            file_get_contents(
                __DIR__ . "/certs/" . self::mocrtfile
            )
        );
        $this->eucrt = new X509Certificate(
            file_get_contents(
                __DIR__ . "/certs/" . self::eucrtfile
            )
        );
    }

    public function testQCStatementsParse()
    {
        $crtParsed = $this->eucrt->getParsed();
        $qcStatementBinary =
          $crtParsed['extensions']['qcStatements'];
        $qcStatements = new QCStatements($qcStatementBinary);
        $this->assertEquals(
            ['QCSyntaxV2-LegalPerson',
            'QCComplianceStatement',
            'QCSSCD',
            'QCQualifiedType-eseal',
            'QCPDSs'],
            array_keys($qcStatements->getStatements())
        );
        $this->assertEquals(
            [
            'https://www.quovadisglobal.com/repository',
            'en'
          ],
            [
            $qcStatements->getPDSLocations()[0]['url'],
            $qcStatements->getPDSLocations()[0]['language']
          ]
        );
        $this->assertEquals(
            'MAgGBgQAjkYBAQ==',
            base64_encode(
                $qcStatements->getStatements()['QCComplianceStatement']->getBinary()
            )
        );

        $crtParsed = $this->jmcrt->getParsed();
        $qcStatementBinary =
          $crtParsed['extensions']['qcStatements'];
        $qcStatements = new QCStatements($qcStatementBinary);
        $this->assertEquals(
            ['QCComplianceStatement', 'QCSSCD'],
            array_keys($qcStatements->getStatements())
        );

        $crtParsed = $this->mocrt->getParsed();
        $qcStatementBinary =
          $crtParsed['extensions']['qcStatements'];
        $qcStatements = new QCStatements($qcStatementBinary);
        $this->assertEquals(
            ['QCComplianceStatement', 'QCSSCD'],
            array_keys($qcStatements->getStatements())
        );
    }
}