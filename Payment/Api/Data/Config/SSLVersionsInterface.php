<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Api\Data\Config;

/**
 *
 */
interface SSLVersionsInterface {

   public const SSL_VERSIONS_OPTIONS_LIST = [
       CURL_SSLVERSION_DEFAULT => 'Default',
       CURL_SSLVERSION_TLSv1 => 'TLSv1',
       CURL_SSLVERSION_SSLv2 => 'SSLv2',
       CURL_SSLVERSION_SSLv3 => 'SSLv3',
       CURL_SSLVERSION_TLSv1_0 => 'TLSv1_0',
       CURL_SSLVERSION_TLSv1_1 => 'TLSv1_1',
       CURL_SSLVERSION_TLSv1_2 => 'TLSv1_2',
       CURL_SSLVERSION_TLSv1_3 => 'TLSv1_3',
   ];
}
