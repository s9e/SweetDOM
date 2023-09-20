## From 2.x to 3.0

Magic methods whose names end in `Sibling` have been renamed after the DOM4 methods `after` and `before`. Methods using the naming scheme `append{Element}Sibling` should be renamed `after{Element}`, and `prepend{Element}Sibling` should be renamed `before{Element}`.

Text methods such as `appendText`, `prependText`, and `insertAdjacentText`, as well as the legacy method `insertAdjacentElement` have been removed. Those operations can be performed using the native DOM methods `after`, `append`, `before`, and `prepend`.

The method `insertAdjacentXML` has been removed without replacement. It can be performed using native methods using [`DOMDocumentFragment::appendXML`](https://www.php.net/manual/domdocumentfragment.appendxml.php).