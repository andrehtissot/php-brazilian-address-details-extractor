<?php
class BrazilianAddressDetailsExtractor {
    static function getStreetFromFullAddress($fullAddress){
        $semiFullAddress = $fullAddress;
        if(($position = stripos($semiFullAddress, ' sala ')) !== false)
            $semiFullAddress = trim(substr($semiFullAddress, 0, $position));
        if(($position = stripos($semiFullAddress, ' apto ')) !== false)
            $semiFullAddress = trim(substr($semiFullAddress, 0, $position));
        if(($position = stripos($semiFullAddress, ', centro')) === (strlen($semiFullAddress)-8))
            $semiFullAddress = trim(substr($semiFullAddress, 0, $position));
        $street = str_replace(array(' S/N', ' N.', ' CJ', ' Prof, '),
            array(' s/n', ' n.', ' cj', ' Prof. '), $fullAddress);
        if(($position = stripos($street, 'cx. postal')) !== false)
            $street = trim(substr($street, 0, $position));
        if(($position = stripos($street, 'caixa postal')) !== false)
            $street = trim(substr($street, 0, $position));
        $street = explode(',', $street);
        $street = explode(' n. ', $street[0]);
        $street = explode(' s/n', $street[0]);
        $street = explode(' km ', $street[0]);
        if(preg_match('/([\D\d]+) \- \d+ \- /', $street[0], $matches))
            $street[0] = $matches[1];
        if(preg_match('/([\D\d]+) \d+ \- /', $street[0], $matches))
            $street[0] = $matches[1];
        if(preg_match('/([\D\d]+) \d+\-Parque/', $street[0], $matches))
            $street[0] = $matches[1];
        if(($position = stripos($street[0], ' av. ')) !== false || ($position = stripos($street[0], 'av. ')) === 0){
            $street[0] = trim(substr($street[0], $position));
        } elseif(($position = stripos($street[0], ' avenida ')) !== false
            || ($position = stripos($street[0], 'avenida ')) === 0){
            $street[0] = trim(substr($street[0], $position));
        } elseif(($position = stripos($street[0], ' rua ')) !== false
            || ($position = stripos($street[0], ' rua ')) === 0){
            $street[0] = trim(substr($street[0], $position));
        }
        $street = explode(' - ', $street[0]);
        if(isset($street[1]) && stripos($street[1], ' rua ') !== false)
            $street[0] = $street[1];
        if(isset($street[1]) && stripos($street[1], ' rua ') !== false)
            $street[0] = $street[1];
        if(preg_match('/^([\D\d]+) \d+ cj/', $street[0], $matches))
            $street[0] = $matches[1];
        $street = explode(' cj', $street[0]);
        foreach (array("º", "°") as $x)
            foreach (array("", ".") as $y)
                foreach (array("-", " ") as $z){
                    $street = explode($z.'n'.$y.$x, $street[0]);
                    $street = explode($z.'N'.$y.$x, $street[0]);
                }
        if(preg_match('/^([\D\d]+) \d+ \/ \d+/', $street[0], $matches))
            $street[0] = $matches[1];
        if(preg_match('/^([\D\d]+) \d+\-\d+/', $street[0], $matches))
            $street[0] = $matches[1];
        if($street[0] === $semiFullAddress && preg_match('/^([\D\d]+) (\d+)$/', $street[0], $matches))
            $street[0] = $matches[1];
        $street[0] = trim($street[0]);
        if(preg_match('/^([\D\d]+ \d+)\-[a-z]+$/i', $street[0], $matches))
            $street[0] = $matches[1];
        $semiFullAddressDigits = preg_replace('/\D/','',$semiFullAddress);
        if(!empty($semiFullAddressDigits) && strpos($street[0], " $semiFullAddressDigits ") !== false)
            $street = explode(" $semiFullAddressDigits ", $street[0]);
        if(!empty($semiFullAddressDigits) && strpos($street[0], " $semiFullAddressDigits")
            === ($position = strlen($street[0])-strlen($semiFullAddressDigits)-1))
            $street[0] = substr($street[0], 0, $position);
        if(($position = strrpos($street[0], ' -')) === (strlen($street[0])-2))
            $street[0] = substr($street[0], 0, $position);
        if((($positionRua = stripos($street[0], ' rua ')) !== false
                || ($positionRua = stripos($street[0], 'rua ')) === 0)
            && (($positionAv = stripos($street[0], ' av. ')) !== false
                || ($positionAv = stripos($street[0], 'av. ')) === 0))
            $street[0] = substr($street[0], 1+max($positionRua, 0, $positionAv));
        $street[0] = trim($street[0]);
        return trim($street[0]);
    }

