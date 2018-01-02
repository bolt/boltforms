Upgrading
=========

If you are upgrading from a relase prior to 4.0, there are a few settings which
may require attention, or modification.

Twig Variables
--------------

The following Twig feedback variables are deprecated:

  - `error`
  - `message`

They are replaced by `messages` (plural) that contains a keyed array of
`info`, `error`, and `debug` message arrays.
