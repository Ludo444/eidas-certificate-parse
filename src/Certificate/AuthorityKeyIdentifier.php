<?php

namespace eIDASCertificate\Certificate;

use eIDASCertificate\Certificate\ExtensionInterface;
use eIDASCertificate\CertificateException;
use ASN1\Type\UnspecifiedType;

/**
 *
 */
class AuthorityKeyIdentifier implements ExtensionInterface
{
    private $binary;
    private $keyIdentifier;

    const type = 'authorityKeyIdentifier';
    const oid = '2.5.29.35';
    const uri = 'https://tools.ietf.org/html/rfc5280#section-4.2.1.1';

    public function __construct($extensionDER)
    {
        $seq = UnspecifiedType::fromDER($extensionDER)->asSequence();
        foreach ($seq->elements() as $akiElement) {
            switch ($akiElement->tag()) {
            case chr(0x80):
              $this->keyIdentifier = $akiElement->asImplicit(0x04)->asOctetString()->string();
              break;
            case 1:
            case 2:
              // TODO: Handle complex AKIs
              // https://tools.ietf.org/html/rfc5280#section-4.2.1.1
              break;
            default:
              throw new ExtensionException("Unrecognised AuthorityKeyIdentifier ". $akiElement->tag()." Format: ". base64_encode($akiElement->toDER()), 1);
              break;
          }
        }
        $this->binary = $extensionDER;
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

    public function getKeyId()
    {
        return $this->keyIdentifier;
    }
}
