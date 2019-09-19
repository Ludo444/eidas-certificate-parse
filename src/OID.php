<?php

namespace eIDASCertificate;

/**
 *
 */
class OID
{
    const qcStatements = '1.3.6.1.5.5.7.1.3';
    const PKIX_QCSYNTAX_V2                    = '1.3.6.1.5.5.7.11.2';
    const qcs_QcCompliance                    = '0.4.0.1862.1.1';
    const QcLimitValue  = '0.4.0.1862.1.2';
    const QcRetentionPeriod  = '0.4.0.1862.1.3';
    const QcSSCD  = '0.4.0.1862.1.4';
    const QcPDS  = '0.4.0.1862.1.5';
    const QcType  = '0.4.0.1862.1.6';
    const esign  = '0.4.0.1862.1.6.1';
    const eseal  = '0.4.0.1862.1.6.2';
    const web  = '0.4.0.1862.1.6.3';
    const RoleOfPsp  = '0.4.0.19495.1';
    const PSP_AS  = '0.4.0.19495.1.1';
    const PSP_PI  = '0.4.0.19495.1.2';
    const PSP_AI  = '0.4.0.19495.1.3';
    const PSP_IC  = '0.4.0.19495.1.4';
    const PSD2  = '0.4.0.19495.2';
    const PreCertPoison = '1.3.6.1.4.1.11129.2.4.3';
    const BasicConstraints = '2.5.29.19';
    const KeyUsage = '2.5.29.15';
    const ExtendedKeyUsage = '2.5.29.37';
    const SubjectKeyIdentifier = '2.5.29.14';
    const AuthorityKeyIdentifier = '2.5.29.35';

    public static function getName($oidString)
    {
        // return 'blah';
        // throw new \Exception("Error Processing Request", 1);

        $oidName = "unknown";
        switch ($oidString) {
          case self::qcStatements:

              $oidName = 'qcStatements';
              break;
          case self::PKIX_QCSYNTAX_V2:
              $oidName = 'id-qcs-pkixQCSyntax-v2';
              break;
          case self::qcs_QcCompliance:
              $oidName = 'qcs-QcCompliance';
              break;
          case self::QcLimitValue:
            $oidName = 'QcLimitValue';
            break;
          case self::QcRetentionPeriod:
            $oidName = 'QcRetentionPeriod';
            break;
          case self::QcSSCD:
            $oidName = 'QcSSCD';
            break;
          case self::QcPDS:
            $oidName = 'QcPDS';
            break;
          case self::QcType:
            $oidName = 'QcType';
            break;
          case self::esign:
            $oidName = 'esign';
            break;
          case self::eseal:
            $oidName = 'eseal';
            break;
          case self::web:
            $oidName = 'web';
            break;
          case self::RoleOfPsp:
            $oidName = 'RoleOfPsp';
            break;
          case self::PSP_AS:
            $oidName = 'PSP_AS';
            break;
          case self::PSP_PI:
            $oidName = 'PSP_PI';
            break;
          case self::PSP_AI:
            $oidName = 'PSP_AI';
            break;
          case self::PSP_IC:
            $oidName = 'PSP_IC';
            break;
          case self::PSD2:
            $oidName = 'PSD2';
            break;
          case self::BasicConstraints:
            $oidName = 'basicConstraints';
            break;
          case self::KeyUsage:
            $oidName = 'keyUsage';
            break;
          case self::ExtendedKeyUsage:
            $oidName = 'extKeyUsage';
            break;
          case self::PreCertPoison:
            $oidName = 'preCertPoison';
            break;
          case self::AuthorityKeyIdentifier:
            $oidName = 'authorityKeyIdentifier';
            break;
          case self::SubjectKeyIdentifier:
            $oidName = 'subjectKeyIdentifier';
            break;
          }
        return $oidName;
        // return "$oidString ($oidName)";
    }
}
