<?php

namespace eIDASCertificate\Certificate;

use eIDASCertificate\OID;
use ASN1\Type\UnspecifiedType;

/**
 *
 */
class DistinguishedName
{
    private $binary;

    public function __construct($dnSequence)
    {
        $this->sequence = $dnSequence->asSequence();
    }

    public function getDN()
    {
        $dn = '';
        foreach ($this->sequence->elements() as $dnPart) {
            $expanded = self::getDNPartExpanded($dnPart);
            if (!is_array($expanded['value'])) {
                $dn .= '/'.$expanded['shortName'].'='.$expanded['value'];
            } else {
                foreach ($expanded['value'] as $value) {
                    $dn .= '/'.$expanded['shortName'].'='.$value;
                }
            }
        }
        return $dn;
    }

    public function getExpanded()
    {
        foreach ($this->sequence->elements() as $dnPart) {
            $dnExpanded[] = self::getDNPartExpanded($dnPart);
        }
        return $dnExpanded;
    }

    public static function getDNPartExpanded($dnPart)
    {
        $dnElement = $dnPart->asSet()->at(0)->asSequence();
        $oid = $dnElement->at(0)->asObjectIdentifier()->oid();
        $oidName = OID::getName($oid);
        $dnPartExpanded['name'] = $oidName;
        $dnPartExpanded['shortName'] = OID::getShortName($oidName);
        $dnPartExpanded['oid'] = $oid;
        $identifier = $dnElement->at(1)->tag();
        switch ($identifier) {
        case 12:
          $dnPartExpanded['value'] = $dnElement->at(1)->asUTF8String()->string();
          break;
        case 19:
          $dnPartExpanded['value'] = $dnElement->at(1)->asPrintableString()->string();
          break;
        case 20:
          $dnPartExpanded['value'] = $dnElement->at(1)->asT61String()->string();
          break;
        case 22:
          $dnPartExpanded['value'] = $dnElement->at(1)->asIA5String()->string();
          break;
        case 16:
          $elements = [];
          foreach ($dnElement->at(1)->asSequence()->elements() as $element) {
              $elementTag = $element->tag();
              switch ($elementTag) {
              case 12:
                $elements[] = $element->asUTF8String()->string();
                break;
              case 19:
                $elements[] = $element->asPrintableString()->string();
                break;
              case 20:
                $elements[] = $element->asT61String()->string();
                break;
              case 22:
                $elements[] = $element->asIA5String()->string();
                break;

              default:
                throw new ParseException(
                    "Unknown DN component element type ".
                  $elementTag.
                  ": ".
                  base64_encode($element->toDER()),
                    1
                );
                break;
            }
          }
          $dnPartExpanded['value'] = $elements;
          break;

        default:
          throw new ParseException(
              "Unknown DN component type ".
              $identifier.
              ": ".
              base64_encode($dnElement->toDER()),
              1
          );
          break;
        }
        if ($oidName == 'unknown') {
            throw new ParseException(
                "Unknown OID $oid in DN: ".
            base64_encode($dnElement->toDER()),
                1
            );
        }
        return $dnPartExpanded;
    }
}
