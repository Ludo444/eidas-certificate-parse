<?php

namespace eIDASCertificate\Certificate;

use eIDASCertificate\Certificate\ExtensionException;
use eIDASCertificate\Certificate\AuthorityKeyIdentifier;
use eIDASCertificate\Certificate\UnknownExtension;
use eIDASCertificate\Extensions\QCStatements;
use eIDASCertificate\OID;
use ASN1\Type\UnspecifiedType;

/**
 *
 */
abstract class Extension
{
    public static function fromBinary($extensionDER)
    {
        $extension = UnspecifiedType::fromDER($extensionDER)->asSequence();
        $extensionOid = $extension->at(0)->asObjectIdentifier()->oid();
        if ($extension->at(1)->isType(1)) {
            $isCritical = $extension->at(1)->asBoolean()->value();
            $extnValue = $extension->at(2)->asOctetString()->string();
        } else {
            $isCritical = false;
            $extnValue = $extension->at(1)->asOctetString()->string();
        }
        $extensionName = OID::getName($extensionOid);
        // print "$extensionOid ($extensionName): " . base64_encode($extnValue) .PHP_EOL;
        switch ($extensionName) {
          case 'basicConstraints':
            // TODO: Properly handle Basic Constraints
            return new BasicConstraints($extnValue, $isCritical);
            break;
          case 'preCertPoison':
            return new PreCertPoison($extnValue, $isCritical);
            // TODO: Properly handle poisoned certificates
            break;
          case 'keyUsage':
            return new KeyUsage($extnValue, $isCritical);
            break;
          case 'authorityInfoAccess':
            return new AuthorityInformationAccess($extnValue, $isCritical);
            break;
          case 'subjectKeyIdentifier':
            return new SubjectKeyIdentifier($extnValue, $isCritical);
            break;
          case 'authorityKeyIdentifier':
            return new AuthorityKeyIdentifier($extnValue, $isCritical);
            break;
          case 'subjectAltName':
            return new SubjectAltName($extnValue, $isCritical);
            break;
          case 'certificatePolicies':
            return new CertificatePolicies($extnValue, $isCritical);
            break;
          case 'extKeyUsage':
            // TODO: Implement EKU
            return new ExtendedKeyUsage($extnValue, $isCritical);
            break;
          case 'crlDistributionPoints':
            // TODO: Implement CDPs
            return new CRLDistributionPoints($extnValue, $isCritical);
            break;
          case 'qcStatements':
            return new QCStatements($extnValue, $isCritical);
            break;
          // case 'certificatePolicies':
            // TODO: Implement certificatePolicies QCStatement
            // return false;
            // break;
          // case 'policyConstraints':
            // TODO: Implement policyConstraints QCStatement
            // return false;
            // break;

          default:
                $extension = new UnknownExtension(
                    $extnValue,
                    $isCritical
                );
                $extension->setOID($extensionOid);
                return $extension;
            // }
            break;
        }
    }
}
