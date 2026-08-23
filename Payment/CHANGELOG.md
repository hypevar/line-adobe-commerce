# Changelog

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

## 0.4.2

- Previous release.