    static function getNumberFromFullAddress($fullAddress, array $options = array()){
        if(empty($options['cachedStreet']))
            $street = self::getStreetFromFullAddress($fullAddress);
        else $street = $options['cachedStreet'];
        $number = trim(str_replace($street, '', $fullAddress));
        $number = trim(str_replace(str_replace(' Prof. ', ' Prof, ', $street), '', $number));
        if($number === '') { return null ;}
        if(($position = stripos($number, 'cx. postal')) !== false)
            $number = trim(substr($number, 0, $position));
        if(($position = stripos($number, 'caixa postal')) !== false)
            $number = trim(substr($number, 0, $position));
        if(($position = stripos($number, ' apto ')) !== false)
            $number = trim(substr($number, 0, $position));
        if(($position = stripos($number, ' ap. ')) !== false)
            $number = trim(substr($number, 0, $position));
        if(($position = stripos($number, ' #')) !== false)
            $number = trim(substr($number, $position+2));
        if(preg_match('/^(\d+ \/ \d+)$/', $number, $matches))
            return null;
        if(preg_match('/^(\d+-\d+)$/', $number, $matches))
            return null;
        if($number === '') { return null ;}
        if(preg_match('/^,? ?(\d)\.(\d{3})$/', $number,$matches))
            return (int) $matches[1].$matches[2];
        if(preg_match('/^,? ?(\d)\.(\d{3})[ ,]/', $number,$matches))
            return (int) $matches[1].$matches[2];
        if(preg_match('/^,? ?(\d\d?\d?\d?)$/', $number,$matches))
            return (int) $matches[1];
        if(preg_match('/^,? ?(\d\d?\d?\d?\d?)[ ,]/', $number,$matches))
            return (int) $matches[1];
        if(preg_match('/^\d+$/', $number))
            return (int) $number;
        if(preg_match('/^,? ?nº \D+-(\d+) \D+$/i', $number, $matches))
            $number = $matches[1];
        foreach (array(".","o","") as $x)
            foreach (array("º", "°",chr(186),'') as $y)
                foreach (array(".","") as $z)
                    foreach (array("-", " ", ", ", null) as $q){
                        if($q === null){
                            if(($position = stripos($number, 'n'.$x.$y.$z)) === 0)
                                $number = trim(substr($number, $position+strlen('n'.$x.$y.$z)));
                        } elseif(($position = stripos($number, $q.'n'.$x.$y.$z)) !== false)
                            $number = trim(substr($number, $position+strlen($q.'n'.$x.$y.$z)));
                    }
        if(preg_match('/^\d+$/', $number))
            return (int) $number;
        if(($position = stripos($number, ' - ')) !== false){
            $number = explode(' - ', $number);
            if(preg_match('/\d/', $number[0]))
                $number = $number[0];
            else $number = $number[1];
        }
        $number = trim(preg_replace('/^-\D, /', '', $number));
        if(preg_match('/^(\d+-\d+)$/', $number, $matches))
            return null;
        if(preg_match('/^- (\d+)$/', $number, $matches))
            return (int) $matches[1];
        if(preg_match('/^- (\d+)[ \.]\D+$/', $number, $matches))
            return (int) $matches[1];
        if(preg_match('/^,? ?(\d\d?\d?\d?\d?)$/', $number,$matches))
            return (int) $matches[1];
        if(preg_match('/^,? ?(\d\d?\d?\d?\d?)\D+$/', $number,$matches))
            return (int) $matches[1];
        if(preg_match('/^,? ?(\d)\.(\d{3})$/', $number,$matches))
            return (int) $matches[1].$matches[2];
        if(preg_match('/^,? ?(\d)\.(\d{3})[ ,]/', $number,$matches))
            return (int) $matches[1].$matches[2];
        return (int) $number;
    }

