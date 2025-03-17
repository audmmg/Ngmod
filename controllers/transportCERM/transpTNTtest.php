<?php

    $Xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><priceRequest xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"> 
<appId>PC</appId> 
            <appVersion>3.0</appVersion> 
            <priceCheck> 
              <rateId>rate2</rateId> 
              <sender> 
                <country>LT</country> 
                <town/> 
                <postcode>LT-01001</postcode> 
              </sender> 
              <delivery> 
                <country>FI</country> 
                <town>Kuhmo</town> 
                <postcode>88899</postcode> 
              </delivery> 
               <collectionDateTime>2016-02-08T13:05:08</collectionDateTime> 
              <product> 
                <type>N</type> 
              </product> 
              <account> 
                <accountNumber>KLIENTO NR</accountNumber> 
                <accountCountry>LT</accountCountry> 
              </account> 
              <currency>EUR</currency> 
              <priceBreakDown>false</priceBreakDown> 
              <consignmentDetails> 
                <totalWeight>2.5</totalWeight> 
                <totalVolume>0.001</totalVolume> 
                <totalNumberOfPieces>1</totalNumberOfPieces> 
              </consignmentDetails> 
              <pieceLine> 
                <numberOfPieces>1</numberOfPieces> 
                <pieceMeasurements> 
                  <length>0.1</length> 
                  <width>0.1</width> 
                  <height>0.1</height> 
                  <weight>2.5</weight> 
                </pieceMeasurements> 
                <pallet>1</pallet> 
              </pieceLine> 
            </priceCheck> 
          </priceRequest> 
";"

    $username = "XXXXX";
    $password = "XXXX";
    $host = "https://express.tnt.com/expressconnect/pricing/getprice";

    $process = curl_init($host);
    
    curl_setopt($process, CURLOPT_USERPWD, $username . ":" . $password);

          curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false); 							 // added as workaround TO use without certificate
    curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'/*, $additionalHeaders*/));
    curl_setopt($process, CURLOPT_HEADER, 1);
    curl_setopt($process, CURLOPT_USERPWD, $username . ":" . $password);
    curl_setopt($process, CURLOPT_TIMEOUT, 30);
    curl_setopt($process, CURLOPT_POST, 1);
    curl_setopt($process, CURLOPT_POSTFIELDS, $Xml);
    curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
    $return = curl_exec($process);
    
    if ( curl_errno($process) ) {
        $result = 'ERROR -> ' . curl_errno($process) . ': ' . curl_error($process);
    } else {
        $returnCode = (int)curl_getinfo($process, CURLINFO_HTTP_CODE);
        switch($returnCode){
            case 200:
                break;
            default:
                echo $result = 'HTTP ERROR -> ' . $returnCode;
                break;
        }
    }

    curl_close($process);
    
    echo "!!". $return ."??";