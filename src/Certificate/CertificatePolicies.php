<?php

namespace eIDASCertificate\Certificate;

use eIDASCertificate\ExtensionInterface;
use eIDASCertificate\CertificateException;
use eIDASCertificate\ParseException;
use eIDASCertificate\Finding;
use eIDASCertificate\OID;
use eIDASCertificate\Certificate\X509Certificate;
use ASN1\Type\UnspecifiedType;

/**
 *
 */
class CertificatePolicies implements ExtensionInterface
{
    private $binary;
    private $isCritical;
    private $findings = [];
    private $policies = [];

    const type = 'certificatePolicies';
    const oid = '2.5.29.32';
    const uri = 'https://tools.ietf.org/html/rfc5280#section-4.2.1.4';

    public function __construct($extensionDER, $isCritical = false)
    {
        $this->isCritical = $isCritical;
        $this->binary = $extensionDER;
        if ($isCritical == true) {
            $findingLevel = 'critical';
        } else {
            $findingLevel = 'warning';
        }
        try {
            $seq = UnspecifiedType::fromDER($extensionDER)->asSequence();
        } catch (\Exception $e) {
            $this->findings[] = new Finding(
                self::type,
                $findingLevel,
                'Malformed certificatePolicies extension \''.$e->getMessage().'\': '.
                base64_encode($extensionDER)
            );
            return;
        }

        foreach ($seq->elements() as $certPolicy) {
            try {
                $policy = new CertificatePolicy($certPolicy);
                $this->policies[] = $policy;
            } catch (ParseException $e) {
                if ($e->getMessage() == 'Unrecognised') {
                    $oid = $certPolicy->at(0)->asObjectIdentifier()->oid();
                    $oidName = OID::getName($oid);
                    $this->findings[] = new Finding(
                        self::type,
                        $findingLevel,
                        "Unrecognised certificatePolicy OID $oid ($oidName): ".
                    base64_encode($certPolicy->toDER())
                    );
                }
            }
        }
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
        return "This is an CertificatePolicies extension";
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
        if (!empty($this->policies)) {
            foreach ($this->policies as $policy) {
                $policies[] = $policy->getAttributes();
            }
            return [
              'issuer' => ['policies' => $policies]
            ];
        } else {
            return [];
        }
    }
}
