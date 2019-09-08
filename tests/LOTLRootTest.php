<?php

namespace eIDASCertificate\tests;

use PHPUnit\Framework\TestCase;
use eIDASCertificate\DataSource;
use eIDASCertificate\TrustedList;
use eIDASCertificate\ParseException;
use eIDASCertificate\Certificate\X509Certificate;

class LOTLRootTest extends TestCase
{
    const lotlXMLFileName = 'eu-lotl-248.xml';

    private $lotlxml;
    private $lotl;
    private $datadir;

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
    }

    public function testParseLOTL()
    {
        $this->lotl = new TrustedList($this->lotlXML);
        $this->assertEquals(
            "EUlistofthelists",
            $this->lotl->getTSLType()->getType()
        );
        $this->assertInternalType("int", $this->lotl->getVersionID());
        $this->assertInternalType("int", $this->lotl->getSequenceNumber());
        $this->assertEquals(
            5,
            $this->lotl->getVersionID()
        );
        $this->assertGreaterThan(
            1,
            $this->lotl->getSequenceNumber()
        );

        $this->assertGreaterThan(
            0,
            sizeof($this->lotl->getTLX509Certificates())
        );
        foreach ($this->lotl->getTLX509Certificates() as $lotlCert) {
            $this->assertGreaterThan(
                12,
                strlen(X509Certificate::getDN($lotlCert))
            );
        }
    }

    public function testVerifyLOTLSelfSigned()
    {
        $lotl = new TrustedList($this->lotlXML);
        $this->assertTrue($lotl->verifyTSL());
        $expectedSignedByDNArray =
        [
          'C' => 'BE',
          'CN' => 'Patrick Kremer (Signature)',
          'SN' => 'Kremer',
          'GN' => 'Patrick Jean',
          'serialNumber' => '72020329970',
        ];
        $lotlSignedByCert = $lotl->getSignedBy();
        $lotlSignedByDNArray = openssl_x509_parse($lotlSignedByCert)['subject'];
        $this->assertEquals(
            $expectedSignedByDNArray,
            $lotlSignedByDNArray
        );
    }

    public function testVerifyLOTLExplicitSigned()
    {
        $wrongCertHash = '9c1a3b646eaf132398ef319e41c8e7ed725b64d5772580ae125d59c0f6845630';
        $rightCertHash = 'd2064fdd70f6982dcc516b86d9d5c56aea939417c624b2e478c0b29de54f8474';
        $certpaths = scandir($this->datadir.'/journal/c-276-1');
        while ($certpaths[0] == '.' || $certpaths[0] == '..') {
            array_shift($certpaths);
        }
        $certs = [];
        foreach ($certpaths as $certpath) {
            $id = explode('.', $certpath)[0];
            $certs[$id] = file_get_contents($this->datadir.'/journal/c-276-1/'.$certpath);
        }
        $wrongCert = $certs[$wrongCertHash];
        $rightCert = $certs[$rightCertHash];
        $lotl = new TrustedList($this->lotlXML);
        try {
            $lotl->verifyTSL([$wrongCert]);
            $this->assetTrue(false); // Should never hit this if exception is raised
        } catch (\eIDASCertificate\Signature\SignatureException $e) {
            $this->assertEquals(
                [
                'signedBy' => 'd2064fdd70f6982dcc516b86d9d5c56aea939417c624b2e478c0b29de54f8474',
                'availableCerts' => [
                  '9c1a3b646eaf132398ef319e41c8e7ed725b64d5772580ae125d59c0f6845630'
                ]
              ],
                $e->getOut()
            );
        }
        $lotl = new TrustedList($this->lotlXML);
        $this->assertTrue($lotl->verifyTSL([$wrongCert,$rightCert]));
        $lotl = new TrustedList($this->lotlXML);
        $this->assertTrue($lotl->verifyTSL($rightCert));
        $expectedSignedByDNArray =
        [
          'C' => 'BE',
          'CN' => 'Patrick Kremer (Signature)',
          'SN' => 'Kremer',
          'GN' => 'Patrick Jean',
          'serialNumber' => '72020329970',
        ];
        $lotlSignedByCert = $lotl->getSignedBy();
        $lotlSignedByDNArray = openssl_x509_parse($lotlSignedByCert)['subject'];
        $this->assertEquals(
            $expectedSignedByDNArray,
            $lotlSignedByDNArray
        );
    }

    public function testGetLOTLTrustedListXMLPointers()
    {
        $lotl = new TrustedList($this->lotlXML);
        $validURLFilterFlags =
            FILTER_FLAG_PATH_REQUIRED;
        // FILTER_FLAG_PATH_REQUIRED |
        // FILTER_FLAG_HOST_REQUIRED |
        // FILTER_FLAG_SCHEME_REQUIRED;
        $tlXMLPointers = $lotl->getTrustedListPointers('xml');
        $this->assertGreaterThan(
            12,
            sizeof($tlXMLPointers)
        );
        foreach ($tlXMLPointers as $tlPointer) {
            $this->assertEquals(
                "application/vnd.etsi.tsl+xml",
                $tlPointer->getTSLMimeType()
            );
            $dp = $tlPointer->getTSLLocation();
            $this->assertEquals(
                $dp,
                filter_var(
                    $dp,
                    FILTER_VALIDATE_URL,
                    $validURLFilterFlags
                )
            );
        };
    }
}
