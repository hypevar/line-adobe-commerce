# Changelog

## 1.0.1

Security release. Upgrade before exposing the checkout to production traffic.

- **The terminal `activationKey` no longer reaches the browser.** The promotions API answers with
  the key inside every merchant object, and it was being served without authentication through
  the promotion responses. It is now removed at the adapter boundary, for every consumer and for
  the cached copy.
- **After upgrading, flush the Magento cache** (`bin/magento cache:flush`): a cached promotions
  payload written by an earlier version still carries the key until it is purged. Rotating the
  affected activation keys is recommended.

## 1.0.0

Packaging release. No behavioural change to the payment gateway: everything in this version is
about what the package contains and where it lives.

- **`Line_VerifiedPurchase` is no longer part of this package.** The verified purchase flow now
  lives in its own repository and Composer package,
  [`line/module-verified-purchase`](https://github.com/hypevar/line-verified-purchase-adobe-commerce).

## 0.5.0

- Installment plans are now resolved server side. The rate and merchant used for the charge are
  determined from the promotions API at authorization time and persisted with the payment.
- Added card testing protection: declined authorizations are counted per card, BIN, quote, email,
  customer, IP and store, with configurable thresholds and a scheduled cleanup job.
- Hardened the gateway transport: certificate verification is enforced and only current TLS
  versions are negotiated.
- Improved handling of credentials and of the data written to logs and to the payment record.
- Hardened the BIN lookup endpoint and added caching for promotions responses.
- Added unit test coverage for the payment validator, the installment plan resolver and the
  request builders.
- Added a Mock mode that answers gateway calls from committed sample responses instead of the
  network, so checkout and the card testing protection can be exercised end to end without
  contacting the API or spending money. A Mock Scenario setting forces a specific response.
- Fixed the checkout feedback when the promotions service fails. The service's own message is now
  shown inside the payment form, instead of telling the customer their card was not recognised, and
  the loading indicator always clears.
- The promotions lookup now waits until the card number is complete for its brand, rather than
  firing once fifteen digits have been typed.
- Corrected log severity so that only a store wide circuit breaker trip is recorded as critical.
  Blocked attempts, advisory thresholds and request building failures now log at their own level,
  which keeps customer triggered events out of the critical log.

## 0.4.2

- Previous release.
