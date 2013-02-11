# Unit and Integration tests

## Requirements

* Recent PHPUnit (3.7.9 was used during the development, but any recent
  PHPUnit should work).
* Copy of the 'tests' directory from eZ Publish (get it by cloning
  ezpublish from Github).

## Unit tests

    $ phpunit extensions/ezbrightcove

## Integration tests

If you do not have Xdebug installed you might want to comment out the
code coverage related code in tests/runtests.php.

    $ php tests/runtests.php --dsn mysql://root@localhost/tests extension/ezbrightcove
