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

   /**
    * Protocol versions this module is willing to negotiate.
    *
    * The legacy SSL versions and the TLS 1.0 / 1.1 entries are removed: all four are broken or
    * deprecated, and PCI DSS has not accepted TLS 1.0 since June 2018. The bare
    * `CURL_SSLVERSION_TLSv1` entry is also gone - it means "any TLS 1.x", which lets a downgrade
    * land on the oldest of them.
    *
    * A stored value outside this list is coerced to TLS 1.2 by the configuration getter rather than
    * being handed to curl.
    *
    * @see \Line\Payment\Model\Config::getApiSslVersion()
    */
   public const SSL_VERSIONS_OPTIONS_LIST = [
       CURL_SSLVERSION_DEFAULT => 'Default',
       CURL_SSLVERSION_TLSv1_2 => 'TLSv1_2',
       CURL_SSLVERSION_TLSv1_3 => 'TLSv1_3',
   ];
}
