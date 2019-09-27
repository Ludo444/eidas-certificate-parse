<?php

namespace eIDASCertificate\tests;

use PHPUnit\Framework\TestCase;
use eIDASCertificate\DataSource;
use eIDASCertificate\TrustedList;
use eIDASCertificate\TrustedList\TSLType;
use eIDASCertificate\TrustedList\TSLPointer;
use eIDASCertificate\DigitalIdentity\ServiceDigitalIdentity;
use eIDASCertificate\Certificate\X509Certificate;

class TLTest extends TestCase
{
    const lotlXMLFileName = 'eu-lotl.xml';
    const nltlAttributes = [
        'SchemeTerritory' => 'NL',
        'SchemeOperatorName' => 'Radiocommunications Agency',
        'TSLSequenceNumber' => 44,
        'TSLSignedBy' => 'def82d40878a148e21fcacbcbfdf7623ed9d6ca149d631ca1ed61051827f31fc',
    ];

    private $tlolxml;
    private $tlol;
    private $tls;
    private $tlxml;
    private $tl;
    private $tslPointers;
    private $testSchemeTerritories;

    public function setUp()
    {
        $this->datadir = __DIR__ . '/../data';
        $xmlFilePath = $this->datadir.self::lotlXMLFileName;
        if (! file_exists($xmlFilePath)) {
            $this->lotlXML = DataSource::getHTTP(
                TrustedList::ListOfTrustedListsXMLPath
            );
            file_put_contents($xmlFilePath, $this->lotlXML);
        } else {
            $this->lotlXML = file_get_contents($xmlFilePath);
        }
        $this->lotl = new TrustedList($this->lotlXML);
        // if (! $this->tlolxml) {
        //     $this->tlolxml=file_get_contents('data/eu-lotl.xml');
        // }
        // if (! $this->tlol) {
        //     $this->tlol = new TrustedList($this->tlolxml, null, false);
        // };
        if (! $this->testSchemeTerritories) {
            $this->testSchemeTerritories = ['HU','DE','SK'];
        }
        // if (! $this->tls) {
        //     foreach ($this->testSchemeTerritories as $schemeTerritory) {
        //         $this->tls[$schemeTerritory] = $this->loadTL($schemeTerritory);
        //     };
        // }
    }

    public static function getNLTLAttributes()
    {
        $tlAttributes = self::nltlAttributes;
        $tlAttributes['ParentTSL'] = LOTLRootTest::getLOTLAttributes();
        return $tlAttributes;
    }


    public function TLAttributeTests($thistl)
    {
        $this->assertEquals(
            2,
            strlen($thistl->getSchemeTerritory())
        );
        $this->assertGreaterThan(
            10,
            strlen($thistl->getSchemeOperatorName())
        );
        $this->assertInternalType("int", $thistl->getListIssueDateTime());
        $this->assertGreaterThan(1262300400, $thistl->getListIssueDateTime());
        $this->assertInternalType("int", $thistl->getNextUpdate());
        $this->assertGreaterThan(1262300400, $thistl->getNextUpdate());
        $this->assertGreaterThan($thistl->getListIssueDateTime(), $thistl->getNextUpdate());
        $this->assertInstanceOf(TSLType::class, $thistl->getTSLType());
        $this->assertEquals(
            "EUgeneric",
            $thistl->getTSLType()->getType()
        );
        foreach ($thistl->getDistributionPoints() as $dp) {
            $this->assertEquals(
                $dp,
                filter_var(
                    $dp,
                    FILTER_VALIDATE_URL,
                    FILTER_FLAG_PATH_REQUIRED
                    // FILTER_FLAG_PATH_REQUIRED |
                    // FILTER_FLAG_HOST_REQUIRED |
                    // FILTER_FLAG_SCHEME_REQUIRED
                )
            );
        };
        $this->assertEquals(64, strlen($thistl->getXMLHash()));
    }

    public function testTLPointers()
    {
        foreach ($this->testSchemeTerritories as $schemeTerritory) {
            $tslPointers = $this->lotl->getTrustedListPointer($schemeTerritory);
            $this->assertEquals(
                1,
                sizeof($tslPointers)
            );
            $tslPointer = $tslPointers[0];
            $this->assertInstanceOf(TSLPointer::class, $tslPointer);
            $this->assertGreaterThan(
                0,
                $tslPointer->getServiceDigitalIdentities()
            );
            $x509Certificates = [];
            foreach ($tslPointer->getServiceDigitalIdentities() as $sdi) {
                $this->assertInstanceOf(ServiceDigitalIdentity::class, $sdi);
                $this->assertGreaterThan(
                    0,
                    $sdi->getX509Certificates()
                );
                foreach ($sdi->getX509Certificates() as $x509Certificate) {
                    $x509Certificates[] = $x509Certificate;
                }
            }
            $this->assertGreaterThan(
                0,
                sizeof($x509Certificates)
            );
            $this->assertEquals(
                'application/vnd.etsi.tsl+xml',
                $tslPointer->getTSLMimeType()
            );
            $this->assertEquals(
                $tslPointer->getTSLLocation(),
                filter_var(
                    $tslPointer->getTSLLocation(),
                    FILTER_VALIDATE_URL,
                    FILTER_FLAG_PATH_REQUIRED
                    // FILTER_FLAG_PATH_REQUIRED |
                    // FILTER_FLAG_HOST_REQUIRED |
                    // FILTER_FLAG_SCHEME_REQUIRED
                )
            );
        };
    }

    // public function loadTL($schemeTerritory)
    // {
    //     $tslPointers = $this->tlol->getTrustedListPointer($schemeTerritory);
    //     $newTL = TrustedList::loadFromPointer($tslPointers[0]);
    //     return $newTL;
    // }
    //
    public function testLoadTLs()
    {
        $crtFileName = $this->datadir.LOTLRootTest::lotlSingingCertPath;
        $crt = file_get_contents($crtFileName);
        $rightCert = new X509Certificate(file_get_contents($crtFileName));
        $lotl = $this->lotl;
        $lotl->verifyTSL($rightCert);
        $nlFile = $this->datadir.'/tl-52f7b34b484ce888c5f1d277bcb2bfbff0b1d3bbf11217a44090fab4b6a83fd3.xml';
        $lotl->addTrustedListXML("NL: Radiocommunications Agency", file_get_contents($nlFile));
        $nlTL = $lotl->getTrustedLists()["NL: Radiocommunications Agency"];
        $this->assertEquals(
            self::getNLTLAttributes(),
            $nlTL->getTrustedListAtrributes()
        );
    }

    // public function testTLAttributes()
    // {
    //     foreach ($this->testSchemeTerritories as $schemeTerritory) {
    //         $this->TLAttributeTests($this->tls[$schemeTerritory]);
    //     };
    // }

    // public function testVerifyAllTLs()
    // {
    //     $this->tlol->verifyTSL();
    //     $this->tlol->setTolerateFailedTLs(true);
    //     $this->assertTrue($this->tlol->verifyAllTLs());
    // }
}