    static function getNeighborhoodFromFullAddress($fullAddress, array $options = array()){
        if(empty($options['cachedStreet']))
            $street = self::getStreetFromFullAddress($fullAddress);
        else $street = $options['cachedStreet'];
        $neighborhood = str_replace($street, '', $fullAddress);
        $neighborhood = trim(str_replace(str_replace(' Prof. ', ' Prof, ', $street), '', $neighborhood));
        if(empty($options['cachedNumber']))
            $number = self::getNumberFromFullAddress($fullAddress);
        else $number = $options['cachedNumber'];
        if(!empty($number)){
            $number = ''.$number;
            if(strpos($neighborhood, $number) !== false)
                $neighborhood = str_replace($number, '', $neighborhood);
            else $neighborhood = str_replace(number_format($number,0,"","."), '', $neighborhood);
        }
        $neighborhood = trim(str_ireplace('s/nº', '', $neighborhood));
        $neighborhood = trim(str_ireplace('s/n°', '', $neighborhood));
        $neighborhood = trim(str_ireplace('s/no', '', $neighborhood));
        $neighborhood = trim(str_ireplace('s/n', '', $neighborhood));
        if(preg_match('/^\d+ \/ \d+$/', $neighborhood)) { return ''; }
        $neighborhood = preg_replace('/sala-\d+/', '', $neighborhood);
        if(strpos($neighborhood, ',') === 0)
            $neighborhood = trim(substr($neighborhood, 1));
        if(strpos($neighborhood, '-') === 0)
            $neighborhood = trim(substr($neighborhood, 1));
        foreach(array(' ', '-', 'º ', 'º-', '° ', '°-') as $suffix) {
            $neighborhood = trim(str_ireplace("n.$suffix", '', $neighborhood));
            $neighborhood = trim(str_ireplace("n$suffix", '', $neighborhood));
        }
        $neighborhood = preg_replace('/conj\. coml\. [a-z]+/i', '', $neighborhood);
        if(preg_match('/^av\. \d+-\D+ esquina \D+,(.+)$/i',$neighborhood,$matches))
            $neighborhood = trim($matches[1]);
        if(preg_match('/^cj\.? \d+ (.+)$/i',$neighborhood,$matches))
            $neighborhood = trim($matches[1]);
        if(preg_match('/^ap\.? ?\d+ (.+)$/i',$neighborhood,$matches))
            $neighborhood = trim($matches[1]);
        if(preg_match('/^,? ?\d+º andar- (.+)$/i',$neighborhood,$matches))
            $neighborhood = trim($matches[1]);
        if(preg_match('/^sala \d+ (.+)$/i',$neighborhood,$matches))
            $neighborhood = trim($matches[1]);
        if(strpos($neighborhood, '-') === 0)
            $neighborhood = trim(substr($neighborhood, 1));
        $neighborhoodExploded = explode(' - ', $neighborhood);
        $neighborhood = array();
        foreach ($neighborhoodExploded as $neighborhoodExplodedPart)
            foreach(explode(', ',$neighborhoodExplodedPart) as $neighborhoodExplodedPartPart)
                if(!empty($neighborhoodExplodedPartPart))
                    $neighborhood[] = trim($neighborhoodExplodedPartPart);
        foreach ($neighborhoodExploded as $neighborhoodExplodedPart)
            foreach(explode(', ',$neighborhoodExplodedPart) as $neighborhoodExplodedPartPart)
                if(!empty($neighborhoodExplodedPartPart))
                    $neighborhood[] = trim($neighborhoodExplodedPartPart);
        if(empty($neighborhood[0])) { return ''; }
        $regexesToExclude = array('[a-z]-', '-[a-z]', '[a-z]', 'nº', 'n°', 'no 0', 'nº ?\d+-?\d+?', 'km ?\d?,?\d+', '\d+-\d+',
            '\d+ ?º', 'bloco \d+', 'bloco [a-z]', 'bl\.\d', '\d+\.?º andar', '\d+\.?° andar', '\d+\.?o\.? andar',
            'caixa postal \d+', 'cx. postal \d+', 'apt?o?\.? ?\d+\D?', 'n\. *ap \d+', 'prédio \d+', 'PRÉDIO \d+',
            'sala \d+', 'sala [a-z]', 'rua \d+', 'av\. \d+', 'conj\. ?\d+', 'km\.? ?\d+', 'ed\. \D+ \D+',
            'residencial \D+', 'casa \D+ \D+', 'cp \d+', 'loja \d+', '[a-z] +nº', '[a-z] +n°', '[a-z]+ Shopping',
            'prédio \d+ \d+º andar', 'prédio \d+ \d+° andar', 'prédio \d+ \d+º andar sala \d+',
            'prédio \d+ \d+° andar sala \d+', 'prédio \d+ sala \d+', 'prédio \d+ sala \d+');
        do {
            $neighborhood[0] = trim($neighborhood[0], ' .,-\\/');
            foreach ($regexesToExclude as $regexToExcludeIndex => $regexToExclude) {
                if(preg_match("/^$regexToExclude$/i", $neighborhood[0])) {
                    $neighborhood[0] = '';
                    break;
                }
            }
            if(!empty($neighborhood[0])) { break; }
            array_shift($neighborhood);
        } while(!empty($neighborhood));
        if(empty($neighborhood)) { return ''; }

        $neighborhood = $neighborhood[0];
        $neighborhood = trim(str_ireplace('0', '', $neighborhood));
        if(stripos($neighborhood, '.') === 0) { $neighborhood = trim(substr($neighborhood,1)); }
        if(strlen($neighborhood) === 3 && stripos($neighborhood, 'nº') === 0) { return ''; }
        if(strlen($neighborhood) === 3 && stripos($neighborhood, 'n°') === 0) { return ''; }
        if(strlen($neighborhood) === 4 && stripos($neighborhood, 'n.º') === 0) { return ''; }
        if(strlen($neighborhood) === 4 && stripos($neighborhood, 'n.°') === 0) { return ''; }
        if(strlen($neighborhood) === 4 && stripos($neighborhood, 'nº.') === 0) { return ''; }
        if(strlen($neighborhood) === 4 && stripos($neighborhood, 'n°.') === 0) { return ''; }
        if(preg_match('/^cj\.? ?\d+$/i',$neighborhood,$matches)) { return ''; }
        if(strlen($neighborhood) === 9 && stripos($neighborhood, 'centro') === 3) { return substr($neighborhood,3); }
        $neighborhood = explode('-', $neighborhood);
        $neighborhood = $neighborhood[0];
        if(preg_match('/^\d+$/i',$neighborhood)) { return ''; }
        if(strlen($neighborhood) === 1) { return ''; }
        return trim($neighborhood);
    }
    static function getComplementFromFullAddress($fullAddress, array $options = array()){
        if(empty($options['cachedStreet']))
            $street = self::getStreetFromFullAddress($fullAddress);
        else $street = $options['cachedStreet'];
        $complement = $fullAddress;
        if(strpos($fullAddress, ' esquina com ') === false){
            $complement = str_replace($street, '', $complement);
            $complement = trim(str_replace(str_replace(' Prof. ', ' Prof, ', $street), '', $complement));
        }
        if(empty($options['cachedNumber']))
            $number = self::getNumberFromFullAddress($fullAddress);
        else $number = $options['cachedNumber'];
        if(!empty($number)){
            $number = ''.$number;
            if(strpos($complement, $number) !== false){
                $complement = str_ireplace("n.º $number", '', $complement);
                $complement = str_ireplace("n. $number", '', $complement);
                $complement = str_ireplace("nº $number", '', $complement);
                $complement = str_ireplace("nº$number", '', $complement);
                $complement = str_ireplace("n.$number", '', $complement);
                $complement = str_ireplace("nº. $number", '', $complement);
                $complement = str_ireplace("no 0$number", '', $complement);
                $complement = str_ireplace("0$number", '', $complement);
                $complement = str_ireplace($number, '', $complement);
            } else $complement = str_replace(number_format($number,0,"","."), '', $complement);
        }
        if(empty($options['cachedNeighborhood']))
            $neighborhood = self::getNeighborhoodFromFullAddress($fullAddress);
        else $neighborhood = $options['cachedNeighborhood'];
        $complement = str_replace($neighborhood, '', $complement);
        if(empty($number))
            $complement = str_ireplace('s/n', '', $complement);
        $complement = preg_replace('/ +/',' ',$complement);
        $complement = str_replace('- , ', '- ', $complement);
        $complement = trim($complement, ' .,-\\/');
        if(strlen($complement) === 1 && stripos($complement, '#') === 0) { return ''; }
        if(strlen($complement) === 1 && stripos($complement, '0') === 0) { return ''; }
        if(preg_match('/^n?.?º?.? ?0?$/i',$complement)) { return ''; }
        $complement = str_replace('- - ', '- ', $complement);
        return $complement;
    }
}
