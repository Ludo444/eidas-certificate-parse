<?php

namespace eIDASCertificate\Certificate;

use eIDASCertificate\Certificate\ExtensionInterface;
use eIDASCertificate\Certificate\CertificateException;
use eIDASCertificate\Certificate\X509Certificate;
use eIDASCertificate\OID;
use eIDASCertificate\Finding;
use ASN1\Type\UnspecifiedType;

/**
 *
 */
class AuthorityInformationAccess implements ExtensionInterface
{
    private $binary;
    private $caIssuers = [];
    private $ocsp = [];
    private $findings = [];
    private $isCritical;


    const type = 'authorityInfoAccess';
    const oid = '1.3.6.1.5.5.7.1.1';
    const uri = 'https://tools.ietf.org/html/rfc5280#section-4.2.2.1';

    public function __construct($extensionDER, $isCritical = false)
    {
        $this->isCritical = $isCritical;
        $seq = UnspecifiedType::fromDER($extensionDER)->asSequence();
        foreach ($seq->elements() as $accessDescription) {
            $accessDescription = $accessDescription->asSequence();
            $oid = $accessDescription->at(0)->asObjectIdentifier()->oid();
            $oidName = OID::getName($oid);
            switch ($oidName) {
            case 'caIssuers':
              if ($accessDescription->at(1)->asTagged()->tag() != 6) {
                  // Truly weird content!
                  break;
              }
              $caIssuer = $accessDescription->at(1)->asTagged()->expectExplicit(6)->toDER();
              if ($caIssuer[0] == chr(0x86)) {
                  $caIssuer[0] = chr(22);
              }
              $this->caIssuers[] = UnspecifiedType::fromDER($caIssuer)->asIA5String()->string();
              break;
            case 'ocsp':
              $ocsp = $accessDescription->at(1)->asTagged()->expectExplicit(6)->toDER();
              if ($ocsp[0] == chr(0x86)) {
                  $ocsp[0] = chr(22);
              }
              $this->ocsp[] = UnspecifiedType::fromDER($ocsp)->asIA5String()->string();
              break;
            default:
              $this->findings[] = new Finding(
                  self::type,
                  'warning',
                  "Unrecognised authorityInfoAccess OID $oid ($oidName): ".
                base64_encode($extensionDER)
              );
              break;
          }
        }
        $this->binary = $extensionDER;
    }

    public function getCAIssuers()
    {
        return $this->caIssuers;
    }

    public function getOCSP()
    {
        return $this->ocsp;
    }

    public function getType()
    {
        return self::type;
    }

    public function getURI()
    {
        return self::uri;
    }

    public function getBinary()
    {
        return $this->binary;
    }

    public function getDescription()
    {
        return "This is an AuthorityInformationAccess extension";
    }

    public function getFindings()
    {
        return $this->findings;
    }

    public function getIsCritical()
    {
        return $this->isCritical;
    }

    public function setCertificate(X509Certificate $cert)
    {
        null;
    }

    public function getAttributes()
    {
        return
          [
            "ocsp" => $this->ocsp,
            "caIssuers" => $this->caIssuers,
          ];
    }
}
